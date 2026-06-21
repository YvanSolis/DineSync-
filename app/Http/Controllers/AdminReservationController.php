<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminReservationController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->query('date', now()->toDateString());

        try {
            $selectedDate = Carbon::parse($selectedDate)->toDateString();
        } catch (\Exception $e) {
            $selectedDate = now()->toDateString();
        }

        $baseQuery = Reservation::query()
            ->whereDate('reservation_date', $selectedDate);

        $totalReservations = (clone $baseQuery)->count();
        $pendingCount = (clone $baseQuery)->where('status', 'pending')->count();
        $approvedCount = (clone $baseQuery)->where('status', 'approved')->count();
        $cancelledCount = (clone $baseQuery)->whereIn('status', ['cancelled', 'declined'])->count();

        $reservations = $baseQuery
            ->orderBy('reservation_time', 'asc')
            ->paginate(10)
            ->appends($request->query());

        return view('admin.reservations', compact(
            'reservations',
            'selectedDate',
            'totalReservations',
            'pendingCount',
            'approvedCount',
            'cancelledCount'
        ));
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