@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex min-w-0 items-center gap-3">
            <a href="{{ route('cards.edit', $card) }}" class="icon-btn" title="Retour">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            </a>
            <div class="min-w-0">
                <p class="label-cap">Journal</p>
                <h1 class="truncate text-[15px] font-semibold tracking-[0.08em] text-fog">{{ $card->name }}</h1>
            </div>
        </div>
    </div>

    <div class="glass overflow-hidden rounded-[20px]">
        @forelse ($activities as $activity)
            <div class="row-dark flex items-center justify-between gap-4 px-5 py-4">
                <div>
                    <p class="text-[13px] font-medium text-fog">{{ str_replace('_', ' ', $activity->event) }}</p>
                    <p class="text-[11px] text-fog-3">{{ $activity->created_at->format('d/m/Y à H:i') }}</p>
                </div>
                @if ($activity->metadata)
                    <span class="pill !cursor-default">{{ json_encode($activity->metadata, JSON_UNESCAPED_UNICODE) }}</span>
                @endif
            </div>
        @empty
            <p class="px-6 py-10 text-center text-[13px] text-fog-3">Aucune activité enregistrée pour cette carte.</p>
        @endforelse
    </div>

    @if($activities->hasPages())
        <div>{{ $activities->links() }}</div>
    @endif
</div>
@endsection