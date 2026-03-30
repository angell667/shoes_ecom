<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Catch-all for SPA - redirect all non-API routes to frontend
Route::get('/{any}', function () {
    return redirect(env('FRONTEND_URL', 'http://localhost:5173'));
})->where('any', '.*');
