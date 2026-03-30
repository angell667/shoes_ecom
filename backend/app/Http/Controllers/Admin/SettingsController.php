<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function getOAuth()
    {
        return response()->json(Setting::getOAuthSettings());
    }

    public function saveOAuth(Request $request)
    {
        $validated = $request->validate([
            'google_client_id' => 'nullable|string',
            'google_client_secret' => 'nullable|string',
            'google_redirect_uri' => 'nullable|string',
            'google_enabled' => 'nullable|string|in:0,1',
        ]);

        Setting::saveOAuthSettings($validated);

        return response()->json(['message' => 'OAuth settings saved successfully']);
    }
}
