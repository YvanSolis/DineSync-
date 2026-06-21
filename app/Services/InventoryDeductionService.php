<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Ingredient;
use App\Models\InventoryBatch;
use App\Models\InventoryTransaction;
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

                /*
                * NEW DAILY MENU INVENTORY LOGIC
                * per_order = ala carte, counted by order quantity
                * per_head = unlimited, counted by heads/persons
                * custom = Chef Oppa Special, staff confirms
                *
                * These new inventory types should NOT deduct old ingredient stock.
                */
                if (Schema::hasColumn('menu_items', 'inventory_type')) {
                    $inventoryType = $menuItem->inventory_type ?? 'per_order';

                    if (in_array($inventoryType, ['per_order', 'per_head', 'custom'])) {
                        continue;
                    }
                }

                /*
                * OLD INGREDIENT DEDUCTION FALLBACK
                * This only runs for old menu items without the new inventory_type logic.
                */
                foreach ($menuItem->ingredients as $ingredient) {
                    $quantityRequired = (float) $ingredient->pivot->quantity_required;
                    $orderQuantity = (float) $orderItem->quantity;

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
                unitCost: (float) $batch->unit_cost,
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