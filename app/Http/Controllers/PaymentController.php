<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\RestaurantSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    private string $timezone = 'Asia/Manila';

    private array $completedStatuses = [
        'completed',
        'paid',
        'success',
        'successful',
        'verified',
        'settled',
    ];

    private array $pendingStatuses = [
        'pending',
        'processing',
        'unpaid',
    ];

    public function index(Request $request)
    {
        $mode = $request->query('mode', 'daily');
        $selectedDate = $request->query('date', now($this->timezone)->toDateString());

        try {
            $selectedDate = Carbon::parse($selectedDate, $this->timezone)->toDateString();
        } catch (\Throwable $e) {
            $selectedDate = now($this->timezone)->toDateString();
        }

        $legacyPayments = $this->getLegacyPaymentRecords($mode, $selectedDate);
        $orderPayments = $this->getOrderPaymentRecords($mode, $selectedDate);
        $reservationPayments = $this->getReservationPaymentRecords($mode, $selectedDate);

        $payments = collect()
            ->merge($orderPayments)
            ->merge($reservationPayments)
            ->merge($legacyPayments)
            ->sortByDesc(function ($payment) {
                return $payment['transaction_date']
                    ?? $payment['paid_at']
                    ?? $payment['created_at']
                    ?? $payment['updated_at']
                    ?? null;
            })
            ->values();

        $completed = $payments->filter(function ($payment) {
            return in_array(strtolower($payment['status'] ?? ''), $this->completedStatuses, true);
        });

        $pending = $payments->filter(function ($payment) {
            return in_array(strtolower($payment['status'] ?? ''), $this->pendingStatuses, true);
        });

        $restaurantSettings = RestaurantSetting::current();

        return response()->json([
            'mode' => $mode,
            'selected_date' => $selectedDate,
            'today_manila' => now($this->timezone)->toDateString(),
            'restaurant' => [
                'name' => $restaurantSettings->restaurant_name ?: 'Chef Oppa',
                'branch' => 'Main Branch',
                'address' => $restaurantSettings->address,
                'contact_number' => $restaurantSettings->contact_number,
                'website' => 'dinesync.shop',
                'logo_url' => asset('images/customer-menu/chef-oppa-logo.png'),
            ],
            'payments' => $payments,
            'summary' => [
                'total_transactions' => $payments->count(),
                'completed' => $completed->count(),
                'pending' => $pending->count(),
                'total_amount' => $completed->sum(function ($payment) {
                    return (float) ($payment['amount'] ?? 0);
                }),
            ],
        ]);
    }

    private function getManilaDayUtcRange(string $selectedDate): array
    {
        $start = Carbon::parse($selectedDate, $this->timezone)
            ->startOfDay()
            ->timezone('UTC');

        $end = Carbon::parse($selectedDate, $this->timezone)
            ->endOfDay()
            ->timezone('UTC');

        return [$start, $end];
    }

    private function applyPaymentDateFilter($query, string $table, string $selectedDate): void
    {
        [$start, $end] = $this->getManilaDayUtcRange($selectedDate);

        $query->where(function ($query) use ($table, $start, $end) {
            // Pending / Pay Later / Pay at Counter should follow ORDER CREATED DATE.
            // This makes unpaid July 1 orders show on July 1 even before settlement.
            $query->where(function ($pendingQuery) use ($table, $start, $end) {
                $pendingQuery->whereIn(DB::raw("LOWER({$table}.payment_status)"), $this->pendingStatuses)
                    ->whereBetween("{$table}.created_at", [$start, $end]);
            })
            // Completed payments should follow PAID DATE when available.
            ->orWhere(function ($completedQuery) use ($table, $start, $end) {
                $completedQuery->whereIn(DB::raw("LOWER({$table}.payment_status)"), $this->completedStatuses)
                    ->where(function ($dateQuery) use ($table, $start, $end) {
                        $dateQuery->whereBetween("{$table}.paid_at", [$start, $end])
                            ->orWhere(function ($fallbackQuery) use ($table, $start, $end) {
                                $fallbackQuery->whereNull("{$table}.paid_at")
                                    ->whereBetween("{$table}.created_at", [$start, $end]);
                            });
                    });
            })
            // Fallback for old records without payment_status.
            ->orWhere(function ($fallbackQuery) use ($table, $start, $end) {
                $fallbackQuery->whereNull("{$table}.payment_status")
                    ->whereBetween("{$table}.created_at", [$start, $end]);
            });
        });
    }

    private function getLegacyPaymentRecords(string $mode, string $selectedDate): Collection
    {
        [$start, $end] = $this->getManilaDayUtcRange($selectedDate);

        $query = Payment::with(['order.items.menuItem', 'order.discountVerifiedBy']);

        if ($mode !== 'all') {
            $query->where(function ($query) use ($start, $end) {
                $query->whereBetween('payments.created_at', [$start, $end])
                    ->orWhereHas('order', function ($orderQuery) use ($start, $end) {
                        $orderQuery->whereBetween('orders.created_at', [$start, $end]);
                    });
            });
        }

        return $query
            ->latest()
            ->get()
            ->map(function ($payment) {
                $order = $payment->order;
                $status = $this->normalizePaymentStatus($payment->status ?? $order?->payment_status);
                $transactionDate = in_array($status, $this->completedStatuses, true)
                    ? ($order?->paid_at ?? $payment->created_at)
                    : ($order?->created_at ?? $payment->created_at);

                return [
                    'id' => 2000000 + (int) $payment->id,
                    'source_type' => 'legacy_payment',
                    'source_id' => $payment->id,
                    'transaction_id' => $payment->reference_number
                        ?? ('PAY-' . str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT)),
                    'reference_number' => $payment->reference_number,
                    'order_id' => $payment->order_id,
                    'order_number' => $order?->order_number ?? ('Order #' . $payment->order_id),
                    'payment_method' => $payment->payment_method ?? $order?->payment_method ?? 'N/A',
                    'amount' => (float) ($payment->amount ?? 0),
                    'status' => $status,
                    'created_at' => $this->safeDateIso($payment->created_at),
                    'updated_at' => $this->safeDateIso($payment->updated_at),
                    'paid_at' => $this->safeDateIso($order?->paid_at),
                    'transaction_date' => $this->safeDateIso($transactionDate),
                    'order' => $order ? $this->formatOrderForReceipt($order) : null,
                ];
            });
    }

    private function getOrderPaymentRecords(string $mode, string $selectedDate): Collection
    {
        $query = Order::with(['items.menuItem', 'discountVerifiedBy'])
            ->whereNotNull('payment_method')
            ->whereIn('payment_method', [
                'Pay at Counter',
                'pay at counter',
                'Pay Later',
                'pay later',
                'Digital Payment',
                'digital payment',
                'QR PH',
                'qr ph',
                'Xendit',
                'xendit',
                'Cash',
                'cash',
            ]);

        if ($mode !== 'all') {
            $this->applyPaymentDateFilter($query, 'orders', $selectedDate);
        }

        return $query
            ->latest()
            ->get()
            ->map(function ($order) {
                $transactionId = $order->xendit_invoice_id
                    ?? $order->xendit_external_id
                    ?? $order->payment_reference
                    ?? $order->order_number
                    ?? ('ORDER-' . $order->id);

                $status = $this->normalizePaymentStatus($order->payment_status ?? 'pending');
                $transactionDate = in_array($status, $this->completedStatuses, true)
                    ? ($order->paid_at ?? $order->created_at)
                    : $order->created_at;

                return [
                    'id' => 3000000 + (int) $order->id,
                    'source_type' => 'order',
                    'source_id' => $order->id,
                    'transaction_id' => $transactionId,
                    'xendit_invoice_id' => $order->xendit_invoice_id ?? null,
                    'xendit_external_id' => $order->xendit_external_id ?? null,
                    'xendit_invoice_url' => $order->xendit_invoice_url ?? null,
                    'reference_number' => $order->payment_reference ?? null,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number ?? ('Order #' . $order->id),
                    'payment_method' => $this->normalizePaymentMethod($order->payment_method),
                    'amount' => (float) (($order->final_amount ?? 0) > 0 ? $order->final_amount : $order->total_amount),
                    'status' => $status,
                    'created_at' => $this->safeDateIso($order->created_at),
                    'updated_at' => $this->safeDateIso($order->updated_at),
                    'paid_at' => $this->safeDateIso($order->paid_at),
                    'transaction_date' => $this->safeDateIso($transactionDate),
                    'order' => $this->formatOrderForReceipt($order),
                ];
            });
    }

    private function getReservationPaymentRecords(string $mode, string $selectedDate): Collection
    {
        $query = Reservation::query()
            ->whereNotNull('payment_method');

        if ($mode !== 'all') {
            $this->applyPaymentDateFilter($query, 'reservations', $selectedDate);
        }

        return $query
            ->latest()
            ->get()
            ->map(function ($reservation) {
                $transactionId = $reservation->xendit_invoice_id
                    ?? $reservation->xendit_external_id
                    ?? $reservation->payment_reference
                    ?? ('RESERVATION-' . $reservation->id);

                $reservationNumber = '#RES-' . str_pad((string) $reservation->id, 4, '0', STR_PAD_LEFT);
                $status = $this->normalizePaymentStatus($reservation->payment_status ?? 'pending');
                $transactionDate = in_array($status, $this->completedStatuses, true)
                    ? ($reservation->paid_at ?? $reservation->created_at)
                    : $reservation->created_at;

                return [
                    'id' => 4000000 + (int) $reservation->id,
                    'source_type' => 'reservation',
                    'source_id' => $reservation->id,
                    'transaction_id' => $transactionId,
                    'xendit_invoice_id' => $reservation->xendit_invoice_id ?? null,
                    'xendit_external_id' => $reservation->xendit_external_id ?? null,
                    'xendit_invoice_url' => $reservation->xendit_invoice_url ?? null,
                    'reference_number' => $reservation->payment_reference ?? null,
                    'order_id' => null,
                    'order_number' => $reservationNumber,
                    'payment_method' => $this->normalizePaymentMethod($reservation->payment_method ?? 'Xendit'),
                    'amount' => (float) ($reservation->reservation_fee_amount ?? 0),
                    'status' => $status,
                    'created_at' => $this->safeDateIso($reservation->created_at),
                    'updated_at' => $this->safeDateIso($reservation->updated_at),
                    'paid_at' => $this->safeDateIso($reservation->paid_at),
                    'transaction_date' => $this->safeDateIso($transactionDate),
                    'order' => [
                        'id' => null,
                        'order_number' => $reservationNumber,
                        'payment_method' => $this->normalizePaymentMethod($reservation->payment_method ?? 'Xendit'),
                        'payment_status' => $status,
                        'total_amount' => (float) ($reservation->reservation_fee_amount ?? 0),
                        'items' => [
                            [
                                'id' => 'reservation-fee-' . $reservation->id,
                                'quantity' => 1,
                                'price' => (float) ($reservation->reservation_fee_amount ?? 0),
                                'name' => 'Reservation Fee',
                                'item_name' => 'Reservation Fee',
                                'notes' => 'Non-refundable securing fee',
                                'menuItem' => [
                                    'name' => 'Reservation Fee',
                                ],
                                'menu_item' => [
                                    'name' => 'Reservation Fee',
                                ],
                            ],
                        ],
                    ],
                    'reservation' => [
                        'id' => $reservation->id,
                        'customer_name' => $reservation->customer_name,
                        'customer_email' => $reservation->customer_email,
                        'customer_phone' => $reservation->customer_phone,
                        'reservation_date' => $reservation->reservation_date,
                        'reservation_time' => $reservation->reservation_time,
                        'guest_count' => $reservation->guest_count,
                    ],
                ];
            });
    }

    private function formatOrderForReceipt(Order $order): array
    {
        $discountType = strtolower(trim($order->discount_type ?? 'none'));
        $hasGovernmentDiscount = in_array($discountType, ['senior', 'pwd'], true);

        $originalAmount = (float) ($order->total_amount ?? 0);
        $finalAmount = (float) (($order->final_amount ?? 0) > 0
            ? $order->final_amount
            : $originalAmount);

        return [
            'id' => $order->id,
            'order_number' => $order->order_number ?? ('Order #' . $order->id),
            'table_number' => $order->table_number
                ?? optional($order->table)->table_number
                ?? null,
            'customer_name' => $order->customer_name,
            'payment_method' => $this->normalizePaymentMethod($order->payment_method),
            'payment_status' => $this->normalizePaymentStatus($order->payment_status ?? 'pending'),
            'original_amount' => $originalAmount,
            'total_amount' => $originalAmount,
            'final_amount' => $finalAmount,
            'amount_paid' => $finalAmount,
            'paid_at' => $this->safeDateIso($order->paid_at),
            'processed_by' => $order->discountVerifiedBy?->name ?? 'Service Staff',
            'discount' => [
                'applied' => $hasGovernmentDiscount,
                'type' => $discountType,
                'label' => match ($discountType) {
                    'senior' => 'Senior Citizen',
                    'pwd' => 'PWD',
                    default => 'None',
                },
                'qualified_diners' => (int) ($order->qualified_diners ?? 0),
                'total_diners' => (int) ($order->total_diners ?? 0),
                'holder_name' => $order->discount_holder_name,
                'id_number' => $order->discount_id_number,
                'vat_exempt_amount' => (float) ($order->vat_exempt_amount ?? 0),
                'discount_amount' => (float) ($order->discount_amount ?? 0),
                'verified_at' => $this->safeDateIso($order->discount_verified_at),
                'verified_by' => $order->discountVerifiedBy?->name,
            ],
            'items' => $order->items->map(function ($item) {
                $name = $item->menuItem->name
                    ?? $item->menuItem->item_name
                    ?? $item->name
                    ?? $item->item_name
                    ?? 'Menu Item';

                return [
                    'id' => $item->id,
                    'quantity' => (int) ($item->quantity ?? 1),
                    'price' => (float) ($item->price ?? 0),
                    'name' => $name,
                    'item_name' => $name,
                    'notes' => $item->notes ?? $item->special_request ?? null,
                    'menuItem' => [
                        'name' => $name,
                    ],
                    'menu_item' => [
                        'name' => $name,
                    ],
                ];
            })->values(),
        ];
    }

    private function normalizePaymentStatus(?string $status): string
    {
        $value = strtolower(trim($status ?? 'pending'));

        return match ($value) {
            'completed', 'paid', 'success', 'successful', 'verified', 'settled' => 'completed',
            'pending', 'processing', 'unpaid' => 'pending',
            'failed', 'expired', 'cancelled', 'canceled', 'voided', 'rejected' => 'failed',
            default => $value ?: 'pending',
        };
    }

    private function normalizePaymentMethod(?string $method): string
    {
        $value = strtolower(str_replace(['_', '-'], ' ', trim($method ?? '')));

        return match (true) {
            str_contains($value, 'digital') => 'Digital Payment',
            str_contains($value, 'qr') => 'Digital Payment',
            str_contains($value, 'xendit') => 'Digital Payment',
            str_contains($value, 'later') => 'Pay Later',
            str_contains($value, 'counter') => 'Pay at Counter',
            str_contains($value, 'cash') => 'Cash',
            default => $method ?: 'N/A',
        };
    }

    private function safeDateIso($date): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            return Carbon::parse($date)
                ->timezone($this->timezone)
                ->toIso8601String();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function show(Payment $payment)
    {
        return response()->json(
            $payment->load('order')
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:255',
        ]);

        $payment = Payment::create($validated);

        return response()->json($payment->load('order'), 201);
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'payment_method' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|string|max:255',
            'reference_number' => 'nullable|string|max:255',
        ]);

        $payment->update($validated);

        return response()->json($payment->load('order'));
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully.',
        ]);
    }
}
