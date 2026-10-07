@extends('layouts.app')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="label-cap">Facturation</p>
        <h1 class="text-[15px] font-semibold tracking-[0.08em] text-fog">ABONNEMENT</h1>
    </div>
    <span class="pill is-active !cursor-default">
        <span class="status-dot"></span>
        Plan actif : {{ strtoupper($subscription?->plan ?? 'FREE') }}
        @if($subscription?->expires_at)
            · jusqu’au {{ $subscription->expires_at->format('d/m/Y') }}
        @endif
    </span>
</div>

<div class="grid gap-4 md:grid-cols-3">
    @foreach($plans as $name => $plan)
        <div class="glass flex flex-col rounded-[20px] p-6 {{ $name === 'free' ? 'border-[var(--tk-line-2)]' : '' }}">
            <p class="label-cap">{{ $name === 'free' ? 'Gratuit' : strtoupper($name) }}</p>
            <p class="mt-3 text-3xl font-light text-fog">{{ $plan['price']['XOF'] ?? '—' }} <span class="text-[11px] text-fog-3">XOF</span></p>
            <p class="mt-3 text-[12px] leading-relaxed text-fog-2">
                {{ $plan['cards'] === null ? 'Cartes illimitées' : $plan['cards'].' cartes / mois' }}
                ·
                {{ $plan['exports'] === null ? 'exports illimités' : $plan['exports'].' exports / mois' }}
                @if($plan['premium_templates'] ?? false) · Modèles premium @endif
                @if($plan['custom_templates'] ?? false) · Modèles personnalisés @endif
                @if($plan['bulk'] ?? false) · Génération en masse @endif
            </p>
            <div class="mt-auto pt-5">
                @if($name === 'free')
                    <span class="pill !cursor-default">Plan par défaut</span>
                @else
                    @if(! $paymentsConfigured)
                        <span class="pill !cursor-default !border-[var(--tk-line-3)]">Bientôt disponible</span>
                    @else
                        <form method="POST" action="{{ route('billing.checkout') }}" class="flex gap-2">
                            @csrf
                            <input type="hidden" name="plan" value="{{ $name }}">
                            <select class="pill !cursor-pointer" name="currency">
                                <option>XOF</option>
                                <option>EUR</option>
                                <option>USD</option>
                            </select>
                            <button class="btn-primary flex-1">Choisir ce plan</button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    @endforeach
</div>

@if(! $paymentsConfigured)
    <p class="mt-4 text-[11px] text-fog-3">Le paiement en ligne sera activé prochainement. Vos plans gratuits et déjà souscrits restent actifs.</p>
@endif

<section class="glass mt-6 rounded-[20px] p-6">
    <p class="label-cap mb-4">Derniers paiements</p>
    <div class="divide-y divide-[var(--tk-line)]">
        @forelse($payments as $payment)
            <div class="flex flex-wrap justify-between gap-2 py-3 text-[12px]">
                <span class="text-fog">{{ strtoupper($payment->currency) }} {{ number_format($payment->amount, 0, ',', ' ') }} · {{ $payment->provider ?? '—' }}</span>
                <span class="text-fog-3">{{ $payment->status }} · {{ $payment->created_at->format('d/m/Y') }}</span>
            </div>
        @empty
            <p class="py-3 text-[12px] text-fog-3">Aucun paiement enregistré.</p>
        @endforelse
    </div>
</section>
@endsection