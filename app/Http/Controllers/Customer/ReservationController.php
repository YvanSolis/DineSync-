<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\RestaurantSetting;
use App\Services\XenditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
    private const TIMEZONE = 'Asia/Manila';
    private const RESERVATION_START_TIME = '11:00';
    private const RESERVATION_END_TIME = '19:00';
    private const MAX_RESERVATIONS_PER_SLOT = 5;

    public function index()
    {
        $reservations = Reservation::where('user_id', auth()->id())
            ->latest()
            ->get();

        $this->syncPendingReservationPayments($reservations);

        $reservations = Reservation::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('customer.reservations.index', compact('reservations'));
    }

    public function create()
    {
        $settings = RestaurantSetting::current();
        $reservationFee = $settings->reservation_fee;
        $user = auth()->user();

        return view('customer.reservations.create', compact(
            'settings',
            'reservationFee',
            'user'
        ));
    }

    public function store(Request $request, XenditService $xenditService)
    {
        $settings = RestaurantSetting::current();

        $validated = $this->validateReservationRequest($request);

        try {
            $reservation = DB::transaction(function () use ($validated, $settings) {
                $this->ensureSlotIsAvailable(
                    $validated['reservation_date'],
                    $validated['reservation_time']
                );

                return Reservation::create([
                    'user_id' => auth()->id(),
                    'customer_name' => $validated['customer_name'],
                    'customer_email' => $validated['customer_email'],
                    'customer_phone' => $validated['customer_phone'],
                    'reservation_date' => $validated['reservation_date'],
                    'reservation_time' => $validated['reservation_time'],
                    'guest_count' => $validated['guest_count'],
                    'reservation_fee_amount' => $settings->reservation_fee,

                    'payment_method' => 'Xendit',
                    'payment_reference' => null,
                    'payment_proof' => null,
                    'payment_status' => 'pending',

                    'xendit_invoice_id' => null,
                    'xendit_external_id' => null,
                    'xendit_invoice_url' => null,
                    'xendit_expiry_date' => null,

                    'notes' => $validated['notes'] ?? null,
                    'status' => 'pending',
                ]);
            });

            $invoice = $xenditService->createReservationInvoice($reservation);

            $reservation->update([
                'xendit_invoice_id' => $invoice['id'] ?? null,
                'xendit_external_id' => $invoice['external_id'] ?? ('RESERVATION-' . $reservation->id),
                'xendit_invoice_url' => $invoice['invoice_url'] ?? null,
                'xendit_expiry_date' => $invoice['expiry_date'] ?? null,
            ]);

            if (empty($invoice['invoice_url'])) {
                Log::warning('Reservation created but Xendit invoice URL is missing', [
                    'reservation_id' => $reservation->id,
                    'invoice' => $invoice,
                ]);

                return redirect()
                    ->route('customer.reservations.index')
                    ->with('error', 'Reservation was created, but the payment link was not generated. Please contact staff.');
            }

            return redirect()->away($invoice['invoice_url']);
        } catch (\Throwable $e) {
            Log::error('Reservation Xendit payment creation failed', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage() ?: 'Unable to create payment link right now. Please try again.');
        }
    }

    public function edit(Reservation $reservation)
    {
        $this->authorizeCustomerReservation($reservation);

        if (!$this->canCustomerManageReservation($reservation)) {
            return redirect()
                ->route('customer.reservations.index')
                ->with('error', 'This reservation can no longer be edited.');
        }

        $settings = RestaurantSetting::current();
        $reservationFee = $reservation->reservation_fee_amount ?? $settings->reservation_fee;

        return view('customer.reservations.edit', compact(
            'reservation',
            'settings',
            'reservationFee'
        ));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $this->authorizeCustomerReservation($reservation);

        if (!$this->canCustomerManageReservation($reservation)) {
            return redirect()
                ->route('customer.reservations.index')
                ->with('error', 'This reservation can no longer be updated.');
        }

        $validated = $this->validateReservationRequest($request, $reservation->id);

        try {
            DB::transaction(function () use ($reservation, $validated) {
                $this->ensureSlotIsAvailable(
                    $validated['reservation_date'],
                    $validated['reservation_time'],
                    $reservation->id
                );

                $reservation->update([
                    'customer_name' => $validated['customer_name'],
                    'customer_email' => $validated['customer_email'],
                    'customer_phone' => $validated['customer_phone'],
                    'reservation_date' => $validated['reservation_date'],
                    'reservation_time' => $validated['reservation_time'],
                    'guest_count' => $validated['guest_count'],
                    'notes' => $validated['notes'] ?? null,
                ]);
            });

            return redirect()
                ->route('customer.reservations.index')
                ->with('success', 'Reservation updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Customer reservation update failed', [
                'message' => $e->getMessage(),
                'reservation_id' => $reservation->id,
                'user_id' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage() ?: 'Unable to update reservation. Please try again.');
        }
    }

    public function destroy(Reservation $reservation)
    {
        $this->authorizeCustomerReservation($reservation);

        if (!$this->canCustomerManageReservation($reservation)) {
            return redirect()
                ->route('customer.reservations.index')
                ->with('error', 'This reservation can no longer be cancelled.');
        }

        $reservation->update([
            'status' => 'cancelled',
        ]);

        return redirect()
            ->route('customer.reservations.index')
            ->with('success', 'Reservation cancelled successfully.');
    }

    private function validateReservationRequest(Request $request, ?int $ignoreReservationId = null): array
    {
        return $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'reservation_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request, $ignoreReservationId) {
                    $openingTime = Carbon::createFromFormat('H:i', self::RESERVATION_START_TIME, self::TIMEZONE);
                    $closingTime = Carbon::createFromFormat('H:i', self::RESERVATION_END_TIME, self::TIMEZONE);

                    try {
                        $reservationDate = Carbon::parse($request->reservation_date, self::TIMEZONE)->toDateString();
                        $reservationTime = Carbon::createFromFormat('H:i', $value, self::TIMEZONE);
                    } catch (\Throwable $e) {
                        $fail('Please select a valid reservation date and time.');
                        return;
                    }

                    if ($reservationTime->lt($openingTime) || $reservationTime->gt($closingTime)) {
                        $fail('Reservation time must be between 11:00 AM and 7:00 PM.');
                        return;
                    }

                    $minutes = (int) $reservationTime->format('i');

                    if (!in_array($minutes, [0, 30], true)) {
                        $fail('Reservation time must be in 30-minute intervals.');
                        return;
                    }

                    $now = now(self::TIMEZONE);
                    $today = $now->toDateString();

                    if ($reservationDate === $today) {
                        $reservationDateTime = Carbon::parse($reservationDate . ' ' . $value, self::TIMEZONE);

                        if ($reservationDateTime->lte($now)) {
                            $fail('Reservation time must not be in the past.');
                            return;
                        }
                    }

                    $existingReservations = Reservation::whereDate('reservation_date', $reservationDate)
                        ->where('reservation_time', $value)
                        ->whereNotIn('status', ['declined', 'cancelled', 'completed'])
                        ->when($ignoreReservationId, function ($query) use ($ignoreReservationId) {
                            $query->where('id', '!=', $ignoreReservationId);
                        })
                        ->count();

                    if ($existingReservations >= self::MAX_RESERVATIONS_PER_SLOT) {
                        $fail('This reservation time is already full. Please choose another time slot.');
                    }
                },
            ],
            'guest_count' => ['required', 'integer', 'min:1', 'max:30'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function ensureSlotIsAvailable(string $date, string $time, ?int $ignoreReservationId = null): void
    {
        $existingReservations = Reservation::whereDate('reservation_date', $date)
            ->where('reservation_time', $time)
            ->whereNotIn('status', ['declined', 'cancelled', 'completed'])
            ->when($ignoreReservationId, function ($query) use ($ignoreReservationId) {
                $query->where('id', '!=', $ignoreReservationId);
            })
            ->count();

        if ($existingReservations >= self::MAX_RESERVATIONS_PER_SLOT) {
            throw new \Exception('This reservation time is already full. Please choose another time slot.');
        }
    }

    private function authorizeCustomerReservation(Reservation $reservation): void
    {
        if ((int) $reservation->user_id !== (int) auth()->id()) {
            abort(403);
        }
    }

    private function canCustomerManageReservation(Reservation $reservation): bool
    {
        $reservationStatus = strtolower($reservation->status ?? 'pending');

        $lockedReservationStatuses = [
            'approved',
            'arrived',
            'seated',
            'completed',
            'declined',
            'cancelled',
        ];

        return !in_array($reservationStatus, $lockedReservationStatuses, true);
    }

    private function syncPendingReservationPayments($reservations): void
    {
        $secretKey = config('services.xendit.secret_key') ?: env('XENDIT_SECRET_KEY');

        if (empty($secretKey)) {
            Log::warning('Xendit sync skipped because XENDIT_SECRET_KEY is missing.');
            return;
        }

        foreach ($reservations as $reservation) {
            $paymentStatus = strtolower($reservation->payment_status ?? 'pending');

            if (in_array($paymentStatus, ['paid', 'verified', 'settled', 'completed'], true)) {
                continue;
            }

            if (empty($reservation->xendit_invoice_id)) {
                continue;
            }

            try {
                $httpRequest = Http::withBasicAuth($secretKey, '')
                    ->acceptJson()
                    ->timeout(15);

                if (app()->environment(['local', 'development'])) {
                    $httpRequest = $httpRequest->withoutVerifying();
                }

                $response = $httpRequest->get(
                    'https://api.xendit.co/v2/invoices/' . $reservation->xendit_invoice_id
                );

                if (!$response->successful()) {
                    Log::warning('Xendit invoice sync failed', [
                        'reservation_id' => $reservation->id,
                        'xendit_invoice_id' => $reservation->xendit_invoice_id,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    continue;
                }

                $invoice = $response->json();
                $invoiceStatus = strtoupper($invoice['status'] ?? '');

                if (in_array($invoiceStatus, ['PAID', 'SETTLED'], true)) {
                    $reservation->update([
                        'payment_status' => 'paid',
                        'payment_reference' => $invoice['id'] ?? $reservation->xendit_invoice_id,
                        'paid_at' => $reservation->paid_at ?? now(self::TIMEZONE),
                    ]);

                    continue;
                }

                if ($invoiceStatus === 'EXPIRED') {
                    $reservation->update([
                        'payment_status' => 'expired',
                    ]);

                    continue;
                }

                if (in_array($invoiceStatus, ['FAILED', 'CANCELLED', 'CANCELED'], true)) {
                    $reservation->update([
                        'payment_status' => 'failed',
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Xendit invoice sync error', [
                    'reservation_id' => $reservation->id,
                    'xendit_invoice_id' => $reservation->xendit_invoice_id,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}