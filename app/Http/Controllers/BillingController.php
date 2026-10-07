<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BillingController extends Controller
{
    public function index(Request $request, PaymentServiceInterface $payments)
    {
        $user = $request->user();

        return view('billing.index', [
            'plans' => config('plans'),
            'subscription' => $user->subscriptions()
                ->where('status', 'active')
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->latest('started_at')
                ->first(),
            'payments' => $user->payments()->latest()->take(10)->get(),
            'paymentsConfigured' => $payments->isConfigured(),
        ]);
    }

    public function checkout(Request $request, PaymentServiceInterface $payments)
    {
        if (! $payments->isConfigured()) {
            return back()->with('error', 'Les paiements en ligne ne sont pas encore configurés.');
        }

        $data = $request->validate(['plan' => ['required', 'in:pro,business'], 'currency' => ['required', 'in:XOF,EUR,USD']]);
        $checkout = $payments->createCheckout($request->user(), $data['plan'], $data['currency']);

        return redirect()->away($checkout['url']);
    }

    public function webhook(Request $request, PaymentServiceInterface $payments): Response
    {
        abort_unless($payments->confirmWebhook($request->getContent(), (string) $request->header('Stripe-Signature')), 400);

        return response('ok');
    }
}
