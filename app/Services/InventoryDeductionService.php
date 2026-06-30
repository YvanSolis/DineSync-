<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Ingredient;
use App\Models\InventoryBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class InventoryDeductionService
{
    public function deductForOrder(Order $order): void
    {
        if (Schema::hasColumn('orders', 'inventory_deducted_at') && $order->inventory_deducted_at) {
            return;
        }

        if (
            Schema::hasTable('ingredient_usages') &&
            Schema::hasColumn('ingredient_usages', 'order_id') &&
            DB::table('ingredient_usages')->where('order_id', $order->id)->exists()
        ) {
            return;
        }

        $order->load('items.menuItem.ingredients');

        DB::transaction(function () use ($order) {
            foreach ($order->items as $orderItem) {
                $menuItem = $orderItem->menuItem;

                if (!$menuItem) {
                    continue;
                }

                $inventoryType = Schema::hasColumn('menu_items', 'inventory_type')
                    ? strtolower(trim($menuItem->inventory_type ?? 'ingredient'))
                    : 'ingredient';

                $category = strtolower(trim($menuItem->category ?? ''));

                /*
                |--------------------------------------------------------------------------
                | Chef Oppa Special / Custom
                |--------------------------------------------------------------------------
                | Custom requests should not deduct ingredient stock automatically.
                */
                if ($inventoryType === 'custom' || $category === strtolower('Chef Oppa Special')) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Ingredient Deduction
                |--------------------------------------------------------------------------
                | IMPORTANT:
                | Do NOT skip per_order or per_head anymore.
                | Your menu availability is currently computed from linked ingredients,
                | so paid/confirmed orders must deduct linked ingredient batches.
                */
                $ingredients = $menuItem->ingredients ?? collect();

                if ($ingredients->isEmpty()) {
                    continue;
                }

                foreach ($ingredients as $ingredient) {
                    $quantityRequired = (float) ($ingredient->pivot->quantity_required ?? 0);
                    $orderQuantity = (float) ($orderItem->quantity ?? 0);

                    $totalNeeded = $quantityRequired * $orderQuantity;

                    if ($totalNeeded <= 0) {
                        continue;
                    }

                    $this->deductIngredient(
                        ingredient: $ingredient,
                        quantityNeeded: $totalNeeded,
                        order: $order,
                        orderItemId: $orderItem->id,
                        menuItemId: $menuItem->id,
                        menuItemName: $menuItem->name
                    );
                }
            }

            if (Schema::hasColumn('orders', 'inventory_deducted_at')) {
                $order->inventory_deducted_at = now();
                $order->save();
            }
        });
    }

    private function deductIngredient(
        Ingredient $ingredient,
        float $quantityNeeded,
        Order $order,
        int $orderItemId,
        int $menuItemId,
        string $menuItemName
    ): void {
        $this->markExpiredBatches($ingredient);

        $availableStock = InventoryBatch::where('ingredient_id', $ingredient->id)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->sum('quantity_remaining');

        if ((float) $availableStock < $quantityNeeded) {
            throw ValidationException::withMessages([
                'stock' => "Not enough stock for {$ingredient->name}. Needed: {$quantityNeeded} {$ingredient->unit}, Available: {$availableStock} {$ingredient->unit}.",
            ]);
        }

        $remainingToDeduct = $quantityNeeded;

        $batches = InventoryBatch::where('ingredient_id', $ingredient->id)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->orderBy('expiry_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($batches as $batch) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            $deductAmount = min((float) $batch->quantity_remaining, $remainingToDeduct);

            $batch->quantity_remaining = (float) $batch->quantity_remaining - $deductAmount;

            if ($batch->quantity_remaining <= 0) {
                $batch->quantity_remaining = 0;
                $batch->status = 'used_up';
            }

            $batch->save();

            $this->recordInventoryTransaction(
                ingredient: $ingredient,
                batchId: $batch->id,
                quantity: $deductAmount,
                unitCost: (float) ($batch->unit_cost ?? 0),
                remarks: "Used for order #{$order->id} - {$menuItemName}"
            );

            $remainingToDeduct -= $deductAmount;
        }

        $this->recordIngredientUsage(
            ingredient: $ingredient,
            orderId: $order->id,
            orderItemId: $orderItemId,
            menuItemId: $menuItemId,
            quantityUsed: $quantityNeeded,
            remarks: "Used for {$menuItemName}"
        );

        $this->syncCurrentStock($ingredient);
    }

    private function recordInventoryTransaction(
        Ingredient $ingredient,
        int $batchId,
        float $quantity,
        float $unitCost,
        string $remarks
    ): void {
        if (!Schema::hasTable('inventory_transactions')) {
            return;
        }

        $data = [];

        if (Schema::hasColumn('inventory_transactions', 'ingredient_id')) {
            $data['ingredient_id'] = $ingredient->id;
        }

        if (Schema::hasColumn('inventory_transactions', 'inventory_batch_id')) {
            $data['inventory_batch_id'] = $batchId;
        }

        if (Schema::hasColumn('inventory_transactions', 'type')) {
            $data['type'] = 'stock_out';
        }

        if (Schema::hasColumn('inventory_transactions', 'quantity')) {
            $data['quantity'] = $quantity;
        }

        if (Schema::hasColumn('inventory_transactions', 'unit_cost')) {
            $data['unit_cost'] = $unitCost;
        }

        if (Schema::hasColumn('inventory_transactions', 'total_cost')) {
            $data['total_cost'] = $quantity * $unitCost;
        }

        if (Schema::hasColumn('inventory_transactions', 'remarks')) {
            $data['remarks'] = $remarks;
        }

        if (Schema::hasColumn('inventory_transactions', 'created_at')) {
            $data['created_at'] = now();
        }

        if (Schema::hasColumn('inventory_transactions', 'updated_at')) {
            $data['updated_at'] = now();
        }

        DB::table('inventory_transactions')->insert($data);
    }

    private function recordIngredientUsage(
        Ingredient $ingredient,
        int $orderId,
        int $orderItemId,
        int $menuItemId,
        float $quantityUsed,
        string $remarks
    ): void {
        if (!Schema::hasTable('ingredient_usages')) {
            return;
        }

        $data = [];

        if (Schema::hasColumn('ingredient_usages', 'ingredient_id')) {
            $data['ingredient_id'] = $ingredient->id;
        }

        if (Schema::hasColumn('ingredient_usages', 'order_id')) {
            $data['order_id'] = $orderId;
        }

        if (Schema::hasColumn('ingredient_usages', 'order_item_id')) {
            $data['order_item_id'] = $orderItemId;
        }

        if (Schema::hasColumn('ingredient_usages', 'menu_item_id')) {
            $data['menu_item_id'] = $menuItemId;
        }

        if (Schema::hasColumn('ingredient_usages', 'quantity_used')) {
            $data['quantity_used'] = $quantityUsed;
        }

        if (Schema::hasColumn('ingredient_usages', 'unit')) {
            $data['unit'] = $ingredient->unit;
        }

        if (Schema::hasColumn('ingredient_usages', 'remarks')) {
            $data['remarks'] = $remarks;
        }

        if (Schema::hasColumn('ingredient_usages', 'created_at')) {
            $data['created_at'] = now();
        }

        if (Schema::hasColumn('ingredient_usages', 'updated_at')) {
            $data['updated_at'] = now();
        }

        DB::table('ingredient_usages')->insert($data);
    }

    private function markExpiredBatches(Ingredient $ingredient): void
    {
        InventoryBatch::where('ingredient_id', $ingredient->id)
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '<', now()->toDateString())
            ->update([
                'status' => 'expired',
                'updated_at' => now(),
            ]);

        InventoryBatch::where('ingredient_id', $ingredient->id)
            ->where('quantity_remaining', '<=', 0)
            ->where('status', '!=', 'used_up')
            ->update([
                'status' => 'used_up',
                'updated_at' => now(),
            ]);
    }

    private function syncCurrentStock(Ingredient $ingredient): void
    {
        $this->markExpiredBatches($ingredient);

        $totalUsableStock = InventoryBatch::where('ingredient_id', $ingredient->id)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->sum('quantity_remaining');

        $ingredient->current_stock = $totalUsableStock;
        $ingredient->save();
    }
}