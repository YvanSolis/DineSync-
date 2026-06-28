<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Reservation;
use App\Models\RestaurantSetting;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminReservationController extends Controller
{
    private const TIMEZONE = 'Asia/Manila';
    private const RESERVATION_START_TIME = '11:00';
    private const RESERVATION_END_TIME = '19:00';
    private const MAX_RESERVATIONS_PER_SLOT = 5;

    public function index(Request $request)
    {
        $selectedDate = $request->query('date', now(self::TIMEZONE)->toDateString());

        try {
            $selectedDate = Carbon::parse($selectedDate, self::TIMEZONE)->toDateString();
        } catch (\Throwable $e) {
            $selectedDate = now(self::TIMEZONE)->toDateString();
        }

        $baseQuery = Reservation::query()
            ->whereDate('reservation_date', $selectedDate);

        $totalReservations = (clone $baseQuery)->count();
        $pendingCount = (clone $baseQuery)->where('status', 'pending')->count();
        $approvedCount = (clone $baseQuery)->where('status', 'approved')->count();
        $cancelledCount = (clone $baseQuery)->whereIn('status', ['cancelled', 'declined'])->count();

        $reservations = $baseQuery
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
            ->orderBy('reservation_time', 'asc')
            ->orderByDesc('id')
            ->paginate(10)
            ->appends($request->query());

        $settings = RestaurantSetting::current();
        $reservationFee = $settings->reservation_fee ?? 100;

        $tables = class_exists(RestaurantTable::class)
            ? RestaurantTable::orderByRaw('CAST(table_number AS INTEGER) ASC')->get()
            : collect();

        return view('admin.reservations', compact(
            'reservations',
            'selectedDate',
            'totalReservations',
            'pendingCount',
            'approvedCount',
            'cancelledCount',
            'reservationFee',
            'tables'
        ));
    }

    public function store(Request $request)
    {
        $settings = RestaurantSetting::current();
        $reservationFee = $settings->reservation_fee ?? 100;

        $validated = $this->validateReservationRequest($request);
        $autoApprove = $request->boolean('auto_approve');

        try {
            DB::transaction(function () use ($validated, $reservationFee, $autoApprove) {
                $this->ensureSlotIsAvailable(
                    $validated['reservation_date'],
                    $validated['reservation_time']
                );

                Reservation::create([
                    'user_id' => null,
                    'created_by_role' => 'admin',

                    'customer_name' => $validated['customer_name'],
                    'customer_email' => $validated['customer_email'],
                    'customer_phone' => $validated['customer_phone'],
                    'reservation_date' => $validated['reservation_date'],
                    'reservation_time' => $validated['reservation_time'],
                    'guest_count' => $validated['guest_count'],

                    'reservation_fee_amount' => $reservationFee,
                    'reservation_fee_billing_type' => 'add_to_bill',
                    'reservation_fee_added_to_bill' => false,
                    'reservation_fee_added_at' => null,
                    'reservation_fee_order_id' => null,

                    'payment_method' => 'Add to Bill',
                    'payment_reference' => null,
                    'payment_proof' => null,
                    'payment_status' => 'pending',

                    'xendit_invoice_id' => null,
                    'xendit_external_id' => null,
                    'xendit_invoice_url' => null,
                    'xendit_expiry_date' => null,
                    'paid_at' => null,

                    'notes' => $validated['notes'] ?? null,
                    'status' => $autoApprove ? 'approved' : 'pending',
                ]);
            });

            return back()->with('success', 'Admin reservation created successfully. Reservation fee will be added to the bill once the customer is seated.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage() ?: 'Unable to create reservation.');
        }
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        $request->validate([
            'status' => ['required', 'in:pending,approved,declined,arrived,seated,completed,cancelled'],
            'table_number' => ['nullable', 'string', 'max:50'],
        ]);

        $status = strtolower(trim($request->status));
        $paymentStatus = strtolower(trim($reservation->payment_status ?? 'pending'));
        $billingType = strtolower(trim($reservation->reservation_fee_billing_type ?? 'online_payment'));

        $paymentVerifiedStatuses = [
            'paid',
            'verified',
            'settled',
            'completed',
        ];

        if (
            $status === 'approved'
            && $billingType !== 'add_to_bill'
            && !in_array($paymentStatus, $paymentVerifiedStatuses, true)
        ) {
            return back()->with('error', 'Please verify the reservation payment before accepting this reservation.');
        }

        if ($status === 'seated' && !$request->filled('table_number') && !$reservation->table_number) {
            return back()->with('error', 'Please assign a table number before seating the customer.');
        }

        try {
            DB::transaction(function () use ($request, $reservation, $status) {
                $reservation->status = $status;

                if ($request->filled('table_number')) {
                    $reservation->table_number = $request->table_number;
                }

                if ($status === 'arrived') {
                    $reservation->arrived_at = now();
                }

                if ($status === 'seated') {
                    $this->seatReservationCustomer($reservation);
                    $this->addReservationFeeToBillIfNeeded($reservation);
                }

                if (in_array($status, ['completed', 'cancelled', 'declined'], true)) {
                    $this->releaseReservationTableIfNeeded($reservation);
                }

                $reservation->save();
            });

            return back()->with('success', 'Reservation status updated successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage() ?: 'Unable to update reservation status.');
        }
    }

    public function verifyPayment(Reservation $reservation)
    {
        $reservation->payment_status = 'verified';
        $reservation->status = 'approved';

        if (empty($reservation->payment_reference)) {
            $reservation->payment_reference = $reservation->xendit_invoice_id ?? $reservation->xendit_external_id ?? null;
        }

        if (empty($reservation->paid_at)) {
            $reservation->paid_at = now();
        }

        $reservation->save();

        return back()->with('success', 'Payment verified and reservation approved.');
    }

    public function rejectPayment(Reservation $reservation)
    {
        $reservation->payment_status = 'rejected';
        $reservation->status = 'declined';
        $reservation->save();

        return back()->with('success', 'Payment rejected and reservation declined.');
    }

    private function validateReservationRequest(Request $request): array
    {
        return $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'reservation_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request) {
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

    private function ensureSlotIsAvailable(string $date, string $time): void
    {
        $existingReservations = Reservation::whereDate('reservation_date', $date)
            ->where('reservation_time', $time)
            ->whereNotIn('status', ['declined', 'cancelled', 'completed'])
            ->count();

        if ($existingReservations >= self::MAX_RESERVATIONS_PER_SLOT) {
            throw new \Exception('This reservation time is already full. Please choose another time slot.');
        }
    }

    private function seatReservationCustomer(Reservation $reservation): void
    {
        if (!$reservation->table_number) {
            throw new \Exception('Please assign a table number before seating the customer.');
        }

        $reservation->seated_at = now();

        if (!$reservation->arrived_at) {
            $reservation->arrived_at = now();
        }

        $table = RestaurantTable::where('table_number', $reservation->table_number)->first();

        if (!$table) {
            throw new \Exception('Table number does not exist in table monitoring.');
        }

        if (!in_array($table->status, ['available', 'reserved'], true)) {
            throw new \Exception('Selected table is not available.');
        }

        if ($reservation->guest_count > $table->capacity) {
            throw new \Exception('Guest count exceeds the selected table capacity.');
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

    private function addReservationFeeToBillIfNeeded(Reservation $reservation): void
    {
        $billingType = strtolower(trim($reservation->reservation_fee_billing_type ?? 'online_payment'));

        if ($billingType !== 'add_to_bill') {
            return;
        }

        if ($reservation->reservation_fee_added_to_bill) {
            return;
        }

        $feeAmount = (float) ($reservation->reservation_fee_amount ?? 0);

        if ($feeAmount <= 0) {
            return;
        }

        $orderData = [
            'order_number' => $this->generateReservationFeeOrderNumber($reservation),
            'status' => 'served',
            'total_amount' => $feeAmount,
        ];

        if (Schema::hasColumn('orders', 'payment_status')) {
            $orderData['payment_status'] = 'pending';
        }

        if (Schema::hasColumn('orders', 'payment_method')) {
            $orderData['payment_method'] = 'Pay Later';
        }

        if (Schema::hasColumn('orders', 'table_number')) {
            $orderData['table_number'] = $reservation->table_number;
        }

        if (Schema::hasColumn('orders', 'customer_name')) {
            $orderData['customer_name'] = $reservation->customer_name;
        }

        if (Schema::hasColumn('orders', 'notes')) {
            $orderData['notes'] = 'Reservation Fee added to bill for Reservation #' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT);
        }

        if (Schema::hasColumn('orders', 'special_instructions')) {
            $orderData['special_instructions'] = 'Reservation Fee';
        }

        if (Schema::hasColumn('orders', 'table_session_id')) {
            $table = RestaurantTable::where('table_number', $reservation->table_number)->first();

            if ($table) {
                $activeSession = TableSession::where('restaurant_table_id', $table->id)
                    ->where('status', 'active')
                    ->latest('id')
                    ->first();

                if ($activeSession) {
                    $orderData['table_session_id'] = $activeSession->id;
                }
            }
        }

        $order = Order::create($orderData);

        $reservation->reservation_fee_added_to_bill = true;
        $reservation->reservation_fee_added_at = now();
        $reservation->reservation_fee_order_id = $order->id;
        $reservation->payment_method = 'Add to Bill';
        $reservation->payment_status = 'pending';
    }

    private function releaseReservationTableIfNeeded(Reservation $reservation): void
    {
        if (!$reservation->table_number) {
            return;
        }

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

    private function generateReservationFeeOrderNumber(Reservation $reservation): string
    {
        do {
            $orderNumber = 'RESFEE-' . $reservation->id . '-' . now()->format('YmdHis') . '-' . random_int(100, 999);
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}