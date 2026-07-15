<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use App\Models\User;
use App\Services\XenditService;
use App\Services\InventoryDeductionService;
use App\Services\GovernmentDiscountService;
use App\Services\AuditService;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ServiceStaffController extends Controller
{
    public function dashboard()
    {
        $today = now()->toDateString();

        $activeStatuses = [
            'pending',
            'new',
            'placed',
            'confirmed',
            'preparing',
            'ready',
        ];

        $orderStats = [
            'active' => Order::whereRaw("LOWER(TRIM(status)) IN (?, ?, ?, ?, ?, ?)", $activeStatuses)->count(),
            'preparing' => Order::whereRaw("LOWER(TRIM(status)) = ?", ['preparing'])->count(),
            'ready' => Order::whereRaw("LOWER(TRIM(status)) = ?", ['ready'])->count(),
        ];

        $reservationStats = [
            'pending' => Reservation::where('status', 'pending')->count(),
            'approved_today' => Reservation::whereDate('reservation_date', $today)
                ->where('status', 'approved')
                ->count(),
            'arrived' => Reservation::where('status', 'arrived')->count(),
            'seated' => Reservation::where('status', 'seated')->count(),
        ];

        $recentOrders = Order::with([
            'items.menuItem.ingredients',
            'items.refillRecords',
            'payment',
        ])
            ->whereRaw("LOWER(TRIM(status)) IN (?, ?, ?, ?, ?, ?)", $activeStatuses)
            ->orderByRaw("
                CASE
                    WHEN LOWER(TRIM(status)) IN ('pending', 'new', 'placed', 'confirmed') THEN 1
                    WHEN LOWER(TRIM(status)) = 'preparing' THEN 2
                    WHEN LOWER(TRIM(status)) = 'ready' THEN 3
                    ELSE 4
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
        $activeStatuses = [
            'pending',
            'new',
            'placed',
            'confirmed',
            'preparing',
            'ready',
        ];

        $orders = Order::with([
            'items.menuItem.ingredients',
            'items.refillRecords',
            'payment',
        ])
            ->whereRaw("LOWER(TRIM(status)) IN (?, ?, ?, ?, ?, ?)", $activeStatuses)
            ->orderByRaw("
                CASE
                    WHEN LOWER(TRIM(status)) IN ('pending', 'new', 'placed', 'confirmed') THEN 1
                    WHEN LOWER(TRIM(status)) = 'preparing' THEN 2
                    WHEN LOWER(TRIM(status)) = 'ready' THEN 3
                    ELSE 4
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

        $activeForStats = Order::whereRaw("LOWER(TRIM(status)) IN (?, ?, ?, ?, ?, ?)", $activeStatuses)
            ->get()
            ->map(function ($order) {
                return $this->normalizeOrderStatus($order);
            });

        $stats = [
            'pending' => $activeForStats->filter(fn ($status) => $status === 'pending')->count(),
            'preparing' => $activeForStats->filter(fn ($status) => $status === 'preparing')->count(),
            'ready' => $activeForStats->filter(fn ($status) => $status === 'ready')->count(),
        ];

        return view('service.active-orders', compact('orders', 'stats'));
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', 'in:pending,preparing,ready,served,cancelled'],
        ]);

        $oldStatus = strtolower(trim($order->status ?? 'pending'));
        $newStatus = strtolower(trim($request->status));

        $order->status = $newStatus;
        $order->updated_at = now();
        $order->save();

        AuditService::record(
            module: 'Orders',
            action: 'status_changed',
            description: "Service staff changed order {$order->order_number} from {$oldStatus} to {$newStatus}.",
            auditable: $order,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $newStatus],
            request: $request
        );

        return back()->with('success', 'Order status updated successfully.');
    }

    public function markOrderPaid(Request $request, Order $order, InventoryDeductionService $inventoryDeductionService)
    {
        $orderStatus = strtolower(trim($order->status ?? 'pending'));

        if (in_array(strtolower(trim($order->payment_status ?? 'pending')), ['paid', 'verified'], true)) {
            return back()->with('error', 'This order is already paid.');
        }

        $newStatus = $orderStatus === 'awaiting_payment'
            ? 'pending'
            : $orderStatus;

        try {
            $oldValues = $order->only([
                'payment_method',
                'payment_status',
                'paid_at',
                'status',
            ]);

            $inventoryDeductionService->deductForOrder($order);

            $order->update([
                'payment_method' => 'Cash',
                'payment_status' => 'paid',
                'paid_at' => now(),
                'status' => $newStatus,
                'updated_at' => now(),
            ]);

            $order->refresh();

            AuditService::record(
                module: 'Payments',
                action: 'payment_processed',
                description: "Service staff confirmed cash payment for order {$order->order_number}.",
                auditable: $order,
                oldValues: $oldValues,
                newValues: $order->only([
                    'payment_method',
                    'payment_status',
                    'paid_at',
                    'status',
                ]),
                request: $request
            );

            return back()->with('success', 'Payment confirmed. Inventory was deducted and the order is now sent to KDS.');
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first();

            return back()->with('error', $message ?: 'Not enough stock to process this order.');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', $e->getMessage() ?: 'Unable to process payment and inventory deduction.');
        }
    }

    public function tableMonitoring()
    {
        RestaurantTable::where('status', 'occupied')
            ->whereNull('current_guest_count')
            ->whereNull('current_reservation_id')
            ->whereNull('occupied_at')
            ->update([
                'status' => 'available',
                'current_order_id' => null,
                'notes' => null,
                'updated_at' => now(),
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

                if (!$tablet->is_online) {
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

        $activeStatuses = [
            'pending',
            'new',
            'placed',
            'confirmed',
            'preparing',
            'ready',
        ];

        $orders = Order::with(['items.menuItem'])
            ->whereRaw("LOWER(TRIM(status)) IN (?, ?, ?, ?, ?, ?)", $activeStatuses)
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
            })
            ->keyBy('id');

        $activeOrders = collect();

        foreach ($tables as $table) {
            if ($table->status !== 'occupied') {
                continue;
            }

            if (empty($table->current_order_id)) {
                continue;
            }

            $matchedOrder = $orders->get($table->current_order_id);

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

        $oldValues = $table->only([
            'status',
            'current_guest_count',
            'current_order_id',
            'current_reservation_id',
            'occupied_at',
            'notes',
        ]);

        $table->update([
            'status' => 'occupied',
            'current_guest_count' => $request->guest_count,
            'current_order_id' => null,
            'current_reservation_id' => null,
            'occupied_at' => now(),
            'notes' => $request->notes ?: 'Walk-in customer',
        ]);

        $this->createTableSession($table, $request->guest_count, $request->notes ?: 'Walk-in customer');
        $table->refresh();

        AuditService::record(
            module: 'Tables',
            action: 'walk_in_assigned',
            description: "Assigned {$request->guest_count} walk-in guest(s) to Table {$table->table_number}.",
            auditable: $table,
            oldValues: $oldValues,
            newValues: $table->only([
                'status',
                'current_guest_count',
                'current_order_id',
                'current_reservation_id',
                'occupied_at',
                'notes',
            ]),
            request: $request
        );

        return back()->with('success', 'Walk-in customer assigned to table successfully.');
    }

    public function markTableCleaning(Request $request, RestaurantTable $table)
    {
        if ($table->status !== 'occupied') {
            return back()->with('error', 'Only occupied tables can be marked for cleaning.');
        }

        $oldValues = $table->only([
            'status',
            'current_guest_count',
            'current_order_id',
            'current_reservation_id',
            'occupied_at',
            'notes',
        ]);

        $this->closeActiveTableSession($table);

        $table->update([
            'status' => 'cleaning',
            'current_guest_count' => null,
            'current_order_id' => null,
            'current_reservation_id' => null,
            'occupied_at' => null,
            'notes' => 'Needs cleaning',
            'updated_at' => now(),
        ]);

        $table->refresh();

        AuditService::record(
            module: 'Tables',
            action: 'marked_cleaning',
            description: "Marked Table {$table->table_number} for cleaning.",
            auditable: $table,
            oldValues: $oldValues,
            newValues: $table->only([
                'status',
                'current_guest_count',
                'current_order_id',
                'current_reservation_id',
                'occupied_at',
                'notes',
            ]),
            request: $request
        );

        return back()->with('success', 'Table marked for cleaning. Tablet session has been reset.');
    }

    public function markTableAvailable(Request $request, RestaurantTable $table)
    {
        if ($table->status !== 'cleaning') {
            return back()->with('error', 'Only cleaning tables can be marked as available.');
        }

        $oldValues = $table->only([
            'status',
            'current_guest_count',
            'current_order_id',
            'current_reservation_id',
            'occupied_at',
            'notes',
        ]);

        $table->update([
            'status' => 'available',
            'current_guest_count' => null,
            'current_order_id' => null,
            'current_reservation_id' => null,
            'occupied_at' => null,
            'notes' => null,
            'updated_at' => now(),
        ]);

        $table->refresh();

        AuditService::record(
            module: 'Tables',
            action: 'marked_available',
            description: "Marked Table {$table->table_number} as available.",
            auditable: $table,
            oldValues: $oldValues,
            newValues: $table->only([
                'status',
                'current_guest_count',
                'current_order_id',
                'current_reservation_id',
                'occupied_at',
                'notes',
            ]),
            request: $request
        );

        return back()->with('success', 'Table is now available.');
    }

    public function reservations(Request $request)
    {
        $selectedDate = $request->query('date', now('Asia/Manila')->toDateString());

        try {
            $selectedDate = \Carbon\Carbon::parse($selectedDate, 'Asia/Manila')->toDateString();
        } catch (\Throwable $e) {
            $selectedDate = now('Asia/Manila')->toDateString();
        }

        $pendingForSync = Reservation::whereDate('reservation_date', $selectedDate)
            ->whereNotIn('payment_status', ['paid', 'verified', 'settled', 'completed', 'rejected'])
            ->whereNotNull('xendit_invoice_id')
            ->get();

        $this->syncPendingReservationPayments($pendingForSync);

        $reservations = Reservation::whereDate('reservation_date', $selectedDate)
            ->orderByRaw("
                CASE
                    WHEN status = 'pending' THEN 1
                    WHEN status = 'approved' THEN 2
                    WHEN status = 'arrived' THEN 3
                    WHEN status = 'seated' THEN 4
                    WHEN status = 'completed' THEN 5
                    WHEN status = 'declined' THEN 6
                    WHEN status = 'cancelled' THEN 7
                    ELSE 8
                END
            ")
            ->orderBy('reservation_time')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'selected_date' => $selectedDate,
            'total' => Reservation::whereDate('reservation_date', $selectedDate)->count(),
            'pending' => Reservation::whereDate('reservation_date', $selectedDate)
                ->where('status', 'pending')
                ->count(),
            'approved_today' => Reservation::whereDate('reservation_date', $selectedDate)
                ->where('status', 'approved')
                ->count(),
            'arrived' => Reservation::whereDate('reservation_date', $selectedDate)
                ->where('status', 'arrived')
                ->count(),
            'seated' => Reservation::whereDate('reservation_date', $selectedDate)
                ->where('status', 'seated')
                ->count(),
        ];

        return view('service.reservations', compact('reservations', 'stats', 'selectedDate'));
    }

    public function updateReservationStatus(Request $request, Reservation $reservation)
    {
        $request->validate([
            'status' => ['required', 'in:pending,approved,declined,arrived,seated,completed,cancelled'],
            'table_number' => ['nullable', 'string', 'max:50'],
        ]);

        $oldValues = $reservation->only([
            'status',
            'table_number',
            'arrived_at',
            'seated_at',
            'payment_status',
        ]);

        $status = strtolower(trim($request->status));
        $paymentStatus = strtolower(trim($reservation->payment_status ?? 'pending'));

        $paymentVerifiedStatuses = [
            'paid',
            'verified',
            'settled',
            'completed',
        ];

        if ($status === 'approved' && !in_array($paymentStatus, $paymentVerifiedStatuses, true)) {
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

            if (!in_array($table->status, ['available', 'reserved'], true)) {
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

        if (in_array($status, ['completed', 'cancelled', 'declined'], true)) {
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
        $reservation->refresh();

        AuditService::record(
            module: 'Reservations',
            action: 'status_changed',
            description: "Service staff changed reservation #{$reservation->id} to {$reservation->status}.",
            auditable: $reservation,
            oldValues: $oldValues,
            newValues: $reservation->only([
                'status',
                'table_number',
                'arrived_at',
                'seated_at',
                'payment_status',
            ]),
            request: $request
        );

        return back()->with('success', 'Reservation status updated successfully.');
    }

    public function verifyReservationPayment(Request $request, Reservation $reservation)
    {
        $oldValues = $reservation->only([
            'payment_status',
            'payment_reference',
            'paid_at',
        ]);

        $reservation->payment_status = 'verified';

        if (empty($reservation->payment_reference)) {
            $reservation->payment_reference = $reservation->xendit_invoice_id ?? $reservation->xendit_external_id ?? null;
        }

        if (empty($reservation->paid_at)) {
            $reservation->paid_at = now();
        }

        $reservation->save();
        $reservation->refresh();

        AuditService::record(
            module: 'Reservations',
            action: 'payment_verified',
            description: "Service staff verified payment for reservation #{$reservation->id}.",
            auditable: $reservation,
            oldValues: $oldValues,
            newValues: $reservation->only([
                'payment_status',
                'payment_reference',
                'paid_at',
            ]),
            request: $request
        );

        return back()->with('success', 'Reservation payment verified successfully.');
    }

    public function rejectReservationPayment(Request $request, Reservation $reservation)
    {
        $oldValues = $reservation->only(['payment_status']);

        $reservation->payment_status = 'rejected';
        $reservation->save();
        $reservation->refresh();

        AuditService::record(
            module: 'Reservations',
            action: 'payment_rejected',
            description: "Service staff rejected payment for reservation #{$reservation->id}.",
            auditable: $reservation,
            oldValues: $oldValues,
            newValues: $reservation->only(['payment_status']),
            request: $request
        );

        return back()->with('success', 'Reservation payment rejected.');
    }

    public function payments(Request $request)
    {
        $timezone = 'Asia/Manila';

        $mode = $request->query('mode');

        if (!$mode) {
            $mode = $request->has('date') ? 'daily' : 'today';
        }

        if ($mode === 'today') {
            $selectedDate = now($timezone)->toDateString();
        } else {
            $selectedDate = $request->query('date', now($timezone)->toDateString());

            try {
                $selectedDate = \Carbon\Carbon::parse($selectedDate, $timezone)->toDateString();
            } catch (\Throwable $e) {
                $selectedDate = now($timezone)->toDateString();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Local Development Xendit Sync
        |--------------------------------------------------------------------------
        */
        $pendingDigitalOrders = Order::query()
            ->where(function ($query) {
                $query->whereNull('payment_status')
                    ->orWhereNotIn(DB::raw('LOWER(TRIM(payment_status))'), [
                        'paid',
                        'verified',
                        'completed',
                        'success',
                        'successful',
                        'settled',
                    ]);
            })
            ->where(function ($query) {
                $hasCondition = false;

                if (Schema::hasColumn('orders', 'xendit_invoice_id')) {
                    $query->whereNotNull('xendit_invoice_id');
                    $hasCondition = true;
                }

                if (Schema::hasColumn('orders', 'xendit_external_id')) {
                    if ($hasCondition) {
                        $query->orWhereNotNull('xendit_external_id');
                    } else {
                        $query->whereNotNull('xendit_external_id');
                        $hasCondition = true;
                    }
                }

                if (Schema::hasColumn('orders', 'xendit_invoice_url')) {
                    if ($hasCondition) {
                        $query->orWhereNotNull('xendit_invoice_url');
                    } else {
                        $query->whereNotNull('xendit_invoice_url');
                    }
                }
            })
            ->get();

        $this->syncPendingOrderPayments($pendingDigitalOrders);

        /*
        |--------------------------------------------------------------------------
        | Service Staff Payment Records
        |--------------------------------------------------------------------------
        | Same date logic as admin payment management:
        | - Pending / Pay Later / Pay at Counter follows order created date.
        | - Paid / Completed follows paid_at date when available.
        | - Date range is converted from Manila day to UTC range to avoid
        |   July 1 records appearing under June 30 on hosting.
        */
        $query = Order::with(['items.menuItem'])
            ->whereNotNull('payment_method')
            ->whereRaw("TRIM(payment_method) != ''");

        if ($mode !== 'all') {
            $this->applyServicePaymentDateFilter($query, $selectedDate);
        }

        $orders = $query
            ->orderByRaw("
                CASE
                    WHEN LOWER(TRIM(payment_status)) IN ('pending', 'processing', 'unpaid') THEN 1
                    WHEN LOWER(TRIM(status)) = 'awaiting_payment' THEN 2
                    WHEN LOWER(TRIM(payment_status)) IN ('paid', 'verified', 'completed', 'success', 'successful', 'settled') THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('created_at', 'asc')
            ->paginate(12)
            ->withQueryString();

        $orderCollection = $orders->getCollection();

        $orderIds = $orderCollection->pluck('id')->filter()->values();

        $tablesByOrderId = RestaurantTable::whereIn('current_order_id', $orderIds)
            ->get()
            ->keyBy('current_order_id');

        $orders->setCollection(
            $orderCollection->map(function ($order) use ($tablesByOrderId) {
                $tableNumber = $order->table_number ?? null;

                if (!$tableNumber) {
                    $tableNumber = optional($tablesByOrderId->get($order->id))->table_number;
                }

                $order->source_table_number = $tableNumber;

                return $order;
            })
        );

        $baseStatsQuery = Order::query()
            ->whereNotNull('payment_method')
            ->whereRaw("TRIM(payment_method) != ''");

        if ($mode !== 'all') {
            $this->applyServicePaymentDateFilter($baseStatsQuery, $selectedDate);
        }

        $allPaymentOrders = $baseStatsQuery->get();

        $completedStatuses = [
            'paid',
            'verified',
            'completed',
            'success',
            'successful',
            'settled',
        ];

        $stats = [
            'awaiting_counter' => $allPaymentOrders->filter(function ($order) use ($completedStatuses) {
                $method = strtolower(str_replace(['_', '-'], ' ', trim($order->payment_method ?? '')));
                $paymentStatus = strtolower(trim($order->payment_status ?? 'pending'));

                return (
                    str_contains($method, 'counter') ||
                    str_contains($method, 'cash')
                ) && !in_array($paymentStatus, $completedStatuses, true);
            })->count(),

            'pay_later_unpaid' => $allPaymentOrders->filter(function ($order) use ($completedStatuses) {
                $method = strtolower(str_replace(['_', '-'], ' ', trim($order->payment_method ?? '')));
                $paymentStatus = strtolower(trim($order->payment_status ?? 'pending'));

                return str_contains($method, 'later')
                    && !in_array($paymentStatus, $completedStatuses, true);
            })->count(),

            'digital_pending' => $allPaymentOrders->filter(function ($order) use ($completedStatuses) {
                $method = strtolower(str_replace(['_', '-'], ' ', trim($order->payment_method ?? '')));
                $paymentStatus = strtolower(trim($order->payment_status ?? 'pending'));

                return (
                    str_contains($method, 'digital') ||
                    str_contains($method, 'qr') ||
                    str_contains($method, 'xendit')
                ) && !in_array($paymentStatus, $completedStatuses, true);
            })->count(),

            'paid' => $allPaymentOrders->filter(function ($order) use ($completedStatuses) {
                $paymentStatus = strtolower(trim($order->payment_status ?? 'pending'));

                return in_array($paymentStatus, $completedStatuses, true);
            })->count(),
        ];

        $activeTables = RestaurantTable::where('status', 'occupied')
            ->orderByRaw('CAST(table_number AS INTEGER) ASC')
            ->get();

        return view('service.payments', compact(
            'orders',
            'stats',
            'activeTables',
            'mode',
            'selectedDate'
        ));
    }

    private function getServiceManilaDayUtcRange(string $selectedDate): array
    {
        $start = \Carbon\Carbon::parse($selectedDate, 'Asia/Manila')
            ->startOfDay()
            ->timezone('UTC');

        $end = \Carbon\Carbon::parse($selectedDate, 'Asia/Manila')
            ->endOfDay()
            ->timezone('UTC');

        return [$start, $end];
    }

    private function applyServicePaymentDateFilter($query, string $selectedDate): void
    {
        [$start, $end] = $this->getServiceManilaDayUtcRange($selectedDate);

        $completedStatuses = [
            'paid',
            'verified',
            'completed',
            'success',
            'successful',
            'settled',
        ];

        $pendingStatuses = [
            'pending',
            'processing',
            'unpaid',
        ];

        $query->where(function ($query) use ($start, $end, $completedStatuses, $pendingStatuses) {
            $query
                ->where(function ($pendingQuery) use ($start, $end, $pendingStatuses) {
                    $pendingQuery
                        ->where(function ($statusQuery) use ($pendingStatuses) {
                            $statusQuery->whereNull('payment_status')
                                ->orWhereIn(DB::raw('LOWER(TRIM(payment_status))'), $pendingStatuses);
                        })
                        ->whereBetween('created_at', [$start, $end]);
                })
                ->orWhere(function ($completedQuery) use ($start, $end, $completedStatuses) {
                    $completedQuery
                        ->whereIn(DB::raw('LOWER(TRIM(payment_status))'), $completedStatuses)
                        ->where(function ($dateQuery) use ($start, $end) {
                            $dateQuery->whereBetween('paid_at', [$start, $end])
                                ->orWhere(function ($fallbackQuery) use ($start, $end) {
                                    $fallbackQuery->whereNull('paid_at')
                                        ->whereBetween('created_at', [$start, $end]);
                                });
                        });
                });
        });
    }

    public function processOrderPayment(
        Request $request,
        Order $order,
        GovernmentDiscountService $discountService,
        InventoryDeductionService $inventoryDeductionService
    ) {
        $validated = $request->validate([
            'payment_method' => [
                'required',
                'in:cash',
            ],
            'discount_type' => [
                'required',
                'in:none,senior,pwd',
            ],
            'total_diners' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'qualified_diners' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'discount_holder_name' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'discount_id_number' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'discount_id_verified' => [
                'nullable',
            ],
        ]);

        $paymentStatus = strtolower(
            trim($order->payment_status ?? 'pending')
        );

        if (in_array($paymentStatus, [
            'paid',
            'verified',
            'completed',
            'success',
            'successful',
            'settled',
        ], true)) {
            return back()->with(
                'error',
                'This order is already paid.'
            );
        }

        $discountType = strtolower(
            trim($validated['discount_type'])
        );

        $totalDiners = (int) (
            $validated['total_diners'] ?? 0
        );

        $qualifiedDiners = (int) (
            $validated['qualified_diners'] ?? 0
        );

        $discountHolderName = trim(
            (string) (
                $validated['discount_holder_name'] ?? ''
            )
        );

        $discountIdNumber = trim(
            (string) (
                $validated['discount_id_number'] ?? ''
            )
        );

        $discountIdVerified = $request->boolean(
            'discount_id_verified'
        );

        if ($discountType !== 'none') {
            if ($totalDiners < 1) {
                throw ValidationException::withMessages([
                    'total_diners' =>
                        'The total number of diners must be at least 1.',
                ]);
            }

            if ($qualifiedDiners < 1) {
                throw ValidationException::withMessages([
                    'qualified_diners' =>
                        'At least one qualified diner is required.',
                ]);
            }

            if ($qualifiedDiners > $totalDiners) {
                throw ValidationException::withMessages([
                    'qualified_diners' =>
                        'Qualified diners cannot exceed total diners.',
                ]);
            }

            if ($discountHolderName === '') {
                throw ValidationException::withMessages([
                    'discount_holder_name' =>
                        'Enter the name of every qualified ID holder.',
                ]);
            }

            if ($discountIdNumber === '') {
                throw ValidationException::withMessages([
                    'discount_id_number' =>
                        'Enter the ID number of every qualified diner.',
                ]);
            }

            if (!$discountIdVerified) {
                throw ValidationException::withMessages([
                    'discount_id_verified' =>
                        'The valid Senior Citizen or PWD ID must be verified.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Multiple qualified diners
            |--------------------------------------------------------------------------
            |
            | For more than one qualified diner, the cashier must enter one name
            | and one ID number per person, separated by commas or new lines.
            |
            */
            $holderNames = collect(
                preg_split(
                    '/[\r\n,]+/',
                    $discountHolderName
                )
            )
                ->map(fn ($value) => trim($value))
                ->filter()
                ->values();

            $idNumbers = collect(
                preg_split(
                    '/[\r\n,]+/',
                    $discountIdNumber
                )
            )
                ->map(fn ($value) => trim($value))
                ->filter()
                ->values();

            if ($holderNames->count() !== $qualifiedDiners) {
                throw ValidationException::withMessages([
                    'discount_holder_name' =>
                        "Enter exactly {$qualifiedDiners} qualified holder name(s), separated by commas.",
                ]);
            }

            if ($idNumbers->count() !== $qualifiedDiners) {
                throw ValidationException::withMessages([
                    'discount_id_number' =>
                        "Enter exactly {$qualifiedDiners} verified ID number(s), separated by commas.",
                ]);
            }

            if (
                $holderNames->map(fn ($value) => strtolower($value))
                    ->duplicates()
                    ->isNotEmpty()
            ) {
                throw ValidationException::withMessages([
                    'discount_holder_name' =>
                        'Duplicate qualified holder names are not allowed.',
                ]);
            }

            if (
                $idNumbers->map(fn ($value) => strtolower($value))
                    ->duplicates()
                    ->isNotEmpty()
            ) {
                throw ValidationException::withMessages([
                    'discount_id_number' =>
                        'Duplicate Senior Citizen or PWD ID numbers are not allowed.',
                ]);
            }

            $discountHolderName = $holderNames->implode(', ');
            $discountIdNumber = $idNumbers->implode(', ');
        } else {
            $totalDiners = 0;
            $qualifiedDiners = 0;
            $discountHolderName = '';
            $discountIdNumber = '';
            $discountIdVerified = false;
        }

        $calculation = $discountService->calculate(
            originalAmount: (float) $order->total_amount,
            discountType: $discountType,
            qualifiedDiners: $qualifiedDiners,
            totalDiners: $totalDiners
        );

        $orderStatus = strtolower(
            trim($order->status ?? 'pending')
        );

        $newStatus = $orderStatus === 'awaiting_payment'
            ? 'pending'
            : $orderStatus;

        try {
            DB::transaction(function () use (
                $request,
                $order,
                $inventoryDeductionService,
                $discountType,
                $totalDiners,
                $qualifiedDiners,
                $discountHolderName,
                $discountIdNumber,
                $discountIdVerified,
                $calculation,
                $newStatus
            ) {
                $lockedOrder = Order::query()
                    ->whereKey($order->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedPaymentStatus = strtolower(
                    trim(
                        $lockedOrder->payment_status
                        ?? 'pending'
                    )
                );

                if (in_array($lockedPaymentStatus, [
                    'paid',
                    'verified',
                    'completed',
                    'success',
                    'successful',
                    'settled',
                ], true)) {
                    throw ValidationException::withMessages([
                        'payment' =>
                            'This order has already been paid.',
                    ]);
                }

                $oldValues = $lockedOrder->only([
                    'payment_method',
                    'payment_status',
                    'paid_at',
                    'status',
                    'discount_type',
                    'qualified_diners',
                    'total_diners',
                    'discount_holder_name',
                    'discount_id_number',
                    'vat_exempt_amount',
                    'discount_amount',
                    'final_amount',
                    'discount_verified_by',
                    'discount_verified_at',
                ]);

                $inventoryDeductionService
                    ->deductForOrder($lockedOrder);

                $updateData = [
                    'payment_method' => 'Cash',
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'status' => $newStatus,
                    'discount_type' => $discountType,
                    'qualified_diners' => $qualifiedDiners,
                    'total_diners' => $totalDiners,
                    'discount_holder_name' =>
                        $discountType === 'none'
                            ? null
                            : $discountHolderName,
                    'discount_id_number' =>
                        $discountType === 'none'
                            ? null
                            : $discountIdNumber,
                    'vat_exempt_amount' =>
                        $calculation['vat_exempt_amount'],
                    'discount_amount' =>
                        $calculation['discount_amount'],
                    'final_amount' =>
                        $calculation['final_amount'],
                    'discount_verified_by' =>
                        $discountType === 'none'
                            ? null
                            : $request->user()?->id,
                    'discount_verified_at' =>
                        $discountType === 'none'
                            ? null
                            : now(),
                    'updated_at' => now(),
                ];

                $lockedOrder->update($updateData);
                $lockedOrder->refresh();

                $discountLabel = match ($discountType) {
                    'senior' => 'Senior Citizen',
                    'pwd' => 'PWD',
                    default => 'No',
                };

                $description = $discountType === 'none'
                    ? "Service staff processed cash payment for order {$lockedOrder->order_number} without a government discount."
                    : sprintf(
                        'Service staff processed cash payment for order %s with a %s discount for %d of %d diner(s). Original: ₱%s. Final: ₱%s.',
                        $lockedOrder->order_number,
                        $discountLabel,
                        $qualifiedDiners,
                        $totalDiners,
                        number_format(
                            (float) $lockedOrder->total_amount,
                            2
                        ),
                        number_format(
                            (float) $calculation['final_amount'],
                            2
                        )
                    );

                AuditService::record(
                    module: 'Payments',
                    action: $discountType === 'none'
                        ? 'payment_processed'
                        : 'government_discount_applied',
                    description: $description,
                    auditable: $lockedOrder,
                    oldValues: $oldValues,
                    newValues: $lockedOrder->only([
                        'payment_method',
                        'payment_status',
                        'paid_at',
                        'status',
                        'discount_type',
                        'qualified_diners',
                        'total_diners',
                        'discount_holder_name',
                        'discount_id_number',
                        'vat_exempt_amount',
                        'discount_amount',
                        'final_amount',
                        'discount_verified_by',
                        'discount_verified_at',
                    ]),
                    request: $request
                );
            });

            $successMessage = $discountType === 'none'
                ? 'Cash payment confirmed. The order is now sent to KDS.'
                : sprintf(
                    '%s discount applied. Final payable amount: ₱%s. The order is now sent to KDS.',
                    $discountType === 'senior'
                        ? 'Senior Citizen'
                        : 'PWD',
                    number_format(
                        (float) $calculation['final_amount'],
                        2
                    )
                );

            return back()->with(
                'success',
                $successMessage
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                $exception->getMessage()
                    ?: 'Unable to process the payment and discount.'
            );
        }
    }

    private function syncPendingOrderPayments($orders): void
    {
        $secretKey = config('services.xendit.secret_key') ?: env('XENDIT_SECRET_KEY');

        if (empty($secretKey)) {
            Log::warning('Order Xendit sync skipped because XENDIT_SECRET_KEY is missing.');
            return;
        }

        foreach ($orders as $order) {
            $paymentStatus = strtolower(trim($order->payment_status ?? 'pending'));

            if (in_array($paymentStatus, ['paid', 'verified'], true)) {
                continue;
            }

            $invoice = null;
            $invoiceId = $order->xendit_invoice_id ?? null;
            $externalId = $order->xendit_external_id ?? null;

            try {
                $httpRequest = Http::withBasicAuth($secretKey, '')
                    ->acceptJson()
                    ->timeout(20);

                if (app()->environment(['local', 'development'])) {
                    $httpRequest = $httpRequest->withoutVerifying();
                }

                if (!empty($invoiceId)) {
                    $response = $httpRequest->get(
                        'https://api.xendit.co/v2/invoices/' . $invoiceId
                    );

                    if ($response->successful()) {
                        $invoice = $response->json();
                    } else {
                        Log::warning('Order Xendit invoice sync by invoice ID failed', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'invoice_id_used' => $invoiceId,
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ]);
                    }
                }

                if (!$invoice && !empty($externalId)) {
                    $response = $httpRequest->get(
                        'https://api.xendit.co/v2/invoices',
                        [
                            'external_id' => $externalId,
                        ]
                    );

                    if ($response->successful()) {
                        $result = $response->json();

                        if (isset($result['data']) && is_array($result['data']) && count($result['data']) > 0) {
                            $invoice = $result['data'][0];
                        } elseif (isset($result[0]) && is_array($result[0])) {
                            $invoice = $result[0];
                        } elseif (isset($result['id'])) {
                            $invoice = $result;
                        }
                    } else {
                        Log::warning('Order Xendit invoice sync by external ID failed', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'external_id_used' => $externalId,
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ]);
                    }
                }

                if (!$invoice) {
                    Log::warning('Order Xendit sync skipped because invoice was not found', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'xendit_invoice_id' => $order->xendit_invoice_id ?? null,
                        'xendit_external_id' => $order->xendit_external_id ?? null,
                        'xendit_invoice_url' => $order->xendit_invoice_url ?? null,
                    ]);

                    continue;
                }

                $invoiceStatus = strtoupper((string) ($invoice['status'] ?? ''));

                Log::info('Order Xendit invoice sync checked invoice', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'invoice_id' => $invoice['id'] ?? $invoiceId,
                    'external_id' => $invoice['external_id'] ?? $externalId,
                    'invoice_status' => $invoiceStatus,
                ]);

                if (in_array($invoiceStatus, ['PAID', 'SETTLED'], true)) {
                    $orderStatus = strtolower(trim($order->status ?? 'pending'));

                    $newStatus = $orderStatus === 'awaiting_payment'
                        ? 'pending'
                        : $orderStatus;

                    $updateData = [
                        'payment_status' => 'paid',
                        'status' => $newStatus,
                        'paid_at' => $order->paid_at ?? now(),
                        'updated_at' => now(),
                    ];

                    if (Schema::hasColumn('orders', 'xendit_invoice_id')) {
                        $updateData['xendit_invoice_id'] = $invoice['id'] ?? $order->xendit_invoice_id;
                    }

                    if (Schema::hasColumn('orders', 'xendit_external_id')) {
                        $updateData['xendit_external_id'] = $invoice['external_id'] ?? $order->xendit_external_id;
                    }

                    if (Schema::hasColumn('orders', 'xendit_invoice_url')) {
                        $updateData['xendit_invoice_url'] = $invoice['invoice_url'] ?? $order->xendit_invoice_url;
                    }

                    if (Schema::hasColumn('orders', 'payment_reference')) {
                        $updateData['payment_reference'] = $invoice['id']
                            ?? $invoice['external_id']
                            ?? $order->payment_reference;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Inventory Deduction for Digital / QR PH Payments
                    |--------------------------------------------------------------------------
                    | When Xendit confirms the invoice as PAID/SETTLED, deduct the linked
                    | ingredients before marking the order as paid. The deduction service is
                    | already protected from double deductions through inventory_deducted_at
                    | or existing ingredient usage records.
                    */
                    try {
                        app(InventoryDeductionService::class)->deductForOrder($order);
                    } catch (ValidationException $e) {
                        $message = collect($e->errors())->flatten()->first();

                        Log::error('Inventory deduction failed after Xendit paid', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'message' => $message ?: 'Not enough stock to process this order.',
                        ]);

                        throw $e;
                    }

                    $order->update($updateData);

                    if (Schema::hasTable('payments')) {
                        DB::table('payments')
                            ->where('order_id', $order->id)
                            ->update([
                                'status' => 'paid',
                                'updated_at' => now(),
                            ]);
                    }

                    Log::info('Order Xendit sync marked order as paid', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'invoice_status' => $invoiceStatus,
                        'new_order_status' => $newStatus,
                    ]);

                    continue;
                }

                if ($invoiceStatus === 'EXPIRED') {
                    $order->update([
                        'payment_status' => 'expired',
                        'updated_at' => now(),
                    ]);

                    continue;
                }

                if (in_array($invoiceStatus, ['FAILED', 'VOIDED', 'CANCELLED', 'CANCELED'], true)) {
                    $order->update([
                        'payment_status' => 'failed',
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Order Xendit invoice sync error', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'xendit_invoice_id' => $invoiceId,
                    'xendit_external_id' => $externalId,
                    'message' => $e->getMessage(),
                ]);
            }
        }
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

    private function syncPendingReservationPayments($reservations): void
    {
        $secretKey = config('services.xendit.secret_key') ?: env('XENDIT_SECRET_KEY');

        if (empty($secretKey)) {
            Log::warning('Service reservation Xendit sync skipped because XENDIT_SECRET_KEY is missing.');
            return;
        }

        foreach ($reservations as $reservation) {
            $paymentStatus = strtolower($reservation->payment_status ?? 'pending');

            if (in_array($paymentStatus, ['paid', 'verified', 'settled', 'completed', 'rejected'], true)) {
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
                    Log::warning('Service reservation Xendit invoice sync failed', [
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
                        'paid_at' => $reservation->paid_at ?? now(),
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
                Log::warning('Service reservation Xendit invoice sync error', [
                    'reservation_id' => $reservation->id,
                    'xendit_invoice_id' => $reservation->xendit_invoice_id,
                    'message' => $e->getMessage(),
                ]);
            }
        }
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
                'updated_at' => now(),
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

        if (in_array($status, ['new', 'placed', 'confirmed'], true)) {
            return 'pending';
        }

        return $status;
    }
}