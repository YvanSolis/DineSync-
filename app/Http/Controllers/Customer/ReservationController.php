<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\RestaurantSetting;
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        $settings = RestaurantSetting::current();

        $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'reservation_time' => ['required'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:30'],
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_reference' => ['required', 'string', 'max:255'],
            'payment_proof' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $proofPath = $request->file('payment_proof')->store('reservation_payment_proofs', 'public');

        Reservation::create([
            'user_id' => auth()->id(),
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'reservation_date' => $request->reservation_date,
            'reservation_time' => $request->reservation_time,
            'guest_count' => $request->guest_count,
            'reservation_fee_amount' => $settings->reservation_fee,
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
            'payment_proof' => $proofPath,
            'payment_status' => 'pending',
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('customer.reservations.index')
            ->with('success', 'Your reservation request has been submitted. Please wait for admin verification.');
    }
}