<?php

namespace App\Services;

use App\Contracts\PaymentServiceInterface;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Stripe\StripeClient;
use Stripe\Webhook;
use Throwable;

class StripePaymentService implements PaymentServiceInterface
{
    public function __construct(private readonly StripeClient $stripe) {}

    public function createCheckout(User $user, string $plan, string $currency): array
    {
        $config = config('plans.'.strtolower($plan));
        if (! $config || ! isset($config['price'][$currency]) || $config['price'][$currency] <= 0) {
            throw new InvalidArgumentException('Plan ou devise invalide.');
        }
        $amount = strtoupper($currency) === 'XOF' ? (int) $config['price'][$currency] : (int) round($config['price'][$currency] * 100);
        $session = $this->stripe->checkout->sessions->create(['mode' => 'payment', 'success_url' => route('billing.index').'?payment=success', 'cancel_url' => route('billing.index').'?payment=cancelled', 'line_items' => [['price_data' => ['currency' => strtolower($currency), 'unit_amount' => $amount, 'product_data' => ['name' => 'CardMaker '.strtoupper($plan)]], 'quantity' => 1]], 'metadata' => ['user_id' => (string) $user->id, 'plan' => strtolower($plan), 'currency' => strtoupper($currency)]]);
        Payment::create(['user_id' => $user->id, 'amount' => $config['price'][$currency], 'currency' => strtoupper($currency), 'status' => 'pending', 'provider' => 'stripe', 'external_id' => $session->id]);

        return ['id' => $session->id, 'url' => $session->url];
    }

    public function confirmWebhook(string $payload, string $signature): bool
    {
        try {
            $event = Webhook::constructEvent($payload, $signature, (string) config('services.stripe.webhook_secret'));
        } catch (Throwable) {
            return false;
        }
        if ($event->type !== 'checkout.session.completed') {
            return true;
        }
        $session = $event->data->object;
        DB::transaction(function () use ($session): void {
            $payment = Payment::where('external_id', $session->id)->lockForUpdate()->first();
            if (! $payment || $payment->status === 'completed') {
                return;
            }

            $payment->update(['status' => 'completed']);
            $plan = $session->metadata->plan ?? 'free';
            Subscription::where('user_id', $payment->user_id)
                ->where('status', 'active')
                ->update(['status' => 'expired']);
            Subscription::create([
                'user_id' => $payment->user_id,
                'plan' => $plan,
                'status' => 'active',
                'provider' => 'stripe',
                'external_id' => $session->id,
                'started_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addMonth(),
            ]);
        });

        return true;
    }
}
