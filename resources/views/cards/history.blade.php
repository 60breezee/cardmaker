@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-slate-500">Historique de la carte</p>
            <h1 class="text-3xl font-bold">{{ $card->name }}</h1>
        </div>
        <a class="rounded border px-4 py-2" href="{{ route('cards.edit', $card) }}">Retour à l’éditeur</a>
    </div>
    <div class="overflow-hidden rounded-xl bg-white shadow">
        @forelse ($activities as $activity)
            <div class="flex items-center justify-between gap-4 border-b px-5 py-4 last:border-0">
                <div>
                    <p class="font-medium text-slate-800">{{ str_replace('_', ' ', $activity->event) }}</p>
                    <p class="text-sm text-slate-500">{{ $activity->created_at->format('d/m/Y à H:i') }}</p>
                </div>
                @if ($activity->metadata)
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">{{ json_encode($activity->metadata, JSON_UNESCAPED_UNICODE) }}</span>
                @endif
            </div>
        @empty
            <p class="p-8 text-center text-slate-500">Aucune activité enregistrée pour cette carte.</p>
        @endforelse
    </div>
    {{ $activities->links() }}
</div>
@endsection
