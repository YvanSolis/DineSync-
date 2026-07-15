<?php

namespace App\Http\Controllers\ServiceStaff;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\AuditService;
use App\Services\RefillService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RefillController extends Controller
{
    public function store(
        Request $request,
        Order $order,
        OrderItem $orderItem,
        RefillService $refillService
    ) {
        $validated = $request->validate([
            'ingredient_id' => [
                'required',
                'integer',
                'exists:ingredients,id',
            ],
        ]);

        if ((int) $orderItem->order_id !== (int) $order->id) {
            return back()->with(
                'error',
                'The selected item does not belong to this order.'
            );
        }

        $ingredient = Ingredient::findOrFail(
            $validated['ingredient_id']
        );

        try {
            $refill = $refillService->addRefill(
                $order,
                $orderItem,
                $ingredient,
                $request->user()
            );

            AuditService::record(
                module: 'Refills',
                action: 'request',
                description: "Requested {$ingredient->name} refill for order {$order->order_number}.",
                auditable: $refill,
                oldValues: [],
                newValues: [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'order_item_id' => $orderItem->id,
                    'menu_item_id' => $orderItem->menu_item_id,
                    'ingredient_id' => $ingredient->id,
                    'ingredient_name' => $ingredient->name,
                    'status' => $refill->status,
                ],
                request: $request
            );

            return back()->with(
                'success',
                "{$ingredient->name} refill request sent to the kitchen successfully."
            );
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())
                ->flatten()
                ->first();

            return back()->with(
                'error',
                $message ?: 'Unable to record refill.'
            );
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'Something went wrong while recording the refill.'
            );
        }
    }
}
