<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\RestaurantSetting;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('customer.reservations.index', compact('reservations'));
    }

    public function create()
    {
        $settings = RestaurantSetting::current();
        $reservationFee = $settings->reservation_fee;

        return view('customer.reservations.create', compact(
            'settings',
            'reservationFee'
        ));
    }

    public function store(Request $request, XenditService $xenditService)
    {
        $settings = RestaurantSetting::current();

        $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'reservation_time' => ['required'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:30'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $reservation = DB::transaction(function () use ($request, $settings) {
                return Reservation::create([
                    'user_id' => auth()->id(),
                    'customer_name' => $request->customer_name,
                    'customer_email' => $request->customer_email,
                    'customer_phone' => $request->customer_phone,
                    'reservation_date' => $request->reservation_date,
                    'reservation_time' => $request->reservation_time,
                    'guest_count' => $request->guest_count,
                    'reservation_fee_amount' => $settings->reservation_fee,

                    // Xendit payment flow
                    'payment_method' => 'Xendit',
                    'payment_reference' => null,
                    'payment_proof' => null,
                    'payment_status' => 'pending',

                    'xendit_invoice_id' => null,
                    'xendit_external_id' => null,
                    'xendit_invoice_url' => null,
                    'xendit_expiry_date' => null,

                    'notes' => $request->notes,
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
                ->with('error', 'Unable to create payment link right now. Please try again.');
        }
    }
}