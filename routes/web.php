<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminTemplateController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BulkGenerationController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TemplateController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:password');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', fn () => view('auth.verify-email'))->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
        $user = User::findOrFail($id);
        abort_unless(hash_equals((string) $hash, sha1($user->getEmailForVerification())), 403);
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect('/dashboard')->with('success', 'Email vérifié.');
    })->middleware(['signed', 'throttle:verification'])->name('verification.verify');
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Lien de vérification envoyé.');
    })->middleware('throttle:verification')->name('verification.send');
});
Route::get('/verify/{identifier}', [CardController::class, 'verify'])->middleware('throttle:verification')->name('cards.verify');
Route::get('/share/{identifier}', [CardController::class, 'verify'])->middleware('throttle:verification')->name('cards.share');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/billing/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/bulk-generation', [BulkGenerationController::class, 'create'])->name('bulk.create');
    Route::post('/bulk-generation', [BulkGenerationController::class, 'store'])->middleware('throttle:generation')->name('bulk.store');
    Route::get('/bulk-generation/{generation}/download', [BulkGenerationController::class, 'download'])->name('bulk.download');
    Route::resource('cards', CardController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('/cards/{card}/history', [CardController::class, 'history'])->name('cards.history');
    Route::post('/cards/{card}/duplicate', [CardController::class, 'duplicate'])->name('cards.duplicate');
    Route::post('/cards/{card}/generate', [CardController::class, 'generate'])->middleware('throttle:generation')->name('cards.generate');
    Route::post('/cards/{card}/upload', [CardController::class, 'upload'])->middleware('throttle:generation')->name('cards.upload');
    Route::post('/cards/{card}/upload/{asset}', [CardController::class, 'uploadAsset'])->middleware('throttle:generation')->name('cards.upload-asset');
    Route::get('/cards/{card}/asset/{asset}', [CardController::class, 'asset'])->name('cards.asset');
    Route::get('/cards/{card}/download/{format}', [CardController::class, 'download'])->name('cards.download');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::resource('templates', AdminTemplateController::class)->except(['show']);
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/toggle', [AdminUserController::class, 'toggle'])->name('users.toggle');
});
