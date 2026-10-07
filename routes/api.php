<?php

use App\Http\Controllers\Api\CardApiController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\BillingController;
use App\Models\Template;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/templates', fn () => Template::where('is_active', true)->where('is_premium', false)->latest()->paginate(20));
    Route::post('/auth/token', [TokenController::class, 'store'])->middleware('throttle:api-login');
    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('/auth/token', [TokenController::class, 'destroy']);
        Route::get('/cards', [CardApiController::class, 'index']);
        Route::post('/cards', [CardApiController::class, 'store']);
        Route::get('/cards/{card}', [CardApiController::class, 'show']);
    });
});

Route::post('/stripe/webhook', [BillingController::class, 'webhook'])->middleware('throttle:verification')->name('webhooks.stripe');
