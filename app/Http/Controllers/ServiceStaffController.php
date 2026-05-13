<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;

class ServiceStaffController extends Controller
{
    public function dashboard()
    {
        $today = now()->toDateString();

        $orderStats = [
            'active' => Order::whereIn('status', ['pending', 'preparing', 'ready'])->count(),
            'preparing' => Order::where('status', 'preparing')->count(),
            'ready' => Order::where('status', 'ready')->count(),
            'served_today' => Order::where('status', 'served')
                ->whereDate('updated_at', $today)
                ->count(),
        ];

        $reservationStats = [
            'pending' => Reservation::where('status', 'pending')->count(),
            'approved_today' => Reservation::whereDate('reservation_date', $today)
                ->where('status', 'approved')
                ->count(),
            'arrived' => Reservation::where('status', 'arrived')->count(),
            'seated' => Reservation::where('status', 'seated')->count(),
        ];

        $recentOrders = Order::with(['items.menuItem', 'payment'])
            ->latest()
            ->take(5)
            ->get();

        $recentReservations = Reservation::latest()
            ->take(5)
            ->get();

        return view('service.dashboard', compact(
            'orderStats',
            'reservationStats',
            'recentOrders',
            'recentReservations'
        ));
    }

    public function activeOrders()
    {
        $orders = Order::with(['items.menuItem', 'payment'])
            ->whereIn('status', ['pending', 'preparing', 'ready', 'served'])
            ->latest()
            ->paginate(10);

        $stats = [
            'pending' => Order::where('status', 'pending')->count(),
            'preparing' => Order::where('status', 'preparing')->count(),
            'ready' => Order::where('status', 'ready')->count(),
            'served_today' => Order::where('status', 'served')
                ->whereDate('updated_at', now()->toDateString())
                ->count(),
        ];

        return view('service.active-orders', compact('orders', 'stats'));
    }

    public function markOrderServed(Order $order)
    {
        if ($order->status !== 'ready') {
            return back()->with('error', 'Only ready orders can be marked as served.');
        }

        $order->status = 'served';
        $order->save();

        return back()->with('success', 'Order marked as served successfully.');
    }

    public function tableMonitoring()
    {
        $tables = RestaurantTable::orderByRaw('CAST(table_number AS INTEGER) ASC')
            ->get();

        $tableStats = [
            'available' => RestaurantTable::where('status', 'available')->count(),
            'occupied' => RestaurantTable::where('status', 'occupied')->count(),
            'reserved' => RestaurantTable::where('status', 'reserved')->count(),
            'cleaning' => RestaurantTable::where('status', 'cleaning')->count(),
        ];

        return view('service.table-monitoring', compact('tables', 'tableStats'));
    }

    public function assignWalkIn(Request $request, RestaurantTable $table)
    {
        $request->validate([
            'guest_count' => ['required', 'integer', 'min:1', 'max:20'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        if ($table->status !== 'available') {
            return back()->with('error', 'Only available tables can be assigned to walk-in customers.');
        }

        if ($request->guest_count > $table->capacity) {
            return back()->with('error', 'Guest count exceeds the table capacity.');
        }

        $table->update([
            'status' => 'occupied',
            'current_guest_count' => $request->guest_count,
            'current_order_id' => null,
            'current_reservation_id' => null,
            'occupied_at' => now(),
            'notes' => $request->notes ?: 'Walk-in customer',
        ]);

        return back()->with('success', 'Walk-in customer assigned to table successfully.');
    }

    public function markTableCleaning(RestaurantTable $table)
    {
        if ($table->status !== 'occupied') {
            return back()->with('error', 'Only occupied tables can be marked for cleaning.');
        }

        $table->update([
            'status' => 'cleaning',
            'current_guest_count' => null,
            'current_order_id' => null,
            'current_reservation_id' => null,
            'occupied_at' => null,
            'notes' => 'Needs cleaning',
        ]);

        return back()->with('success', 'Table marked for cleaning.');
    }

    public function markTableAvailable(RestaurantTable $table)
    {
        if ($table->status !== 'cleaning') {
            return back()->with('error', 'Only cleaning tables can be marked as available.');
        }

        $table->update([
            'status' => 'available',
            'current_guest_count' => null,
            'current_order_id' => null,
            'current_reservation_id' => null,
            'occupied_at' => null,
            'notes' => null,
        ]);

        return back()->with('success', 'Table is now available.');
    }

    public function reservations()
    {
        $reservations = Reservation::orderByRaw("CASE 
                WHEN status = 'pending' THEN 1
                WHEN status = 'approved' THEN 2
                WHEN status = 'arrived' THEN 3
                WHEN status = 'seated' THEN 4
                WHEN status = 'completed' THEN 5
                WHEN status = 'declined' THEN 6
                WHEN status = 'cancelled' THEN 7
                ELSE 8
            END")
            ->orderBy('reservation_date')
            ->orderBy('reservation_time')
            ->paginate(10);

        $stats = [
            'pending' => Reservation::where('status', 'pending')->count(),
            'approved_today' => Reservation::whereDate('reservation_date', now()->toDateString())
                ->where('status', 'approved')
                ->count(),
            'arrived' => Reservation::where('status', 'arrived')->count(),
            'seated' => Reservation::where('status', 'seated')->count(),
        ];

        return view('service.reservations', compact('reservations', 'stats'));
    }

    public function updateReservationStatus(Request $request, Reservation $reservation)
    {
        $request->validate([
            'status' => ['required', 'in:pending,approved,declined,arrived,seated,completed,cancelled'],
            'table_number' => ['nullable', 'string', 'max:50'],
        ]);

        $status = $request->status;

        if ($status === 'approved' && $reservation->payment_status !== 'verified') {
            return back()->with('error', 'Please verify the reservation payment before accepting this reservation.');
        }

        if ($status === 'seated' && !$request->filled('table_number') && !$reservation->table_number) {
            return back()->with('error', 'Please assign a table number before seating the customer.');
        }

        $reservation->status = $status;

        if ($request->filled('table_number')) {
            $reservation->table_number = $request->table_number;
        }

        if ($status === 'arrived') {
            $reservation->arrived_at = now();
        }

        if ($status === 'seated') {
            $reservation->seated_at = now();

            if (!$reservation->arrived_at) {
                $reservation->arrived_at = now();
            }

            $table = RestaurantTable::where('table_number', $reservation->table_number)->first();

            if (!$table) {
                return back()->with('error', 'Table number does not exist in table monitoring.');
            }

            if (!in_array($table->status, ['available', 'reserved'])) {
                return back()->with('error', 'Selected table is not available.');
            }

            if ($reservation->guest_count > $table->capacity) {
                return back()->with('error', 'Guest count exceeds the selected table capacity.');
            }

            $table->update([
                'status' => 'occupied',
                'current_guest_count' => $reservation->guest_count,
                'current_order_id' => null,
                'current_reservation_id' => $reservation->id,
                'occupied_at' => now(),
                'notes' => 'Reservation customer',
            ]);
        }

        if (in_array($status, ['completed', 'cancelled', 'declined'])) {
            if ($reservation->table_number) {
                $table = RestaurantTable::where('table_number', $reservation->table_number)
                    ->where('current_reservation_id', $reservation->id)
                    ->first();

                if ($table && $table->status === 'occupied') {
                    $table->update([
                        'status' => 'cleaning',
                        'current_guest_count' => null,
                        'current_order_id' => null,
                        'current_reservation_id' => null,
                        'occupied_at' => null,
                        'notes' => 'Needs cleaning',
                    ]);
                }
            }
        }

        $reservation->save();

        return back()->with('success', 'Reservation status updated successfully.');
    }

    public function verifyReservationPayment(Reservation $reservation)
    {
        $reservation->payment_status = 'verified';
        $reservation->save();

        return back()->with('success', 'Reservation payment verified successfully.');
    }

    public function rejectReservationPayment(Reservation $reservation)
    {
        $reservation->payment_status = 'rejected';
        $reservation->save();

        return back()->with('success', 'Reservation payment rejected.');
    }

    public function customerAssistance()
    {
        return view('service.customer-assistance');
    }
}