@extends('layouts.app')

@section('content')
@php($cardsCollection = $cards->getCollection())
@php($latest = $cardsCollection->first())

<div class="grid gap-7 lg:grid-cols-[minmax(200px,240px)_minmax(0,1fr)_minmax(220px,270px)]">

    {{-- GAUCHE : templates + palettes --}}
    <aside class="space-y-7">
        <section>
            <div class="mb-3 flex items-center justify-between">
                <p class="label-cap">Templates</p>
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('templates.builder') }}" class="icon-btn" title="Créer un modèle personnalisé">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    </a>
                    <a href="{{ route('templates.index') }}" class="icon-btn" title="Voir tous les modèles">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                @foreach($templates as $template)
                    <a href="{{ route('cards.create', ['template' => $template->id]) }}" class="thumb aspect-[7/4]" title="{{ $template->name }}">
                        @php($cfg = $template->configuration ?? [])
                        @if(! empty($cfg['background']))
                            <div class="h-full w-full" style="background:{{ is_string($cfg['background']) ? $cfg['background'] : '#0a241a' }};"></div>
                        @else
                            <div class="h-full w-full bg-gradient-to-br from-surface-3 to-ink"></div>
                        @endif
                        <span class="absolute inset-x-1.5 bottom-1.5 truncate rounded-full bg-ink/70 px-2 py-0.5 text-[9px] text-fog-2 backdrop-blur">{{ $template->name }}</span>
                    </a>
                @endforeach
                <a href="{{ route('cards.create') }}" class="thumb-plus aspect-[7/4]">
                    <span class="text-lg">+</span>
                </a>
            </div>
        </section>

        <section>
            <p class="label-cap mb-3">Palettes</p>
            <div class="glass-soft space-y-1.5 rounded-[16px] p-3" x-data="{ theme: localStorage.getItem('cardmaker-theme') || 'emerald' }">
                <template x-for="t in [
                    { n: 'Emerald', k: 'emerald', c: '#13C878' },
                    { n: 'Midnight', k: 'midnight', c: '#5B8CFF' },
                    { n: 'Amber', k: 'amber', c: '#F0A03C' },
                    { n: 'Mono', k: 'mono', c: '#E8E8E8' },
                ]" :key="t.k">
                    <button type="button" @click="theme = t.k; cardmakerTheme(t.k)"
                        class="flex w-full items-center gap-2.5 rounded-xl border px-3 py-2 text-left text-[11px] text-fog-2 transition hover:bg-[var(--tk-hover)] hover:text-fog"
                        :class="theme === t.k ? 'border-accent/45 bg-accent/8 text-fog' : 'border-[var(--tk-line)]'">
                        <span class="h-3 w-6 rounded-full border border-[var(--tk-line-2)]" :style="`background:${t.c}`"></span>
                        <span x-text="t.n"></span>
                    </button>
                </template>
            </div>
        </section>
    </aside>

    {{-- CENTRE : héro canvas --}}
    <section class="flex flex-col">
        <div class="mb-3 flex items-center justify-between">
            <p class="label-cap">Canvas</p>
            <span class="text-[10px] text-fog-3">1050 × 600 · {{ ($latest ? $latest->template->name : '—') }}</span>
        </div>

        <div class="stage flex min-h-[320px] flex-1 items-center justify-center p-4">
            @if($latest)
                <div class="w-full max-w-[520px]">
                    <a href="{{ route('cards.edit', $latest) }}" class="block overflow-hidden rounded-[14px] border border-[var(--tk-line-2)] shadow-[0_30px_80px_rgba(0,0,0,.45)]">
                        @include('cards._preview', ['config' => $latest->template->configuration, 'data' => $latest->data, 'card' => $latest])
                    </a>
                </div>
            @else
                <a href="{{ route('cards.create') }}" class="flex flex-col items-center gap-3 py-10 text-center transition hover:opacity-80">
                    <span class="thumb-plus grid h-16 w-16 place-items-center rounded-2xl text-2xl">+</span>
                    <span class="label-cap">Aucune carte — composer</span>
                </a>
            @endif
        </div>

        <div class="glass mx-auto -mt-5 flex w-[min(460px,96%)] items-center justify-between gap-3 rounded-[20px] px-4 py-3">
            <div class="min-w-0">
                <p class="label-cap">Dernière carte</p>
                <p class="truncate text-[12px] text-fog">{{ $latest?->name ?? 'Vide' }}</p>
            </div>
            @if($latest)
                <div class="flex items-center gap-2">
                    <a href="{{ route('cards.edit', $latest) }}" class="pill">Éditer</a>
                    <a href="{{ route('cards.create') }}" class="btn-primary pulse-glow">✦ Nouvelle</a>
                </div>
            @else
                <a href="{{ route('cards.create') }}" class="btn-primary pulse-glow">✦ Créer ma carte</a>
            @endif
        </div>
    </section>

    {{-- DROITE : variantes + stats --}}
    <aside class="space-y-7">
        <section>
            <div class="mb-3 flex items-center justify-between">
                <p class="label-cap">Générées</p>
                <a href="{{ route('cards.index') }}" class="text-[10px] text-fog-3 underline-offset-4 transition hover:text-fog-2 hover:underline">Toutes →</a>
            </div>
            <div class="grid grid-cols-2 gap-2">
                @forelse($cardsCollection->take(6) as $card)
                    <a href="{{ route('cards.edit', $card) }}" class="thumb aspect-[7/4] {{ $loop->first ? 'is-selected' : '' }}">
                        @include('cards._preview', ['config' => $card->template->configuration, 'data' => $card->data, 'card' => $card])
                        <span class="absolute inset-x-1.5 bottom-1.5 truncate rounded-full bg-ink/70 px-2 py-0.5 text-[9px] text-fog-2 backdrop-blur">{{ $card->name }}</span>
                    </a>
                @empty
                    <div class="col-span-2 rounded-[12px] border border-dashed border-[var(--tk-line-2)] p-6 text-center text-[11px] text-fog-3">Rien ici pour l’instant.</div>
                @endforelse
            </div>
        </section>

        <section class="grid grid-cols-2 gap-2">
            <div class="glass-soft rounded-[16px] p-4">
                <p class="label-cap">Créées</p>
                <p class="mt-1 text-2xl font-light text-fog">{{ $cardsCreated }}</p>
            </div>
            <div class="glass-soft rounded-[16px] p-4">
                <p class="label-cap">Exports</p>
                <p class="mt-1 text-2xl font-light text-fog">{{ $exportsCount }}</p>
            </div>
        </section>

        <section class="glass-soft flex items-center justify-between rounded-[16px] px-4 py-3">
            <span class="flex items-center gap-2 text-[11px] text-fog-2">
                <span class="status-dot"></span> Restantes ce mois
            </span>
            <span class="text-[12px] font-medium text-fog">{{ $cardsRemaining ?? 'Illimité' }}</span>
        </section>
    </aside>
</div>
@endsection