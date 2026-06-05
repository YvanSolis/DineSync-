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
        $successRedirectUrl = config('services.xendit.success_redirect_url');
        $failureRedirectUrl = config('services.xendit.failure_redirect_url');

        if (!$secretKey) {
            throw new RuntimeException('Xendit secret key is not configured. Please check your .env file.');
        }

        if (!$successRedirectUrl || !$failureRedirectUrl) {
            throw new RuntimeException('Xendit redirect URLs are not configured. Please check config/services.php and your .env file.');
        }

        $externalId = 'RESERVATION-' . $reservation->id;

        $payload = [
            'external_id' => $externalId,
            'amount' => (int) round($reservation->reservation_fee_amount),
            'description' => 'DineSync+ Reservation Fee - Reservation #' . $reservation->id,
            'currency' => 'PHP',
            'payer_email' => $reservation->customer_email,

            // Redirect after payment attempt
            'success_redirect_url' => $successRedirectUrl,
            'failure_redirect_url' => $failureRedirectUrl,

            // Helpful settings
            'should_send_email' => true,

            // 24 hours before invoice expires
            'expiry_duration' => 86400,

            // Optional metadata for easier tracing
            'customer' => [
                'given_names' => $reservation->customer_name,
                'email' => $reservation->customer_email,
                'mobile_number' => $reservation->customer_phone,
            ],
        ];

        $response = Http::withBasicAuth($secretKey, '')
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post('https://api.xendit.co/v2/invoices', $payload);

        if (!$response->successful()) {
            Log::error('Xendit invoice creation failed', [
                'reservation_id' => $reservation->id,
                'external_id' => $externalId,
                'status' => $response->status(),
                'body' => $response->json(),
                'raw_body' => $response->body(),
            ]);

            throw new RuntimeException('Unable to create Xendit invoice. Status: ' . $response->status());
        }

        $invoice = $response->json();

        Log::info('Xendit invoice created successfully', [
            'reservation_id' => $reservation->id,
            'external_id' => $externalId,
            'xendit_invoice_id' => $invoice['id'] ?? null,
            'invoice_url' => $invoice['invoice_url'] ?? null,
        ]);

        return $invoice;
    }
}