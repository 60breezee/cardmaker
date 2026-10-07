@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-3">
    <div>
        <p class="label-cap">Galerie</p>
        <h1 class="text-[15px] font-semibold tracking-[0.08em] text-fog">MODÈLES</h1>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <form method="GET" action="{{ route('templates.index') }}" class="flex items-center gap-2">
            <input class="ui-input !w-56" name="search" value="{{ request('search') }}" placeholder="Rechercher…">
            <button class="pill">Rechercher</button>
        </form>
        <a href="{{ route('templates.builder') }}" class="btn-primary">✦ Créer un modèle</a>
    </div>
</div>

@if($customs->isNotEmpty())
    <section class="mb-8">
        <div class="mb-3 flex items-center justify-between">
            <p class="label-cap">Mes modèles</p>
        </div>
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($customs as $template)
                <article class="glass group flex flex-col rounded-[20px] p-3 transition hover:border-[var(--tk-line-3)]">
                    @php($cfg = $template->configuration ?? [])
                    <div class="relative overflow-hidden rounded-[14px] border border-[var(--tk-line)]">
                        @include('cards._preview', ['config' => $cfg, 'data' => [], 'card' => null])
                        <span class="absolute left-2.5 top-2.5 rounded-full border border-accent/40 bg-accent/12 px-2.5 py-1 text-[9px] uppercase tracking-[0.12em] text-[var(--tk-accent-ink)]">Personnalisé</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-1 pt-3 pb-1">
                        <div class="min-w-0">
                            <p class="truncate text-[13px] font-medium text-fog">{{ $template->name }}</p>
                            <p class="truncate text-[11px] text-fog-3">Créé le {{ $template->created_at->format('d/m/Y') }}</p>
                        </div>
                        <a href="{{ route('cards.create', ['template' => $template->id]) }}" class="pill shrink-0 !flex-none">Utiliser</a>
                    </div>
                </article>
            @endforeach
            <a href="{{ route('templates.builder') }}" class="glass flex min-h-[140px] flex-col items-center justify-center gap-2.5 rounded-[20px] p-3 text-fog-2 transition hover:border-[var(--tk-line-3)] hover:text-fog">
                <span class="grid h-9 w-9 place-items-center rounded-full border border-accent/40 bg-accent/12 text-[15px] text-accent">+</span>
                <span class="text-[10px] uppercase tracking-[0.14em]">Nouveau modèle</span>
            </a>
        </div>
    </section>
@endif

<section>
    <div class="mb-3 flex items-center justify-between">
        <p class="label-cap">Bibliothèque</p>
    </div>
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
    @forelse($templates as $template)
        <article class="glass group flex flex-col rounded-[20px] p-3 transition hover:border-[var(--tk-line-3)]">
            @php($cfg = $template->configuration ?? [])
            <div class="relative overflow-hidden rounded-[14px] border border-[var(--tk-line)]">
                @include('cards._preview', ['config' => $cfg, 'data' => [], 'card' => null])
                @if($template->is_premium)
                    <span class="absolute left-2.5 top-2.5 rounded-full border border-[rgba(240,160,60,.4)] bg-[rgba(240,160,60,.12)] px-2.5 py-1 text-[9px] uppercase tracking-[0.12em] text-amber-700 backdrop-blur">Premium</span>
                @endif
            </div>
            <div class="flex items-center justify-between gap-3 px-1 pt-3 pb-1">
                <div class="min-w-0">
                    <p class="truncate text-[13px] font-medium text-fog">{{ $template->name }}</p>
                    <p class="truncate text-[11px] text-fog-3">{{ $template->description }}</p>
                </div>
                <a href="{{ route('cards.create', ['template' => $template->id]) }}" class="pill shrink-0 !flex-none">Utiliser</a>
            </div>
        </article>
    @empty
        <div class="glass col-span-full rounded-[20px] px-6 py-12 text-center text-[13px] text-fog-3">Aucun modèle ne correspond à votre recherche.</div>
    @endforelse
    </div>
</section>

@if($templates->hasPages())
    <div class="mt-8">{{ $templates->links() }}</div>
@endif
@endsection