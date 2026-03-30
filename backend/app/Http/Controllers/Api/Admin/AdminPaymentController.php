<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    /**
     * Get all payments with pagination and filtering.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['order:id,order_number', 'order.user:id,name,email']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by provider
        if ($request->has('provider') && $request->provider) {
            $query->where('provider', $request->provider);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        return response()->json($payments);
    }

    /**
     * Get payment statistics.
     */
    public function stats()
    {
        $totalPayments = Payment::count();
        $completedPayments = Payment::where('status', 'completed')->count();
        $pendingPayments = Payment::where('status', 'pending')->count();
        $failedPayments = Payment::where('status', 'failed')->count();
        $refundedPayments = Payment::whereIn('status', ['refunded', 'partially_refunded'])->count();
        
        $stripePayments = Payment::where('provider', 'stripe')->count();
        $paypalPayments = Payment::where('provider', 'paypal')->count();
        
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        $stripeRevenue = Payment::where('status', 'completed')->where('provider', 'stripe')->sum('amount');
        $paypalRevenue = Payment::where('status', 'completed')->where('provider', 'paypal')->sum('amount');

        return response()->json([
            'total_payments' => $totalPayments,
            'completed_payments' => $completedPayments,
            'pending_payments' => $pendingPayments,
            'failed_payments' => $failedPayments,
            'refunded_payments' => $refundedPayments,
            'stripe_payments' => $stripePayments,
            'paypal_payments' => $paypalPayments,
            'total_revenue' => $totalRevenue,
            'stripe_revenue' => $stripeRevenue,
            'paypal_revenue' => $paypalRevenue,
        ]);
    }

    /**
     * Get a specific payment.
     */
    public function show(Payment $payment)
    {
        $payment->load(['order', 'order.user', 'order.items', 'order.items.product']);
        
        return response()->json($payment);
    }

    /**
     * Process a refund for a payment.
     */
    public function refund(Request $request, Payment $payment)
    {
        $request->validate([
            'amount' => 'nullable|numeric|min:0.01|max:' . $payment->amount,
            'reason' => 'nullable|string|max:500',
        ]);

        if ($payment->status !== 'completed') {
            return response()->json([
                'message' => 'Only completed payments can be refunded.'
            ], 422);
        }

        $refundAmount = $request->amount ?? $payment->amount;

        // Update payment status
        if ($refundAmount >= $payment->amount) {
            $payment->update([
                'status' => 'refunded',
                'metadata' => array_merge($payment->metadata ?? [], [
                    'refund_amount' => $refundAmount,
                    'refund_reason' => $request->reason,
                    'refunded_at' => now()->toISOString(),
                ])
            ]);
        } else {
            $payment->update([
                'status' => 'partially_refunded',
                'metadata' => array_merge($payment->metadata ?? [], [
                    'refund_amount' => $refundAmount,
                    'refund_reason' => $request->reason,
                    'refunded_at' => now()->toISOString(),
                ])
            ]);
        }

        // Update order payment status
        $order = $payment->order;
        $totalRefunded = $order->payments()
            ->whereIn('status', ['refunded', 'partially_refunded'])
            ->sum('metadata->refund_amount');
        
        if ($totalRefunded >= $order->total) {
            $order->update(['payment_status' => 'refunded']);
        } else {
            $order->update(['payment_status' => 'partially_refunded']);
        }

        return response()->json([
            'message' => 'Refund processed successfully',
            'payment' => $payment->fresh()
        ]);
    }
}