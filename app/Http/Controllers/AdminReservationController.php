<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class AdminReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::latest()
            ->paginate(10);

        return view('admin.reservations', compact('reservations'));
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        $request->validate([
            'status' => ['required', 'in:pending,approved,declined,completed,cancelled'],
        ]);

        $reservation->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Reservation status updated successfully.');
    }

    public function verifyPayment(Reservation $reservation)
    {
        $reservation->update([
            'payment_status' => 'verified',
            'status' => 'approved',
        ]);

        return back()->with('success', 'Payment verified and reservation approved.');
    }

    public function rejectPayment(Reservation $reservation)
    {
        $reservation->update([
            'payment_status' => 'rejected',
            'status' => 'declined',
        ]);

        return back()->with('success', 'Payment rejected and reservation declined.');
    }
}