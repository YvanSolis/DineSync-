<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
                'incoming_token' => $incomingToken,
            ]);

            return response()->json([
                'message' => 'Invalid callback token',
            ], 403);
        }

        $payload = $request->all();

        $externalId = $payload['external_id'] ?? null;
        $invoiceId = $payload['id'] ?? null;
        $status = strtoupper((string) ($payload['status'] ?? ''));

        if (!$externalId && !$invoiceId) {
            Log::warning('Xendit webhook missing invoice identifier', [
                'payload' => $payload,
            ]);

            return response()->json([
                'message' => 'No invoice identifier found',
            ], 200);
        }

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

        Log::info('Xendit webhook received but status not handled', [
            'reservation_id' => $reservation->id,
            'status' => $status,
        ]);

        return response()->json([
            'message' => 'Webhook received, but status not updated',
            'status' => $status,
        ], 200);
    }
}