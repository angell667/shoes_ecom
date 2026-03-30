<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Create a payment intent for an order.
     */
    public function createIntent(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->order_id);

        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Ensure order is not already paid
        if ($order->payment_status === 'completed') {
            return response()->json(['error' => 'Order already paid'], 422);
        }

        try {
            $paymentIntent = $this->paymentService->createStripePaymentIntent($order);

            return response()->json([
                'client_secret' => $paymentIntent['client_secret'],
                'payment_intent_id' => $paymentIntent['payment_intent_id'],
            ]);
        } catch (\Exception $e) {
            Log::error('Payment intent creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Payment processing failed'], 500);
        }
    }

    /**
     * Handle Stripe webhook events.
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        if (!$this->paymentService->validateStripeWebhook($payload, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $this->paymentService->handleStripeWebhook();

        return response()->json(['status' => 'success']);
    }

    /**
     * Get payment details for an order.
     */
    public function show(Request $request, Order $order)
    {
        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $payment = $this->paymentService->getPaymentByOrder($order);

        if (!$payment) {
            return response()->json(['error' => 'No payment found'], 404);
        }

        return response()->json([
            'payment' => $payment,
            'order' => $order,
        ]);
    }

    /**
     * Process refund (admin only).
     */
    public function refund(Request $request, Order $order)
    {
        // Check if user is admin
        if (!$request->user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
        ]);

        $payment = $this->paymentService->getPaymentByOrder($order);

        if (!$payment || !$payment->isCompleted()) {
            return response()->json(['error' => 'No completed payment found'], 404);
        }

        $amount = $request->amount ? (int) ($request->amount * 100) : null;
        $success = $this->paymentService->processRefund($payment, $amount);

        if ($success) {
            return response()->json([
                'message' => 'Refund processed successfully',
                'payment' => $payment->fresh(),
            ]);
        }

        return response()->json(['error' => 'Refund failed'], 500);
    }

    // ============================================
    // PayPal Integration
    // ============================================

    /**
     * Create a PayPal order for an order.
     */
    public function createPayPalOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->order_id);

        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Ensure order is not already paid
        if ($order->payment_status === 'completed') {
            return response()->json(['error' => 'Order already paid'], 422);
        }

        try {
            $paypalOrder = $this->paymentService->createPayPalOrder($order);

            return response()->json([
                'order_id' => $paypalOrder['order_id'],
                'approval_url' => $paypalOrder['approval_url'],
                'payment_id' => $paypalOrder['payment_id'],
            ]);
        } catch (\Exception $e) {
            Log::error('PayPal order creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'PayPal payment processing failed'], 500);
        }
    }

    /**
     * Capture PayPal payment.
     */
    public function capturePayPal(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'paypal_order_id' => 'required|string',
        ]);

        $order = Order::findOrFail($request->order_id);

        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $success = $this->paymentService->capturePayPalOrder(
                $request->paypal_order_id,
                $order->id
            );

            if ($success) {
                return response()->json([
                    'message' => 'Payment captured successfully',
                    'status' => 'completed',
                ]);
            }

            return response()->json(['error' => 'Payment capture failed'], 400);
        } catch (\Exception $e) {
            Log::error('PayPal capture failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Payment capture failed'], 500);
        }
    }

    /**
     * Get PayPal configuration for frontend.
     */
    public function paypalConfig()
    {
        return response()->json($this->paymentService->getPayPalConfig());
    }

    /**
     * Handle PayPal cancel.
     */
    public function paypalCancel(Request $request, Order $order)
    {
        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Update order payment status
        $order->update(['payment_status' => 'cancelled']);

        return response()->json([
            'message' => 'Payment cancelled',
            'redirect' => '/checkout',
        ]);
    }
}