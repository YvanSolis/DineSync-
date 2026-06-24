<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Reservation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class XenditService
{
    public function createReservationInvoice(Reservation $reservation): array
    {
        $externalId = 'RESERVATION-' . $reservation->id;

        $payload = [
            'external_id' => $externalId,
            'amount' => (int) round($reservation->reservation_fee_amount),
            'description' => 'DineSync+ Reservation Fee - Reservation #' . $reservation->id,
            'currency' => 'PHP',
            'payer_email' => $reservation->customer_email,
            'success_redirect_url' => route('customer.reservations.index'),
            'failure_redirect_url' => route('customer.reservations.index'),
            'should_send_email' => true,
            'expiry_duration' => 86400,
            'customer' => [
                'given_names' => $reservation->customer_name,
                'email' => $reservation->customer_email,
                'mobile_number' => $reservation->customer_phone,
            ],
        ];

        return $this->createInvoice($payload, [
            'type' => 'reservation',
            'reservation_id' => $reservation->id,
            'external_id' => $externalId,
        ]);
    }

    public function createOrderInvoice(Order $order): array
    {
        $externalId = 'ORDER-' . $order->id . '-' . now()->format('YmdHis');

        $payload = [
            'external_id' => $externalId,
            'amount' => (int) round($order->total_amount ?? 0),
            'description' => 'Chef Oppa Order Payment - ' . ($order->order_number ?? ('Order #' . $order->id)),
            'currency' => 'PHP',

            /*
             * QR PH only:
             * Requires QRPH / QR Code payment method enabled in Xendit dashboard.
             */
            'payment_methods' => [
                'QRPH',
            ],

            'success_redirect_url' => route('service.payments'),
            'failure_redirect_url' => route('service.payments'),
            'should_send_email' => false,
            'expiry_duration' => 3600,

            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'table_number' => $order->table_number,
                'source' => 'service_payment_center',
            ],
        ];

        return $this->createInvoice($payload, [
            'type' => 'order',
            'order_id' => $order->id,
            'external_id' => $externalId,
        ]);
    }

    private function createInvoice(array $payload, array $logContext = []): array
    {
        $secretKey = config('services.xendit.secret_key') ?: env('XENDIT_SECRET_KEY');

        if (!$secretKey) {
            throw new RuntimeException('Xendit secret key is not configured. Please check your .env file.');
        }

        $request = Http::withBasicAuth($secretKey, '')
            ->acceptJson()
            ->asJson()
            ->timeout(30);

        if (app()->environment(['local', 'development'])) {
            $request = $request->withoutVerifying();
        }

        $response = $request->post('https://api.xendit.co/v2/invoices', $payload);

        if (!$response->successful()) {
            Log::error('Xendit invoice creation failed', array_merge($logContext, [
                'status' => $response->status(),
                'body' => $response->json(),
                'raw_body' => $response->body(),
            ]));

            throw new RuntimeException('Unable to create Xendit invoice. Status: ' . $response->status());
        }

        $invoice = $response->json();

        Log::info('Xendit invoice created successfully', array_merge($logContext, [
            'xendit_invoice_id' => $invoice['id'] ?? null,
            'invoice_url' => $invoice['invoice_url'] ?? null,
        ]));

        return $invoice;
    }
}