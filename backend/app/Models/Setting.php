<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function setValue($key, $value)
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public static function getOAuthSettings()
    {
        return [
            'google_client_id' => static::getValue('google_client_id'),
            'google_client_secret' => static::getValue('google_client_secret'),
            'google_redirect_uri' => static::getValue('google_redirect_uri', 'http://localhost:8000/api/auth/google/callback'),
            'google_enabled' => static::getValue('google_enabled', '1'),
        ];
    }

    public static function saveOAuthSettings($data)
    {
        foreach ($data as $key => $value) {
            static::setValue($key, $value);
        }
    }

    public static function getPaymentSettings()
    {
        return [
            // Stripe settings
            'stripe_enabled' => static::getValue('stripe_enabled', '0'),
            'stripe_key' => static::getValue('stripe_key', ''),
            'stripe_secret' => static::getValue('stripe_secret', ''),
            'stripe_webhook_secret' => static::getValue('stripe_webhook_secret', ''),
            
            // PayPal settings
            'paypal_enabled' => static::getValue('paypal_enabled', '0'),
            'paypal_client_id' => static::getValue('paypal_client_id', ''),
            'paypal_client_secret' => static::getValue('paypal_client_secret', ''),
            'paypal_mode' => static::getValue('paypal_mode', 'sandbox'),
            
            // COD settings
            'cod_enabled' => static::getValue('cod_enabled', '1'),
        ];
    }

    public static function savePaymentSettings($data)
    {
        foreach ($data as $key => $value) {
            static::setValue($key, $value);
        }
    }

    public static function isStripeEnabled()
    {
        return static::getValue('stripe_enabled', '0') === '1' 
            && static::getValue('stripe_key', '') !== '' 
            && static::getValue('stripe_secret', '') !== '';
    }

    public static function isPayPalEnabled()
    {
        return static::getValue('paypal_enabled', '0') === '1' 
            && static::getValue('paypal_client_id', '') !== '' 
            && static::getValue('paypal_client_secret', '') !== '';
    }

    public static function isCodEnabled()
    {
        return static::getValue('cod_enabled', '1') === '1';
    }

    public static function getEnabledPaymentMethods()
    {
        $methods = [];
        
        if (static::isStripeEnabled()) {
            $methods['credit_card'] = [
                'name' => 'Credit Card',
                'provider' => 'stripe',
                'key' => static::getValue('stripe_key', ''),
            ];
        }
        
        if (static::isPayPalEnabled()) {
            $methods['paypal'] = [
                'name' => 'PayPal',
                'provider' => 'paypal',
                'client_id' => static::getValue('paypal_client_id', ''),
            ];
        }
        
        if (static::isCodEnabled()) {
            $methods['cod'] = [
                'name' => 'Cash on Delivery',
                'provider' => 'cod',
            ];
        }
        
        return $methods;
    }
}
