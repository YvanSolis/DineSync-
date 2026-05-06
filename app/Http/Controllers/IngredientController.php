<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\InventoryBatch;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IngredientController extends Controller
{
    public function index()
    {
        $ingredients = Ingredient::with([
                'batches' => function ($query) {
                    $query->orderBy('expiry_date', 'asc');
                }
            ])
            ->orderBy('name')
            ->get();

        return response()->json($ingredients);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'threshold' => 'required|numeric|min:0',
        ]);

        $ingredient = Ingredient::create([
            'name' => $validated['name'],
            'current_stock' => 0,
            'unit' => 'unit',
            'threshold' => $validated['threshold'],
        ]);

        return response()->json(
            $ingredient->fresh()->load('batches'),
            201
        );
    }

    public function show(Ingredient $ingredient)
    {
        return response()->json(
            $ingredient->load([
                'batches' => function ($query) {
                    $query->orderBy('expiry_date', 'asc');
                },
                'transactions' => function ($query) {
                    $query->latest();
                }
            ])
        );
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'threshold' => 'required|numeric|min:0',
        ]);

        $ingredient->update([
            'name' => $validated['name'],
            'threshold' => $validated['threshold'],
        ]);

        $this->syncCurrentStock($ingredient);

        return response()->json(
            $ingredient->fresh()->load([
                'batches' => function ($query) {
                    $query->orderBy('expiry_date', 'asc');
                }
            ])
        );
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return response()->json([
            'message' => 'Ingredient deleted successfully.',
        ]);
    }

    public function addStock(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'quantity_received' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'unit_cost' => 'required|numeric|min:0',
            'received_date' => 'nullable|date',
            'expiry_date' => 'required|date',
            'supplier' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $ingredient) {
            $quantity = (float) $validated['quantity_received'];
            $unitCost = (float) $validated['unit_cost'];

            $ingredient->update([
                'unit' => $validated['unit'],
            ]);

            $batch = InventoryBatch::create([
                'ingredient_id' => $ingredient->id,
                'quantity_received' => $quantity,
                'quantity_remaining' => $quantity,
                'unit_cost' => $unitCost,
                'received_date' => $validated['received_date'] ?? now()->toDateString(),
                'expiry_date' => $validated['expiry_date'],
                'supplier' => $validated['supplier'] ?? null,
                'status' => 'active',
                'remarks' => $validated['remarks'] ?? null,
            ]);

            InventoryTransaction::create([
                'ingredient_id' => $ingredient->id,
                'inventory_batch_id' => $batch->id,
                'type' => 'stock_in',
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $quantity * $unitCost,
                'remarks' => $validated['remarks'] ?? 'Stock batch added.',
            ]);

            $this->syncCurrentStock($ingredient);

            return response()->json(
                $ingredient->fresh()->load([
                    'batches' => function ($query) {
                        $query->orderBy('expiry_date', 'asc');
                    }
                ]),
                201
            );
        });
    }

    public function updateBatch(Request $request, Ingredient $ingredient, InventoryBatch $batch)
    {
        if ((int) $batch->ingredient_id !== (int) $ingredient->id) {
            return response()->json([
                'message' => 'This stock batch does not belong to this ingredient.',
            ], 404);
        }

        $validated = $request->validate([
            'quantity_received' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'unit_cost' => 'required|numeric|min:0',
            'expiry_date' => 'required|date',
            'supplier' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $ingredient, $batch) {
            $oldQuantityReceived = (float) $batch->quantity_received;
            $oldQuantityRemaining = (float) $batch->quantity_remaining;

            $newQuantityReceived = (float) $validated['quantity_received'];
            $newUnitCost = (float) $validated['unit_cost'];

            $usedQuantity = max($oldQuantityReceived - $oldQuantityRemaining, 0);
            $newQuantityRemaining = max($newQuantityReceived - $usedQuantity, 0);

            $ingredient->update([
                'unit' => $validated['unit'],
            ]);

            $batch->update([
                'quantity_received' => $newQuantityReceived,
                'quantity_remaining' => $newQuantityRemaining,
                'unit_cost' => $newUnitCost,
                'expiry_date' => $validated['expiry_date'],
                'supplier' => $validated['supplier'] ?? null,
                'status' => $newQuantityRemaining > 0 ? 'active' : 'used_up',
                'remarks' => $validated['remarks'] ?? null,
            ]);

            InventoryTransaction::create([
                'ingredient_id' => $ingredient->id,
                'inventory_batch_id' => $batch->id,
                'type' => 'adjustment',
                'quantity' => $newQuantityReceived,
                'unit_cost' => $newUnitCost,
                'total_cost' => $newQuantityReceived * $newUnitCost,
                'remarks' => $validated['remarks'] ?? 'Stock batch updated.',
            ]);

            $this->syncCurrentStock($ingredient);

            return response()->json(
                $ingredient->fresh()->load([
                    'batches' => function ($query) {
                        $query->orderBy('expiry_date', 'asc');
                    }
                ])
            );
        });
    }

    public function deleteBatch(Ingredient $ingredient, InventoryBatch $batch)
    {
        if ((int) $batch->ingredient_id !== (int) $ingredient->id) {
            return response()->json([
                'message' => 'This stock batch does not belong to this ingredient.',
            ], 404);
        }

        return DB::transaction(function () use ($ingredient, $batch) {
            InventoryTransaction::create([
                'ingredient_id' => $ingredient->id,
                'inventory_batch_id' => $batch->id,
                'type' => 'adjustment',
                'quantity' => 0,
                'unit_cost' => $batch->unit_cost,
                'total_cost' => 0,
                'remarks' => 'Stock batch deleted.',
            ]);

            $batch->delete();

            $this->syncCurrentStock($ingredient);

            return response()->json([
                'message' => 'Stock batch deleted successfully.',
            ]);
        });
    }

    private function syncCurrentStock(Ingredient $ingredient): void
    {
        InventoryBatch::where('ingredient_id', $ingredient->id)
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '<', now()->toDateString())
            ->update([
                'status' => 'expired',
            ]);

        $totalUsableStock = InventoryBatch::where('ingredient_id', $ingredient->id)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->sum('quantity_remaining');

        $ingredient->update([
            'current_stock' => $totalUsableStock,
        ]);
    }
}