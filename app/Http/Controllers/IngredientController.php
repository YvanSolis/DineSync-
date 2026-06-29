<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\InventoryBatch;
use App\Models\InventoryTransaction;
use App\Models\MenuItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IngredientController extends Controller
{
    public function index()
    {
        $this->syncExpiredAndUsedUpBatches();

        $ingredients = $this->baseIngredientQuery()
            ->orderBy('name')
            ->get()
            ->map(function ($ingredient) {
                return $this->formatIngredientResponse($ingredient);
            })
            ->values();

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
            $this->freshFormattedIngredient($ingredient->id),
            201
        );
    }

    public function show(Ingredient $ingredient)
    {
        $this->syncIngredientStock($ingredient);

        $ingredient = Ingredient::with([
                'batches' => function ($query) {
                    $query->orderBy('expiry_date', 'asc');
                },
                'transactions' => function ($query) {
                    $query->latest();
                },
            ])
            ->findOrFail($ingredient->id);

        return response()->json($this->formatIngredientResponse($ingredient, true));
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

        return response()->json(
            $this->freshFormattedIngredient($ingredient->id)
        );
    }

    public function destroy(Ingredient $ingredient)
    {
        $linkedMenuItems = $ingredient->menuItems()->get();

        $ingredient->delete();

        foreach ($linkedMenuItems as $menuItem) {
            $menuItem->refreshAvailability();
        }

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
            $expiryDate = $validated['expiry_date'];

            $ingredient->update([
                'unit' => $validated['unit'],
            ]);

            $batch = InventoryBatch::create([
                'ingredient_id' => $ingredient->id,
                'quantity_received' => $quantity,
                'quantity_remaining' => $quantity,
                'unit_cost' => $unitCost,
                'received_date' => $validated['received_date'] ?? now()->toDateString(),
                'expiry_date' => $expiryDate,
                'supplier' => $validated['supplier'] ?? null,
                'status' => $this->getBatchStatusByDateAndQuantity($expiryDate, $quantity),
                'remarks' => $validated['remarks'] ?? null,
            ]);

            $this->recordInventoryTransaction(
                $ingredient,
                $batch->id,
                'stock_in',
                $quantity,
                $unitCost,
                $validated['remarks'] ?? 'Stock batch added.'
            );

            $this->syncIngredientStock($ingredient);
            $this->refreshLinkedMenuAvailability($ingredient);

            return response()->json(
                $this->freshFormattedIngredient($ingredient->id, true),
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
            'received_date' => 'nullable|date',
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
                'received_date' => $validated['received_date'] ?? $batch->received_date,
                'expiry_date' => $validated['expiry_date'],
                'supplier' => $validated['supplier'] ?? null,
                'status' => $this->getBatchStatusByDateAndQuantity($validated['expiry_date'], $newQuantityRemaining),
                'remarks' => $validated['remarks'] ?? null,
            ]);

            $this->recordInventoryTransaction(
                $ingredient,
                $batch->id,
                'adjustment',
                $newQuantityReceived,
                $newUnitCost,
                $validated['remarks'] ?? 'Stock batch updated.'
            );

            $this->syncIngredientStock($ingredient);
            $this->refreshLinkedMenuAvailability($ingredient);

            return response()->json(
                $this->freshFormattedIngredient($ingredient->id, true)
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
            $this->recordInventoryTransaction(
                $ingredient,
                $batch->id,
                'adjustment',
                0,
                (float) ($batch->unit_cost ?? 0),
                'Stock batch deleted.'
            );

            $batch->delete();

            $this->syncIngredientStock($ingredient);
            $this->refreshLinkedMenuAvailability($ingredient);

            return response()->json(
                $this->freshFormattedIngredient($ingredient->id, true)
            );
        });
    }

    public function inventoryInsights()
    {
        $this->syncExpiredAndUsedUpBatches();

        $ingredients = Ingredient::with([
                'menuItems' => function ($query) {
                    $query->orderBy('name');
                }
            ])
            ->select([
                'ingredients.id',
                'ingredients.name',
                'ingredients.current_stock',
                'ingredients.unit',
                'ingredients.threshold',
            ])
            ->selectSub($this->usableStockSubquery(), 'total_stock')
            ->selectSub($this->nearestExpirySubquery(), 'nearest_expiry_date')
            ->orderBy('name')
            ->get();

        $critical = [];
        $lowStock = [];
        $nearExpiry = [];
        $healthy = [];
        $affectedMenuItems = [];
        $operationalImpact = [];

        foreach ($ingredients as $ingredient) {
            $stock = (float) ($ingredient->total_stock ?? $ingredient->current_stock ?? 0);
            $threshold = (float) ($ingredient->threshold ?? 0);
            $status = $this->resolveStockStatus($stock, $threshold, $ingredient->nearest_expiry_date);
            $unit = $ingredient->unit ?? 'unit';

            $menuItems = $ingredient->menuItems
                ->pluck('name')
                ->filter()
                ->values()
                ->toArray();

            $menuItemText = count($menuItems)
                ? implode(', ', $menuItems)
                : 'No linked menu items';

            if ($status === 'out_of_stock') {
                $critical[] = [
                    'priority' => 'High Priority',
                    'title' => $ingredient->name . ' is out of stock',
                    'message' => "{$ingredient->name} has no usable stock remaining. This directly affects: {$menuItemText}.",
                ];

                if (count($menuItems)) {
                    $operationalImpact[] = [
                        'priority' => 'Menu Availability Impact',
                        'title' => $ingredient->name . ' affects menu availability',
                        'message' => "{$ingredient->name} is required by {$menuItemText}. Menu items using this ingredient should remain unavailable until stock is replenished.",
                    ];
                }

                foreach ($menuItems as $menuName) {
                    $affectedMenuItems[$menuName][] = $ingredient->name;
                }

                continue;
            }

            if (in_array($status, ['low_stock', 'reorder_soon'], true)) {
                $label = $status === 'reorder_soon' ? 'Reorder Soon' : 'Low Stock';

                $lowStock[] = [
                    'priority' => 'Medium Priority',
                    'title' => "{$ingredient->name} needs restocking attention",
                    'message' => "{$ingredient->name} has {$stock} {$unit} remaining with a threshold of {$threshold} {$unit}. It is used by: {$menuItemText}.",
                ];

                $operationalImpact[] = [
                    'priority' => $label,
                    'title' => "{$ingredient->name} may affect menu availability soon",
                    'message' => "If {$ingredient->name} continues to be used without restocking, menu items linked to it may become unavailable during service.",
                ];

                foreach ($menuItems as $menuName) {
                    $affectedMenuItems[$menuName][] = $ingredient->name;
                }

                continue;
            }

            if ($status === 'near_expiry') {
                $nearestExpiry = $ingredient->nearest_expiry_date
                    ? Carbon::parse($ingredient->nearest_expiry_date)->format('M d, Y')
                    : 'soon';

                $nearExpiry[] = [
                    'priority' => 'Monitor',
                    'title' => "{$ingredient->name} is near expiry",
                    'message' => "{$ingredient->name} has usable stock near expiry on {$nearestExpiry}. Prioritize menu items using it: {$menuItemText}.",
                ];

                $operationalImpact[] = [
                    'priority' => 'Waste Reduction',
                    'title' => "Prioritize {$ingredient->name}",
                    'message' => "Using {$ingredient->name} before expiry can help reduce ingredient waste while it is still safe and usable.",
                ];

                continue;
            }

            $healthy[] = $ingredient->name;
        }

        $affectedSummary = [];

        foreach ($affectedMenuItems as $menuName => $ingredientNames) {
            $uniqueIngredients = array_values(array_unique($ingredientNames));

            $affectedSummary[] = [
                'priority' => 'Unavailable',
                'title' => $menuName . ' is unavailable',
                'message' => "{$menuName} is unavailable because of insufficient stock of: " . implode(', ', $uniqueIngredients) . ".",
            ];
        }

        $priorityRecommendations = [];

        if (count($critical)) {
            $priorityRecommendations[] = [
                'priority' => 'High Priority',
                'title' => 'Restock immediately',
                'message' => 'Restock out-of-stock ingredients immediately to restore affected menu item availability.',
            ];
        }

        if (count($lowStock)) {
            $priorityRecommendations[] = [
                'priority' => 'Medium Priority',
                'title' => 'Prepare restocking before peak hours',
                'message' => 'Prepare restocking for low-stock ingredients before peak ordering hours to prevent item unavailability.',
            ];
        }

        if (count($nearExpiry)) {
            $priorityRecommendations[] = [
                'priority' => 'Monitor',
                'title' => 'Use near-expiry ingredients first',
                'message' => 'Prioritize menu items using near-expiry ingredients to reduce waste while maintaining availability.',
            ];
        }

        if (! count($critical) && ! count($lowStock) && ! count($nearExpiry)) {
            $priorityRecommendations[] = [
                'priority' => 'Stable',
                'title' => 'Inventory is stable',
                'message' => 'Inventory levels are currently stable. Continue regular monitoring and keep stock batches updated.',
            ];
        }

        $criticalCount = count($critical);
        $lowStockCount = count($lowStock);
        $nearExpiryCount = count($nearExpiry);
        $affectedMenuCount = count($affectedSummary);

        if ($criticalCount > 0 || $affectedMenuCount > 0) {
            $healthStatus = 'Critical';
        } elseif ($lowStockCount > 0 || $nearExpiryCount > 0) {
            $healthStatus = 'Needs Attention';
        } else {
            $healthStatus = 'Stable';
        }

        $summaryParts = [];

        if ($criticalCount) {
            $summaryParts[] = "{$criticalCount} ingredient(s) are out of stock";
        }

        if ($lowStockCount) {
            $summaryParts[] = "{$lowStockCount} ingredient(s) need restocking attention";
        }

        if ($nearExpiryCount) {
            $summaryParts[] = "{$nearExpiryCount} ingredient(s) are near expiry within 3 days";
        }

        if ($affectedMenuCount) {
            $summaryParts[] = "{$affectedMenuCount} menu item(s) are currently unavailable";
        }

        if (! count($summaryParts)) {
            $summary = 'Inventory is currently stable. No urgent stock issue, near-expiry concern, or unavailable menu item was detected.';
        } else {
            $summary = 'Inventory health is ' . strtolower($healthStatus) . ': ' . implode(', ', $summaryParts) . '. Review the recommended actions below.';
        }

        if (! count($operationalImpact)) {
            $operationalImpact[] = [
                'priority' => 'Stable',
                'title' => 'No major operational impact detected',
                'message' => 'Current inventory records do not show urgent stock issues affecting menu availability.',
            ];
        }

        return response()->json([
            'success' => true,
            'title' => 'AI Inventory Insight',
            'health_status' => $healthStatus,
            'summary' => $summary,

            'critical_count' => $criticalCount,
            'low_stock_count' => $lowStockCount,
            'near_expiry_count' => $nearExpiryCount,
            'affected_menu_count' => $affectedMenuCount,
            'healthy_count' => count($healthy),

            'critical' => $critical,
            'low_stock' => $lowStock,
            'near_expiry' => $nearExpiry,
            'operational_impact' => $operationalImpact,
            'affected_menu_items' => $affectedSummary,
            'recommendations' => $priorityRecommendations,

            'generated_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    private function baseIngredientQuery()
    {
        return Ingredient::query()
            ->select([
                'ingredients.id',
                'ingredients.name',
                'ingredients.current_stock',
                'ingredients.unit',
                'ingredients.threshold',
                'ingredients.created_at',
                'ingredients.updated_at',
            ])
            ->selectSub($this->usableStockSubquery(), 'total_stock')
            ->selectSub($this->stockValueSubquery(), 'stock_value')
            ->selectSub($this->nearestExpirySubquery(), 'nearest_expiry_date')
            ->selectSub($this->batchCountSubquery(), 'batches_count');
    }

    private function usableStockSubquery()
    {
        return InventoryBatch::query()
            ->selectRaw('COALESCE(SUM(quantity_remaining), 0)')
            ->whereColumn('inventory_batches.ingredient_id', 'ingredients.id')
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '>=', now()->toDateString());
    }

    private function stockValueSubquery()
    {
        return InventoryBatch::query()
            ->selectRaw('COALESCE(SUM(quantity_remaining * unit_cost), 0)')
            ->whereColumn('inventory_batches.ingredient_id', 'ingredients.id')
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '>=', now()->toDateString());
    }

    private function nearestExpirySubquery()
    {
        return InventoryBatch::query()
            ->selectRaw('MIN(expiry_date)')
            ->whereColumn('inventory_batches.ingredient_id', 'ingredients.id')
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '>=', now()->toDateString());
    }

    private function batchCountSubquery()
    {
        return InventoryBatch::query()
            ->selectRaw('COUNT(*)')
            ->whereColumn('inventory_batches.ingredient_id', 'ingredients.id');
    }

    private function formatIngredientResponse(Ingredient $ingredient, bool $includeBatches = false): array
    {
        $totalStock = (float) ($ingredient->total_stock ?? $ingredient->current_stock ?? 0);
        $stockValue = (float) ($ingredient->stock_value ?? 0);
        $nearestExpiryDate = $ingredient->nearest_expiry_date ?? null;
        $threshold = (float) ($ingredient->threshold ?? 0);
        $status = $this->resolveStockStatus($totalStock, $threshold, $nearestExpiryDate);

        $data = [
            'id' => $ingredient->id,
            'name' => $ingredient->name,
            'current_stock' => $totalStock,
            'total_stock' => $totalStock,
            'unit' => $ingredient->unit ?? 'unit',
            'threshold' => $threshold,
            'stock_value' => $stockValue,
            'nearest_expiry_date' => $nearestExpiryDate,
            'stock_status' => $status,
            'batches_count' => (int) ($ingredient->batches_count ?? 0),
            'created_at' => $ingredient->created_at,
            'updated_at' => $ingredient->updated_at,
        ];

        if ($includeBatches) {
            $data['batches'] = $ingredient->relationLoaded('batches')
                ? $ingredient->batches->values()
                : [];

            $data['transactions'] = $ingredient->relationLoaded('transactions')
                ? $ingredient->transactions->values()
                : [];
        } else {
            $data['batches'] = [];
        }

        return $data;
    }

    private function freshFormattedIngredient(int $ingredientId, bool $includeBatches = false): array
    {
        $query = $this->baseIngredientQuery()
            ->where('ingredients.id', $ingredientId);

        if ($includeBatches) {
            $query->with([
                'batches' => function ($query) {
                    $query->orderBy('expiry_date', 'asc');
                },
                'transactions' => function ($query) {
                    $query->latest();
                },
            ]);
        }

        $ingredient = $query->firstOrFail();

        return $this->formatIngredientResponse($ingredient, $includeBatches);
    }

    private function resolveStockStatus(float $usableStock, float $threshold, $nearestExpiryDate): string
    {
        if ($usableStock <= 0) {
            return 'out_of_stock';
        }

        if ($nearestExpiryDate) {
            $daysUntilExpiry = now()
                ->startOfDay()
                ->diffInDays(
                    Carbon::parse($nearestExpiryDate)->startOfDay(),
                    false
                );

            if ($daysUntilExpiry >= 0 && $daysUntilExpiry <= 3) {
                return 'near_expiry';
            }
        }

        if ($threshold > 0 && $usableStock < $threshold) {
            return 'low_stock';
        }

        if ($threshold > 0 && $usableStock == $threshold) {
            return 'reorder_soon';
        }

        return 'active';
    }

    private function syncExpiredAndUsedUpBatches(): void
    {
        if (! Schema::hasTable('inventory_batches')) {
            return;
        }

        $today = now()->toDateString();

        InventoryBatch::where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '<', $today)
            ->where('status', '!=', 'expired')
            ->update([
                'status' => 'expired',
            ]);

        InventoryBatch::where('quantity_remaining', '<=', 0)
            ->where('status', '!=', 'used_up')
            ->update([
                'status' => 'used_up',
            ]);
    }

    private function syncIngredientStock(Ingredient $ingredient): void
    {
        if (! Schema::hasTable('inventory_batches')) {
            return;
        }

        $today = now()->toDateString();

        InventoryBatch::where('ingredient_id', $ingredient->id)
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '<', $today)
            ->where('status', '!=', 'expired')
            ->update([
                'status' => 'expired',
            ]);

        InventoryBatch::where('ingredient_id', $ingredient->id)
            ->where('quantity_remaining', '<=', 0)
            ->where('status', '!=', 'used_up')
            ->update([
                'status' => 'used_up',
            ]);

        $totalUsableStock = InventoryBatch::where('ingredient_id', $ingredient->id)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '>=', $today)
            ->sum('quantity_remaining');

        $ingredient->forceFill([
            'current_stock' => $totalUsableStock,
        ])->saveQuietly();
    }

    private function refreshLinkedMenuAvailability(Ingredient $ingredient): void
    {
        $ingredient->loadMissing('menuItems.ingredients');

        foreach ($ingredient->menuItems as $menuItem) {
            if ($menuItem->category === 'Chef Oppa Special' || $menuItem->inventory_type === 'custom') {
                $menuItem->forceFill([
                    'inventory_type' => 'custom',
                    'daily_limit' => null,
                    'is_available' => true,
                ])->saveQuietly();

                continue;
            }

            $menuItem->refreshAvailability();
        }
    }

    private function recordInventoryTransaction(
        Ingredient $ingredient,
        ?int $batchId,
        string $type,
        float $quantity,
        float $unitCost,
        string $remarks
    ): void {
        if (! Schema::hasTable('inventory_transactions')) {
            return;
        }

        InventoryTransaction::create([
            'ingredient_id' => $ingredient->id,
            'inventory_batch_id' => $batchId,
            'type' => $type,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $quantity * $unitCost,
            'remarks' => $remarks,
        ]);
    }

    private function getBatchStatusByDateAndQuantity(string $expiryDate, float $quantityRemaining): string
    {
        if ($quantityRemaining <= 0) {
            return 'used_up';
        }

        if ($expiryDate < now()->toDateString()) {
            return 'expired';
        }

        return 'active';
    }
}
