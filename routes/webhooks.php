<?php

use App\Http\Controllers\BillingController;
use Illuminate\Support\Facades\Route;

Route::post('/stripe', [BillingController::class, 'webhook'])->middleware('throttle:verification')->name('webhooks.stripe');
