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

        if ($callbackToken && $incomingToken !== $callbackToken) {
            Log::warning('Invalid Xendit callback token', [
                'incoming_token' => $incomingToken,
            ]);

            return response()->json([
                'message' => 'Invalid callback token',
            ], 403);
        }

        $payload = $request->all();

        Log::info('Xendit webhook received', [
            'payload' => $payload,
        ]);

        $externalId = $payload['external_id'] ?? null;
        $invoiceId = $payload['id'] ?? null;
        $status = strtoupper($payload['status'] ?? '');

        if (!$externalId && !$invoiceId) {
            return response()->json([
                'message' => 'No invoice identifier found',
            ], 200);
        }

        if ($externalId && str_starts_with($externalId, 'RESERVATION-')) {
            $reservationId = (int) str_replace('RESERVATION-', '', $externalId);

            $reservation = Reservation::where('id', $reservationId)
                ->orWhere('xendit_external_id', $externalId)
                ->orWhere('xendit_invoice_id', $invoiceId)
                ->first();

            if (!$reservation) {
                Log::warning('Xendit reservation not found', [
                    'external_id' => $externalId,
                    'invoice_id' => $invoiceId,
                ]);

                return response()->json([
                    'message' => 'Reservation not found',
                ], 200);
            }

            if ($status === 'PAID' || $status === 'SETTLED') {
                $reservation->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]);

                return response()->json([
                    'message' => 'Reservation payment marked as paid',
                ], 200);
            }

            if ($status === 'EXPIRED') {
                $reservation->update([
                    'payment_status' => 'expired',
                ]);

                return response()->json([
                    'message' => 'Reservation payment marked as expired',
                ], 200);
            }

            return response()->json([
                'message' => 'Reservation webhook received, but status not updated',
                'status' => $status,
            ], 200);
        }

        return response()->json([
            'message' => 'Webhook received, but no matching handler yet',
        ], 200);
    }
}