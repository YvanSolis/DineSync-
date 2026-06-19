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
        $callbackToken = config('services.xendit.callback_token');
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
        $order = null;

        if ($externalId && str_starts_with($externalId, 'ORDER-')) {
            $orderId = (int) str_replace('ORDER-', '', $externalId);

            $order = Order::where('id', $orderId)
                ->orWhere('xendit_external_id', $externalId)
                ->orWhere('xendit_invoice_id', $invoiceId)
                ->first();
        } else {
            $order = Order::where('xendit_invoice_id', $invoiceId)
                ->orWhere('xendit_external_id', $externalId)
                ->first();
        }

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
            $updateData['payment_status'] = 'paid';
            $updateData['paid_at'] = now();
        } elseif ($status === 'EXPIRED') {
            $updateData['payment_status'] = 'expired';
        } elseif (in_array($status, ['FAILED', 'VOIDED'], true)) {
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

        /*
        |--------------------------------------------------------------------------
        | Important:
        |--------------------------------------------------------------------------
        | Do NOT update orders.status here.
        | Payment status and kitchen status must remain separate.
        | Kitchen/KDS should be the only one changing:
        | pending -> preparing -> ready -> served
        */

        $order->update($updateData);

        Log::info('Order payment status updated by Xendit webhook', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'external_id' => $externalId,
            'invoice_id' => $invoiceId,
            'xendit_status' => $status,
            'payment_status' => $updateData['payment_status'],
            'order_status_still' => $order->fresh()->status,
        ]);

        return response()->json([
            'message' => 'Order payment status updated',
            'payment_status' => $updateData['payment_status'],
        ], 200);
    }

    private function handleReservationWebhook(
        ?string $externalId,
        ?string $invoiceId,
        string $status,
        array $payload
    ) {
        $reservation = null;

        if ($externalId && str_starts_with($externalId, 'RESERVATION-')) {
            $reservationId = (int) str_replace('RESERVATION-', '', $externalId);

            $reservation = Reservation::where('id', $reservationId)
                ->orWhere('xendit_external_id', $externalId)
                ->orWhere('xendit_invoice_id', $invoiceId)
                ->first();
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
                'paid_at' => now(),
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

        if (in_array($status, ['FAILED', 'VOIDED'], true)) {
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