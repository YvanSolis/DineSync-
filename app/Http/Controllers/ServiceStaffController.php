<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use App\Models\User;
use Illuminate\Http\Request;

class ServiceStaffController extends Controller
{
    public function dashboard()
    {
        $today = now()->toDateString();

        $orderStats = [
            'active' => Order::whereRaw("LOWER(TRIM(status)) NOT IN (?, ?)", ['cancelled', 'completed'])->count(),
            'preparing' => Order::whereRaw("LOWER(TRIM(status)) = ?", ['preparing'])->count(),
            'ready' => Order::whereRaw("LOWER(TRIM(status)) = ?", ['ready'])->count(),
            'served_today' => Order::whereRaw("LOWER(TRIM(status)) = ?", ['served'])
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
            ->whereRaw("LOWER(TRIM(status)) NOT IN (?, ?)", ['cancelled', 'completed'])
            ->orderByRaw("
                CASE
                    WHEN LOWER(TRIM(status)) IN ('pending', 'new', 'placed', 'confirmed') THEN 1
                    WHEN LOWER(TRIM(status)) = 'preparing' THEN 2
                    WHEN LOWER(TRIM(status)) = 'ready' THEN 3
                    WHEN LOWER(TRIM(status)) = 'served' THEN 4
                    ELSE 5
                END
            ")
            ->orderByDesc('id')
            ->take(5)
            ->get()
            ->map(function ($order) {
                $order->display_status = $this->normalizeOrderStatus($order);
                $order->status = $order->display_status;
                return $order;
            });

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
            ->whereRaw("LOWER(TRIM(status)) NOT IN (?, ?)", ['cancelled', 'completed'])
            ->orderByRaw("
                CASE
                    WHEN LOWER(TRIM(status)) IN ('pending', 'new', 'placed', 'confirmed') THEN 1
                    WHEN LOWER(TRIM(status)) = 'preparing' THEN 2
                    WHEN LOWER(TRIM(status)) = 'ready' THEN 3
                    WHEN LOWER(TRIM(status)) = 'served' THEN 4
                    ELSE 5
                END
            ")
            ->orderByDesc('id')
            ->paginate(10);

        $orderCollection = $orders->getCollection();

        $orderIds = $orderCollection->pluck('id')->filter()->values();

        $tablesByOrderId = RestaurantTable::whereIn('current_order_id', $orderIds)
            ->get()
            ->keyBy('current_order_id');

        $orders->setCollection(
            $orderCollection->map(function ($order) use ($tablesByOrderId) {
                $order->display_status = $this->normalizeOrderStatus($order);
                $order->status = $order->display_status;

                $tableNumber = $order->table_number ?? null;

                if (!$tableNumber) {
                    $tableNumber = optional($tablesByOrderId->get($order->id))->table_number;
                }

                $order->source_table_number = $tableNumber;

                return $order;
            })
        );

        $activeForStats = Order::whereRaw("LOWER(TRIM(status)) NOT IN (?, ?)", ['cancelled', 'completed'])
            ->get()
            ->map(function ($order) {
                return $this->normalizeOrderStatus($order);
            });

        $stats = [
            'pending' => $activeForStats->filter(fn ($status) => $status === 'pending')->count(),
            'preparing' => $activeForStats->filter(fn ($status) => $status === 'preparing')->count(),
            'ready' => $activeForStats->filter(fn ($status) => $status === 'ready')->count(),
            'served_today' => Order::whereRaw("LOWER(TRIM(status)) = ?", ['served'])
                ->whereDate('updated_at', now()->toDateString())
                ->count(),
        ];

        return view('service.active-orders', compact('orders', 'stats'));
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', 'in:pending,preparing,ready,served,cancelled'],
        ]);

        $order->status = strtolower(trim($request->status));
        $order->updated_at = now();
        $order->save();

        return back()->with('success', 'Order status updated successfully.');
    }

    public function tableMonitoring()
    {
        /*
        * Fix invalid/fake occupied tables.
        * A table should only be occupied if service staff assigned it
        * through walk-in or seated reservation.
        *
        * Tablet/mobile login should only show On/Off indicator,
        * not change the table status to occupied.
        */
        RestaurantTable::where('status', 'occupied')
            ->whereNull('current_guest_count')
            ->whereNull('current_reservation_id')
            ->whereNull('occupied_at')
            ->update([
                'status' => 'available',
                'current_order_id' => null,
                'notes' => null,
            ]);

        $tables = RestaurantTable::orderByRaw('CAST(table_number AS INTEGER) ASC')
            ->get();

        $tableStats = [
            'available' => RestaurantTable::where('status', 'available')->count(),
            'occupied' => RestaurantTable::where('status', 'occupied')->count(),
            'reserved' => RestaurantTable::where('status', 'reserved')->count(),
            'cleaning' => RestaurantTable::where('status', 'cleaning')->count(),
        ];

        $tabletAccounts = User::where('role', 'table_customer')
            ->orderByRaw('CAST(table_number AS INTEGER) ASC')
            ->get()
            ->map(function ($tablet) {
                $lastSeen = $tablet->last_seen_at ? \Carbon\Carbon::parse($tablet->last_seen_at) : null;
                $diffMinutes = $lastSeen ? $lastSeen->diffInMinutes(now()) : null;

                if (! $tablet->is_online) {
                    $tablet->display_status = 'offline';
                } elseif ($diffMinutes !== null && $diffMinutes > 2) {
                    $tablet->display_status = 'inactive';
                } else {
                    $tablet->display_status = 'online';
                }

                $tablet->last_seen_text = $lastSeen ? $lastSeen->diffForHumans() : 'Never';

                return $tablet;
            })
            ->keyBy('table_number');

        $orders = Order::with(['items.menuItem'])
            ->whereRaw("LOWER(TRIM(status)) IN (?, ?, ?, ?, ?, ?)", [
                'pending',
                'new',
                'placed',
                'confirmed',
                'preparing',
                'ready',
            ])
            ->orderByRaw("
                CASE
                    WHEN LOWER(TRIM(status)) IN ('pending', 'new', 'placed', 'confirmed') THEN 1
                    WHEN LOWER(TRIM(status)) = 'preparing' THEN 2
                    WHEN LOWER(TRIM(status)) = 'ready' THEN 3
                    ELSE 4
                END
            ")
            ->orderByDesc('id')
            ->get()
            ->map(function ($order) {
                $order->display_status = $this->normalizeOrderStatus($order);
                $order->status = $order->display_status;
                return $order;
            });

        $activeOrders = collect();

        foreach ($tables as $table) {
            $matchedOrder = null;

            if (! empty($table->current_order_id)) {
                $matchedOrder = $orders->firstWhere('id', $table->current_order_id);
            }

            if (! $matchedOrder) {
                $matchedOrder = $orders->first(function ($order) use ($table) {
                    return (string) ($order->table_number ?? '') === (string) $table->table_number;
                });
            }

            if ($matchedOrder) {
                $activeOrders[$table->table_number] = $matchedOrder;
            }
        }

        return view('service.table-monitoring', compact(
            'tables',
            'tableStats',
            'tabletAccounts',
            'activeOrders'
        ));
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

        $this->createTableSession($table, $request->guest_count, $request->notes ?: 'Walk-in customer');

        return back()->with('success', 'Walk-in customer assigned to table successfully.');
    }

    public function markTableCleaning(RestaurantTable $table)
    {
        if ($table->status !== 'occupied') {
            return back()->with('error', 'Only occupied tables can be marked for cleaning.');
        }

        $this->closeActiveTableSession($table);

        $table->update([
            'status' => 'cleaning',
            'current_guest_count' => null,
            'current_order_id' => null,
            'current_reservation_id' => null,
            'occupied_at' => null,
            'notes' => 'Needs cleaning',
        ]);

        return back()->with('success', 'Table marked for cleaning. Tablet session has been reset.');
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

            $this->createTableSession($table, $reservation->guest_count, 'Reservation customer');
        }

        if (in_array($status, ['completed', 'cancelled', 'declined'])) {
            if ($reservation->table_number) {
                $table = RestaurantTable::where('table_number', $reservation->table_number)
                    ->where('current_reservation_id', $reservation->id)
                    ->first();

                if ($table && $table->status === 'occupied') {
                    $this->closeActiveTableSession($table);

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

    public function readyOrderCount()
    {
        $readyCount = Order::whereRaw("LOWER(TRIM(status)) = ?", ['ready'])->count();

        $latestReadyOrder = Order::whereRaw("LOWER(TRIM(status)) = ?", ['ready'])
            ->latest('updated_at')
            ->first();

        return response()->json([
            'ready_count' => $readyCount,
            'latest_order_number' => $latestReadyOrder?->order_number,
            'latest_updated_at' => $latestReadyOrder?->updated_at,
        ]);
    }

    private function createTableSession(RestaurantTable $table, ?int $guestCount = null, ?string $notes = null): TableSession
    {
        $this->closeActiveTableSession($table);

        return TableSession::create([
            'restaurant_table_id' => $table->id,
            'session_code' => $this->generateTableSessionCode(),
            'guest_count' => $guestCount,
            'notes' => $notes,
            'started_at' => now(),
            'status' => 'active',
        ]);
    }

    private function closeActiveTableSession(RestaurantTable $table): void
    {
        TableSession::where('restaurant_table_id', $table->id)
            ->where('status', 'active')
            ->update([
                'status' => 'closed',
                'ended_at' => now(),
            ]);
    }

    private function generateTableSessionCode(): string
    {
        do {
            $sessionCode = 'TS-' . now()->format('YmdHis') . '-' . random_int(100, 999);
        } while (TableSession::where('session_code', $sessionCode)->exists());

        return $sessionCode;
    }

    private function normalizeOrderStatus(Order $order): string
    {
        $status = strtolower(trim($order->status ?? 'pending'));

        if (in_array($status, ['new', 'placed', 'confirmed'])) {
            return 'pending';
        }

        return $status;
    }
}