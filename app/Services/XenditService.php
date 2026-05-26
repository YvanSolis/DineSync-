<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class XenditService
{
    public function createReservationInvoice(Reservation $reservation): array
    {
        $secretKey = config('services.xendit.secret_key');

        if (!$secretKey) {
            throw new RuntimeException('Xendit secret key is not configured. Please check your .env file.');
        }

        $externalId = 'RESERVATION-' . $reservation->id;

        $payload = [
            'external_id' => $externalId,
            'amount' => (float) $reservation->reservation_fee_amount,
            'description' => 'DineSync+ Reservation Fee - Reservation #' . $reservation->id,
            'currency' => 'PHP',
            'payer_email' => $reservation->customer_email,
            'success_redirect_url' => config('services.xendit.success_redirect_url'),
            'failure_redirect_url' => config('services.xendit.failure_redirect_url'),
        ];

        $response = Http::withBasicAuth($secretKey, '')
            ->acceptJson()
            ->asJson()
            ->post('https://api.xendit.co/v2/invoices', $payload);

        if (!$response->successful()) {
            Log::error('Xendit invoice creation failed', [
                'reservation_id' => $reservation->id,
                'status' => $response->status(),
                'body' => $response->json(),
                'raw_body' => $response->body(),
            ]);

            throw new RuntimeException('Unable to create Xendit invoice. Status: ' . $response->status());
        }

        return $response->json();
    }
}