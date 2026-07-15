<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Refill;
use App\Models\RefillItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefillService
{
    public function addRefill(
        Order $order,
        OrderItem $orderItem,
        Ingredient $ingredient,
        ?User $requestedBy = null
    ): Refill {
        return DB::transaction(function () use (
            $order,
            $orderItem,
            $ingredient,
            $requestedBy
        ) {
            if ((int) $orderItem->order_id !== (int) $order->id) {
                throw ValidationException::withMessages([
                    'refill' => 'The selected item does not belong to this order.',
                ]);
            }

            $orderItem->loadMissing('menuItem.ingredients');

            $menuItem = $orderItem->menuItem;

            if (!$menuItem) {
                throw ValidationException::withMessages([
                    'refill' => 'The selected order item has no linked menu item.',
                ]);
            }

            if (!(bool) $menuItem->is_unlimited) {
                throw ValidationException::withMessages([
                    'refill' => 'This menu item is not configured as unlimited.',
                ]);
            }

            $linkedIngredient = $menuItem->ingredients
                ->firstWhere('id', $ingredient->id);

            if (!$linkedIngredient) {
                throw ValidationException::withMessages([
                    'refill' => 'This ingredient is not linked to the selected menu item.',
                ]);
            }

            $isRefillable = (bool) (
                $linkedIngredient->pivot->is_refillable ?? false
            );

            $refillQuantity = (float) (
                $linkedIngredient->pivot->refill_quantity ?? 0
            );

            if (!$isRefillable) {
                throw ValidationException::withMessages([
                    'refill' => 'This ingredient is not enabled for refill.',
                ]);
            }

            if ($refillQuantity <= 0) {
                throw ValidationException::withMessages([
                    'refill' => 'The refill serving quantity is not configured.',
                ]);
            }

            $this->ensureEnoughStock(
                $ingredient,
                $refillQuantity
            );

            $existingRequest = Refill::query()
                ->where('order_id', $order->id)
                ->where('order_item_id', $orderItem->id)
                ->where('menu_item_id', $menuItem->id)
                ->whereHas('items', function ($query) use ($ingredient) {
                    $query->where('ingredient_id', $ingredient->id);
                })
                ->whereIn('status', [
                    'requested',
                    'preparing',
                    'ready',
                ])
                ->lockForUpdate()
                ->first();

            if ($existingRequest) {
                throw ValidationException::withMessages([
                    'refill' => 'This refill already has an active request.',
                ]);
            }

            $refill = Refill::create([
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'menu_item_id' => $menuItem->id,
                'requested_by' => $requestedBy?->id,
                'table_number' => $order->table_number,
                'status' => 'requested',
                'notes' => null,
                'requested_at' => now(),
            ]);

            RefillItem::create([
                'refill_id' => $refill->id,
                'ingredient_id' => $ingredient->id,
                'quantity' => $refillQuantity,
                'unit' => $ingredient->unit,
            ]);

            return $refill->load([
                'order',
                'menuItem',
                'items.ingredient',
            ]);
        });
    }

    private function ensureEnoughStock(
        Ingredient $ingredient,
        float $requiredQuantity
    ): void {
        $availableStock = (float) InventoryBatch::query()
            ->where('ingredient_id', $ingredient->id)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->sum('quantity_remaining');

        if ($availableStock < $requiredQuantity) {
            throw ValidationException::withMessages([
                'refill' => sprintf(
                    'Not enough %s stock. Required: %.2f %s, available: %.2f %s.',
                    $ingredient->name,
                    $requiredQuantity,
                    $ingredient->unit,
                    $availableStock,
                    $ingredient->unit
                ),
            ]);
        }
    }
}