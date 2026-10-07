<?php

namespace App\Providers;

use App\Contracts\PaymentServiceInterface;
use App\Services\NullPaymentService;
use App\Services\StripePaymentService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentServiceInterface::class, function () {
            return config('services.stripe.secret') ? new StripePaymentService(new StripeClient(config('services.stripe.secret'))) : new NullPaymentService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
        RateLimiter::for('register', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('password', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('generation', fn (Request $request) => Limit::perMinute(20)->by((string) ($request->user()?->id ?? $request->ip())));
        RateLimiter::for('verification', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));
        RateLimiter::for('api-login', fn (Request $request) => Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
    }
}
