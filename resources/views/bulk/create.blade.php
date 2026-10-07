@extends('layouts.app')

@section('content')
<div class="mb-5">
    <p class="label-cap">Business</p>
    <h1 class="text-[15px] font-semibold tracking-[0.08em] text-fog">GÉNÉRATION EN MASSE</h1>
</div>

<div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
    <div class="glass h-fit rounded-[20px] p-6">
        <p class="label-cap mb-1.5">Import CSV</p>
        <p class="mb-5 text-[12px] leading-relaxed text-fog-3">Colonnes : <span class="text-fog-2">nom</span> (obligatoire), prenom, fonction, email, telephone, entreprise.</p>

        <form method="POST" enctype="multipart/form-data" action="{{ route('bulk.store') }}" class="space-y-4">
            @csrf
            <label class="block">
                <span class="label-cap mb-1.5 block">Modèle</span>
                <select class="ui-select" name="template_id">
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="label-cap mb-1.5 block">Fichier CSV</span>
                <input class="ui-input" type="file" name="csv" accept=".csv,text/csv" required>
            </label>
            <button class="btn-primary w-full">Lancer la génération</button>
        </form>
    </div>

    <div class="glass h-fit rounded-[20px] p-6">
        <p class="label-cap mb-4">Historique</p>
        <div class="space-y-2.5">
            @forelse($generations as $generation)
                <div class="flex items-center justify-between gap-3 rounded-[14px] border border-[var(--tk-line)] bg-[var(--tk-chip)] px-4 py-3">
                    <div class="min-w-0">
                        <span class="pill !cursor-default {{ $generation->status === 'completed' ? 'is-active' : ($generation->status === 'failed' ? '!border-[rgba(255,120,90,.3)] !text-[rgba(255,140,110,.9)]' : '') }}">{{ $generation->status }}</span>
                        <span class="ml-2 text-[11px] text-fog-3">#{{ $generation->id }} · {{ $generation->processed }}/{{ $generation->total ?: '…' }}</span>
                        @if($generation->error)
                            <p class="mt-1 truncate text-[11px] text-fog-3" title="{{ $generation->error }}">{{ $generation->error }}</p>
                        @endif
                    </div>
                    @if($generation->status === 'completed')
                        <a href="{{ route('bulk.download', $generation) }}" class="btn-primary !px-4 !py-2">ZIP</a>
                    @endif
                </div>
            @empty
                <p class="px-2 py-8 text-center text-[12px] text-fog-3">Aucune génération pour l’instant.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection