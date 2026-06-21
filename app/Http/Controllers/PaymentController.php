<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $mode = $request->query('mode', 'daily');
        $selectedDate = $request->query('date', now()->toDateString());

        try {
            $selectedDate = \Carbon\Carbon::parse($selectedDate)->toDateString();
        } catch (\Exception $e) {
            $selectedDate = now()->toDateString();
        }

        $query = Payment::with('order');

        if ($mode !== 'all') {
            $query->where(function ($query) use ($selectedDate) {
                $query->whereDate('created_at', $selectedDate)
                    ->orWhere(function ($subQuery) use ($selectedDate) {
                        $subQuery->whereNull('created_at')
                            ->whereHas('order', function ($orderQuery) use ($selectedDate) {
                                $orderQuery->whereDate('created_at', $selectedDate);
                            });
                    });
            });
        }

        $payments = $query->latest()->get();

        return response()->json([
            'mode' => $mode,
            'selected_date' => $selectedDate,
            'payments' => $payments,
            'summary' => [
                'total_transactions' => $payments->count(),
                'completed' => $payments->filter(function ($payment) {
                    return in_array(strtolower($payment->status ?? ''), [
                        'completed', 'paid', 'success', 'successful',
                    ]);
                })->count(),
                'pending' => $payments->filter(function ($payment) {
                    return in_array(strtolower($payment->status ?? ''), [
                        'pending', 'processing',
                    ]);
                })->count(),
                'total_amount' => $payments->sum('amount'),
            ],
        ]);
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
            'message' => 'Payment deleted successfully.'
        ]);
    }
}