<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\Webhook;

class PaymentService
{
    protected string $stripeKey;
    protected string $stripeWebhookSecret;
    protected string $paypalClientId;
    protected string $paypalClientSecret;
    protected string $paypalMode;

    public function __construct()
    {
        $this->stripeKey = config('services.stripe.secret');
        $this->stripeWebhookSecret = config('services.stripe.webhook_secret');
        $this->paypalClientId = config('services.paypal.client_id');
        $this->paypalClientSecret = config('services.paypal.client_secret');
        $this->paypalMode = config('services.paypal.mode', 'sandbox');
        Stripe::setApiKey($this->stripeKey);
    }

    /**
     * Create a Stripe PaymentIntent for an order.
     */
    public function createStripePaymentIntent(Order $order): array
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => (int) ($order->total * 100), // Stripe uses cents
                'currency' => 'usd',
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
                'description' => "Order #{$order->order_number}",
                'shipping' => [
                    'name' => $order->user->name,
                    'address' => [
                        'line1' => $order->shipping_address,
                        'country' => 'US',
                    ],
                ],
            ]);

            // Create payment record
            Payment::create([
                'order_id' => $order->id,
                'transaction_id' => 'pay_' . Str::random(16),
                'provider' => 'stripe',
                'provider_payment_id' => $paymentIntent->id,
                'amount' => $order->total,
                'currency' => 'usd',
                'status' => 'pending',
                'payment_method' => 'card',
                'metadata' => [
                    'client_secret' => $paymentIntent->client_secret,
                ],
            ]);

            return [
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ];
        } catch (ApiErrorException $e) {
            Log::error('Stripe PaymentIntent creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Payment processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle Stripe webhook events.
     */
    public function handleStripeWebhook(): void
    {
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sig_header,
                $this->stripeWebhookSecret
            );
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);
            http_response_code(400);
            exit;
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;
            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;
            case 'charge.refunded':
                $this->handleChargeRefunded($event->data->object);
                break;
        }

        http_response_code(200);
    }

    /**
     * Handle successful payment intent.
     */
    protected function handlePaymentIntentSucceeded(object $paymentIntent): void
    {
        $payment = Payment::where('provider_payment_id', $paymentIntent->id)->first();

        if ($payment) {
            DB::transaction(function () use ($payment, $paymentIntent) {
                $payment->markAsCompleted($paymentIntent->id);

                // Update order status
                $payment->order->update([
                    'status' => 'processing',
                    'payment_status' => 'completed',
                ]);

                // Log the transaction
                Log::info('Payment completed', [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'amount' => $payment->amount,
                ]);
            });
        }
    }

    /**
     * Handle failed payment intent.
     */
    protected function handlePaymentIntentFailed(object $paymentIntent): void
    {
        $payment = Payment::where('provider_payment_id', $paymentIntent->id)->first();

        if ($payment) {
            $payment->markAsFailed();

            $payment->order->update([
                'payment_status' => 'failed',
            ]);

            Log::warning('Payment failed', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'error' => $paymentIntent->last_payment_error->message ?? 'Unknown error',
            ]);
        }
    }

    /**
     * Handle refunded charge.
     */
    protected function handleChargeRefunded(object $charge): void
    {
        $payment = Payment::where('provider_payment_id', $charge->payment_intent)->first();

        if ($payment) {
            $isPartialRefund = $charge->amount_refunded < $charge->amount;
            $payment->markAsRefunded($isPartialRefund);

            Log::info('Payment refunded', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'amount_refunded' => $charge->amount_refunded / 100,
                'is_partial' => $isPartialRefund,
            ]);
        }
    }

    /**
     * Process refund for a payment.
     */
    public function processRefund(Payment $payment, int $amount = null): bool
    {
        try {
            $refundAmount = $amount ?? (int) ($payment->amount * 100);

            $refund = Refund::create([
                'payment_intent' => $payment->provider_payment_id,
                'amount' => $refundAmount,
            ]);

            $isPartialRefund = $refundAmount < ($payment->amount * 100);
            $payment->markAsRefunded($isPartialRefund);

            Log::info('Refund processed', [
                'payment_id' => $payment->id,
                'refund_id' => $refund->id,
                'amount_refunded' => $refundAmount / 100,
            ]);

            return true;
        } catch (ApiErrorException $e) {
            Log::error('Refund failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get payment by order ID.
     */
    public function getPaymentByOrder(Order $order): ?Payment
    {
        return Payment::where('order_id', $order->id)->first();
    }

    /**
     * Validate Stripe webhook signature.
     */
    public function validateStripeWebhook(string $payload, string $signature): bool
    {
        try {
            Webhook::constructEvent($payload, $signature, $this->stripeWebhookSecret);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // ============================================
    // PayPal Integration
    // ============================================

    /**
     * Get PayPal access token.
     */
    protected function getPayPalAccessToken(): string
    {
        $baseUrl = $this->paypalMode === 'live' 
            ? 'https://api-m.paypal.com' 
            : 'https://api-m.sandbox.paypal.com';

        $response = Http::asForm()->withBasicAuth($this->paypalClientId, $this->paypalClientSecret)
            ->post("{$baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        if ($response->failed()) {
            throw new \Exception('Failed to get PayPal access token');
        }

        return $response->json()['access_token'];
    }

    /**
     * Create PayPal order for an order.
     */
    public function createPayPalOrder(Order $order): array
    {
        try {
            $accessToken = $this->getPayPalAccessToken();
            $baseUrl = $this->paypalMode === 'live' 
                ? 'https://api-m.paypal.com' 
                : 'https://api-m.sandbox.paypal.com';

            $response = Http::withToken($accessToken)
                ->post("{$baseUrl}/v2/checkout/orders", [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [
                        [
                            'reference_id' => $order->order_number,
                            'description' => "Order #{$order->order_number}",
                            'amount' => [
                                'currency_code' => 'USD',
                                'value' => number_format($order->total, 2, '.', ''),
                            ],
                        ],
                    ],
                    'application_context' => [
                        'brand_name' => config('app.name'),
                        'return_url' => route('paypal.capture', ['order' => $order->id]),
                        'cancel_url' => route('paypal.cancel', ['order' => $order->id]),
                        'user_action' => 'PAY_NOW',
                    ],
                ]);

            if ($response->failed()) {
                throw new \Exception('PayPal order creation failed: ' . $response->body());
            }

            $paypalOrder = $response->json();

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'transaction_id' => 'pp_' . Str::random(16),
                'provider' => 'paypal',
                'provider_payment_id' => $paypalOrder['id'],
                'amount' => $order->total,
                'currency' => 'usd',
                'status' => 'pending',
                'payment_method' => 'paypal',
                'metadata' => [
                    'approval_url' => collect($paypalOrder['links'])->firstWhere('rel', 'approve')['href'] ?? null,
                ],
            ]);

            return [
                'order_id' => $paypalOrder['id'],
                'approval_url' => collect($paypalOrder['links'])->firstWhere('rel', 'approve')['href'] ?? null,
                'payment_id' => $payment->id,
            ];
        } catch (\Exception $e) {
            Log::error('PayPal order creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('PayPal payment processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Capture PayPal order payment.
     */
    public function capturePayPalOrder(string $paypalOrderId, int $orderId): bool
    {
        try {
            $accessToken = $this->getPayPalAccessToken();
            $baseUrl = $this->paypalMode === 'live' 
                ? 'https://api-m.paypal.com' 
                : 'https://api-m.sandbox.paypal.com';

            $response = Http::withToken($accessToken)
                ->post("{$baseUrl}/v2/checkout/orders/{$paypalOrderId}/capture");

            if ($response->failed()) {
                throw new \Exception('PayPal capture failed: ' . $response->body());
            }

            $captureData = $response->json();

            // Find the payment
            $payment = Payment::where('provider_payment_id', $paypalOrderId)->first();

            if ($payment && $captureData['status'] === 'COMPLETED') {
                DB::transaction(function () use ($payment, $captureData, $paypalOrderId) {
                    $captureId = $captureData['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;
                    
                    $payment->markAsCompleted($captureId);

                    // Update order status
                    $payment->order->update([
                        'status' => 'processing',
                        'payment_status' => 'completed',
                    ]);

                    Log::info('PayPal payment completed', [
                        'payment_id' => $payment->id,
                        'order_id' => $payment->order_id,
                        'paypal_order_id' => $paypalOrderId,
                        'capture_id' => $captureId,
                    ]);
                });

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('PayPal capture failed', [
                'paypal_order_id' => $paypalOrderId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Process PayPal refund.
     */
    public function processPayPalRefund(Payment $payment, float $amount = null): bool
    {
        try {
            $accessToken = $this->getPayPalAccessToken();
            $baseUrl = $this->paypalMode === 'live' 
                ? 'https://api-m.paypal.com' 
                : 'https://api-m.sandbox.paypal.com';

            $captureId = $payment->provider_payment_id;
            $refundAmount = $amount ?? $payment->amount;

            $response = Http::withToken($accessToken)
                ->post("{$baseUrl}/v2/payments/captures/{$captureId}/refund", [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => number_format($refundAmount, 2, '.', ''),
                    ],
                ]);

            if ($response->failed()) {
                throw new \Exception('PayPal refund failed: ' . $response->body());
            }

            $refundData = $response->json();
            $isPartialRefund = $refundAmount < $payment->amount;

            $payment->markAsRefunded($isPartialRefund);

            Log::info('PayPal refund processed', [
                'payment_id' => $payment->id,
                'refund_id' => $refundData['id'] ?? null,
                'amount_refunded' => $refundAmount,
                'is_partial' => $isPartialRefund,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('PayPal refund failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get PayPal configuration for frontend.
     */
    public function getPayPalConfig(): array
    {
        return [
            'client_id' => $this->paypalClientId,
            'mode' => $this->paypalMode,
        ];
    }
}