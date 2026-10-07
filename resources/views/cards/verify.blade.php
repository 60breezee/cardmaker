@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-xl space-y-6">
    <div class="glass rounded-[20px] p-7 text-center sm:p-8">
        <span class="mx-auto mb-4 grid h-10 w-10 place-items-center rounded-full border border-accent/40 bg-accent/12 text-accent shadow-[0_0_24px_rgba(19,200,120,.25)]">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </span>
        <p class="label-cap mb-1">Carte vérifiée</p>
        <p class="text-[13px] text-fog-2">{{ $card->public_identifier }}</p>

        <div class="mx-auto mt-6 w-full max-w-sm overflow-hidden rounded-[14px] border border-[rgba(255,255,255,.08)] shadow-[0_20px_60px_rgba(0,0,0,.4)]">
            @include('cards._preview', ['config' => $card->template->configuration, 'data' => $card->data, 'card' => $card])
        </div>

        <div class="mt-6 grid grid-cols-3 gap-2">
            <div class="glass-soft rounded-[14px] p-3">
                <p class="label-cap">Type</p>
                <p class="mt-1 text-[12px] text-fog">{{ $card->template->name }}</p>
            </div>
            <div class="glass-soft rounded-[14px] p-3">
                <p class="label-cap">Créée</p>
                <p class="mt-1 text-[12px] text-fog">{{ $card->created_at->format('d/m/Y') }}</p>
            </div>
            <div class="glass-soft rounded-[14px] p-3">
                <p class="label-cap">Titulaire</p>
                <p class="mt-1 truncate text-[12px] text-fog">{{ $card->data['full_name'] ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection