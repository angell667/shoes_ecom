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
}
