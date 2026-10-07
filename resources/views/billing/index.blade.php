@extends('layouts.app')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <p class="text-sm text-slate-500">Facturation</p>
        <h1 class="text-3xl font-bold">Abonnement</h1>
    </div>
    <div class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">
        Plan actif : {{ strtoupper($subscription?->plan ?? 'FREE') }}
        @if($subscription?->expires_at)
            <span class="text-slate-300">jusqu’au {{ $subscription->expires_at->format('d/m/Y') }}</span>
        @endif
    </div>
</div>

<div class="mt-6 grid gap-6 md:grid-cols-3">
    @foreach($plans as $name => $plan)
        <div class="rounded-xl bg-white p-6 shadow">
            <h2 class="text-xl font-bold uppercase">{{ $name }}</h2>
            <p class="my-4 text-slate-500">{{ $plan['cards'] ?? 'Illimité' }} cartes · {{ $plan['exports'] ?? 'Illimité' }} exports</p>
            @if($name !== 'free')
                <form method="POST" action="{{ route('billing.checkout') }}">
                    @csrf
                    <input type="hidden" name="plan" value="{{ $name }}">
                    <select class="mb-3 w-full rounded border p-2" name="currency">
                        <option>XOF</option>
                        <option>EUR</option>
                        <option>USD</option>
                    </select>
                    <button class="w-full rounded bg-slate-900 px-4 py-2 text-white">Choisir ce plan</button>
                </form>
            @else
                <span class="text-sm text-slate-500">Plan gratuit disponible par défaut</span>
            @endif
        </div>
    @endforeach
</div>

<section class="mt-10 rounded-xl bg-white p-6 shadow">
    <h2 class="text-xl font-bold">Derniers paiements</h2>
    <div class="mt-4 divide-y">
        @forelse($payments as $payment)
            <div class="flex flex-wrap justify-between gap-2 py-3 text-sm">
                <span>{{ strtoupper($payment->currency) }} {{ $payment->amount }} · {{ $payment->provider ?? '—' }}</span>
                <span class="text-slate-500">{{ $payment->status }} · {{ $payment->created_at->format('d/m/Y') }}</span>
            </div>
        @empty
            <p class="py-3 text-sm text-slate-500">Aucun paiement enregistré.</p>
        @endforelse
    </div>
</section>
@endsection
