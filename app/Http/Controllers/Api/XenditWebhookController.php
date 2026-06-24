<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $callbackToken = config('services.xendit.callback_token') ?: env('XENDIT_WEBHOOK_TOKEN');
        $incomingToken = $request->header('x-callback-token');

        Log::info('Xendit webhook attempt received', [
            'incoming_token_exists' => !empty($incomingToken),
            'configured_token_exists' => !empty($callbackToken),
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
        ]);

        if ($callbackToken && $incomingToken !== $callbackToken) {
            Log::warning('Invalid Xendit callback token', [
                'incoming_token_exists' => !empty($incomingToken),
            ]);

            return response()->json([
                'message' => 'Invalid callback token',
            ], 403);
        }

        $payload = $request->all();

        $externalId = $payload['external_id'] ?? null;
        $invoiceId = $payload['id'] ?? null;
        $invoiceUrl = $payload['invoice_url'] ?? null;
        $status = strtoupper((string) ($payload['status'] ?? ''));

        if (!$externalId && !$invoiceId) {
            Log::warning('Xendit webhook missing invoice identifier', [
                'payload' => $payload,
            ]);

            return response()->json([
                'message' => 'No invoice identifier found',
            ], 200);
        }

        if ($externalId && str_starts_with($externalId, 'ORDER-')) {
            return $this->handleOrderWebhook(
                externalId: $externalId,
                invoiceId: $invoiceId,
                invoiceUrl: $invoiceUrl,
                status: $status,
                payload: $payload
            );
        }

        return $this->handleReservationWebhook(
            externalId: $externalId,
            invoiceId: $invoiceId,
            status: $status,
            payload: $payload
        );
    }

    private function handleOrderWebhook(
        ?string $externalId,
        ?string $invoiceId,
        ?string $invoiceUrl,
        string $status,
        array $payload
    ) {
        $order = $this->findOrderFromXenditPayload($externalId, $invoiceId);

        if (!$order) {
            Log::warning('Xendit order not found', [
                'external_id' => $externalId,
                'invoice_id' => $invoiceId,
                'status' => $status,
            ]);

            return response()->json([
                'message' => 'Order not found',
            ], 200);
        }

        $updateData = [
            'xendit_invoice_id' => $invoiceId ?? $order->xendit_invoice_id,
            'xendit_external_id' => $externalId ?? $order->xendit_external_id,
            'xendit_invoice_url' => $invoiceUrl ?? $order->xendit_invoice_url,
            'updated_at' => now(),
        ];

        if (in_array($status, ['PAID', 'SETTLED'], true)) {
            $currentOrderStatus = strtolower(trim($order->status ?? 'pending'));

            $updateData['payment_status'] = 'paid';
            $updateData['paid_at'] = $order->paid_at ?? now();

            /*
            |--------------------------------------------------------------------------
            | Important Payment Flow Rule
            |--------------------------------------------------------------------------
            | Pay at Counter / Digital Payment orders are stored as awaiting_payment
            | so they do NOT appear in KDS before payment.
            |
            | Once Xendit confirms QR PH payment, we only move awaiting_payment
            | orders into pending so KDS can receive them.
            |
            | If the order is already preparing/ready/served, we do not reset it.
            */
            if ($currentOrderStatus === 'awaiting_payment') {
                $updateData['status'] = 'pending';
            }
        } elseif ($status === 'EXPIRED') {
            $updateData['payment_status'] = 'expired';
        } elseif (in_array($status, ['FAILED', 'VOIDED', 'CANCELLED', 'CANCELED'], true)) {
            $updateData['payment_status'] = 'failed';
        } else {
            Log::info('Xendit order webhook received but status not handled', [
                'order_id' => $order->id,
                'external_id' => $externalId,
                'invoice_id' => $invoiceId,
                'status' => $status,
            ]);

            return response()->json([
                'message' => 'Order webhook received, but status not updated',
                'status' => $status,
            ], 200);
        }

        $order->update($updateData);

        $freshOrder = $order->fresh();

        Log::info('Order payment status updated by Xendit webhook', [
            'order_id' => $freshOrder->id,
            'order_number' => $freshOrder->order_number,
            'external_id' => $externalId,
            'invoice_id' => $invoiceId,
            'xendit_status' => $status,
            'payment_status' => $freshOrder->payment_status,
            'order_status' => $freshOrder->status,
            'paid_at' => $freshOrder->paid_at,
        ]);

        return response()->json([
            'message' => 'Order payment status updated',
            'payment_status' => $freshOrder->payment_status,
            'order_status' => $freshOrder->status,
        ], 200);
    }

    private function findOrderFromXenditPayload(?string $externalId, ?string $invoiceId): ?Order
    {
        if ($invoiceId) {
            $order = Order::where('xendit_invoice_id', $invoiceId)->first();

            if ($order) {
                return $order;
            }
        }

        if ($externalId) {
            $order = Order::where('xendit_external_id', $externalId)->first();

            if ($order) {
                return $order;
            }
        }

        if ($externalId && str_starts_with($externalId, 'ORDER-')) {
            /*
             * Supports both:
             * ORDER-68
             * ORDER-68-202606241806
             */
            if (preg_match('/^ORDER-(\d+)/', $externalId, $matches)) {
                $orderId = (int) $matches[1];

                if ($orderId > 0) {
                    return Order::where('id', $orderId)->first();
                }
            }
        }

        return null;
    }

    private function handleReservationWebhook(
        ?string $externalId,
        ?string $invoiceId,
        string $status,
        array $payload
    ) {
        $reservation = null;

        if ($externalId && str_starts_with($externalId, 'RESERVATION-')) {
            if (preg_match('/^RESERVATION-(\d+)/', $externalId, $matches)) {
                $reservationId = (int) $matches[1];

                $reservation = Reservation::where('id', $reservationId)
                    ->orWhere('xendit_external_id', $externalId)
                    ->orWhere('xendit_invoice_id', $invoiceId)
                    ->first();
            }
        } else {
            $reservation = Reservation::where('xendit_invoice_id', $invoiceId)
                ->orWhere('xendit_external_id', $externalId)
                ->first();
        }

        if (!$reservation) {
            Log::warning('Xendit reservation not found', [
                'external_id' => $externalId,
                'invoice_id' => $invoiceId,
                'status' => $status,
            ]);

            return response()->json([
                'message' => 'Reservation not found',
            ], 200);
        }

        if (in_array($status, ['PAID', 'SETTLED'], true)) {
            $reservation->update([
                'payment_status' => 'paid',
                'paid_at' => $reservation->paid_at ?? now(),
            ]);

            Log::info('Reservation payment marked as paid', [
                'reservation_id' => $reservation->id,
                'external_id' => $externalId,
                'invoice_id' => $invoiceId,
                'status' => $status,
            ]);

            return response()->json([
                'message' => 'Reservation payment marked as paid',
            ], 200);
        }

        if ($status === 'EXPIRED') {
            $reservation->update([
                'payment_status' => 'expired',
            ]);

            Log::info('Reservation payment marked as expired', [
                'reservation_id' => $reservation->id,
                'status' => $status,
            ]);

            return response()->json([
                'message' => 'Reservation payment marked as expired',
            ], 200);
        }

        if (in_array($status, ['FAILED', 'VOIDED', 'CANCELLED', 'CANCELED'], true)) {
            $reservation->update([
                'payment_status' => 'failed',
            ]);

            Log::info('Reservation payment marked as failed', [
                'reservation_id' => $reservation->id,
                'status' => $status,
            ]);

            return response()->json([
                'message' => 'Reservation payment marked as failed',
            ], 200);
        }

        Log::info('Xendit reservation webhook received but status not handled', [
            'reservation_id' => $reservation->id,
            'status' => $status,
        ]);

        return response()->json([
            'message' => 'Reservation webhook received, but status not updated',
            'status' => $status,
        ], 200);
    }
}