<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class PaymentSettingsController extends Controller
{
    /**
     * Get all payment settings.
     */
    public function index()
    {
        return response()->json(Setting::getPaymentSettings());
    }

    /**
     * Update payment settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'stripe_enabled' => 'required|in:0,1',
            'stripe_key' => 'nullable|string|max:255',
            'stripe_secret' => 'nullable|string|max:255',
            'stripe_webhook_secret' => 'nullable|string|max:255',
            'paypal_enabled' => 'required|in:0,1',
            'paypal_client_id' => 'nullable|string|max:255',
            'paypal_client_secret' => 'nullable|string|max:255',
            'paypal_mode' => 'required|in:sandbox,live',
            'cod_enabled' => 'required|in:0,1',
        ]);

        // Validate Stripe if enabled
        if ($request->stripe_enabled === '1') {
            if (empty($request->stripe_key) || empty($request->stripe_secret)) {
                return response()->json([
                    'message' => 'Stripe credentials are required when Stripe is enabled.'
                ], 422);
            }
        }

        // Validate PayPal if enabled
        if ($request->paypal_enabled === '1') {
            if (empty($request->paypal_client_id) || empty($request->paypal_client_secret)) {
                return response()->json([
                    'message' => 'PayPal credentials are required when PayPal is enabled.'
                ], 422);
            }
        }

        // At least one payment method must be enabled
        if ($request->stripe_enabled === '0' && $request->paypal_enabled === '0' && $request->cod_enabled === '0') {
            return response()->json([
                'message' => 'At least one payment method must be enabled.'
            ], 422);
        }

        // Save settings
        Setting::savePaymentSettings([
            'stripe_enabled' => $request->stripe_enabled,
            'stripe_key' => $request->stripe_key,
            'stripe_secret' => $request->stripe_secret,
            'stripe_webhook_secret' => $request->stripe_webhook_secret,
            'paypal_enabled' => $request->paypal_enabled,
            'paypal_client_id' => $request->paypal_client_id,
            'paypal_client_secret' => $request->paypal_client_secret,
            'paypal_mode' => $request->paypal_mode,
            'cod_enabled' => $request->cod_enabled,
        ]);

        return response()->json([
            'message' => 'Payment settings updated successfully.',
            'settings' => Setting::getPaymentSettings(),
        ]);
    }

    /**
     * Toggle a payment method.
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'method' => 'required|in:stripe,paypal,cod',
            'enabled' => 'required|boolean',
        ]);

        $key = $request->method . '_enabled';
        
        // If enabling, check credentials exist
        if ($request->enabled) {
            if ($request->method === 'stripe') {
                $keyExists = Setting::getValue('stripe_key') && Setting::getValue('stripe_secret');
                if (!$keyExists) {
                    return response()->json([
                        'message' => 'Please configure Stripe credentials before enabling.'
                    ], 422);
                }
            } elseif ($request->method === 'paypal') {
                $keyExists = Setting::getValue('paypal_client_id') && Setting::getValue('paypal_client_secret');
                if (!$keyExists) {
                    return response()->json([
                        'message' => 'Please configure PayPal credentials before enabling.'
                    ], 422);
                }
            }
        }

        Setting::setValue($key, $request->enabled ? '1' : '0');

        return response()->json([
            'message' => ucfirst($request->method) . ' has been ' . ($request->enabled ? 'enabled' : 'disabled') . '.',
            'enabled' => $request->enabled,
        ]);
    }

    /**
     * Test Stripe connection.
     */
    public function testStripe(Request $request)
    {
        $stripeKey = Setting::getValue('stripe_key');
        $stripeSecret = Setting::getValue('stripe_secret');

        if (empty($stripeKey) || empty($stripeSecret)) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe credentials are not configured.'
            ], 422);
        }

        try {
            \Stripe\Stripe::setApiKey($stripeSecret);
            
            // Test the API key by retrieving account info
            $account = \Stripe\Account::retrieve();

            return response()->json([
                'success' => true,
                'message' => 'Stripe connection successful!',
                'account' => [
                    'id' => $account->id,
                    'email' => $account->email,
                    'business_name' => $account->business_name,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe connection failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Test PayPal connection.
     */
    public function testPayPal(Request $request)
    {
        $clientId = Setting::getValue('paypal_client_id');
        $clientSecret = Setting::getValue('paypal_client_secret');
        $mode = Setting::getValue('paypal_mode', 'sandbox');

        if (empty($clientId) || empty($clientSecret)) {
            return response()->json([
                'success' => false,
                'message' => 'PayPal credentials are not configured.'
            ], 422);
        }

        try {
            $baseUrl = $mode === 'live' 
                ? 'https://api-m.paypal.com' 
                : 'https://api-m.sandbox.paypal.com';

            $response = \Illuminate\Support\Facades\Http::asForm()
                ->withBasicAuth($clientId, $clientSecret)
                ->post("{$baseUrl}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'PayPal connection successful!',
                    'mode' => $mode,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'PayPal connection failed: Invalid credentials.',
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'PayPal connection failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get enabled payment methods for frontend.
     */
    public function methods()
    {
        return response()->json(Setting::getEnabledPaymentMethods());
    }
}