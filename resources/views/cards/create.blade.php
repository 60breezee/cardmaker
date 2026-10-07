@extends('layouts.app')

@section('content')
<div class="mb-6">
    <p class="label-cap">Étape 1 / 1</p>
    <h1 class="text-[15px] font-semibold tracking-[0.08em] text-fog">NOUVEAU PROJET</h1>
</div>

<form method="POST" action="{{ route('cards.store') }}" class="grid gap-7 lg:grid-cols-[1fr_300px]">
    @csrf

    {{-- Choix du modèle --}}
    <section>
        <p class="label-cap mb-4">Modèle</p>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
            @foreach($templates as $item)
                <label class="relative block cursor-pointer">
                    <input type="radio" name="template_id" value="{{ $item->id }}" class="peer sr-only" @checked($template?->id === $item->id)>
                    <span class="block overflow-hidden rounded-[14px] border border-[var(--tk-line)] bg-[var(--tk-chip)] p-2 transition peer-checked:border-accent/50 peer-checked:shadow-[0_0_24px_rgba(19,200,120,.12)] peer-hover:border-[var(--tk-line-3)]">
                        <span class="block overflow-hidden rounded-[10px]">
                            @include('cards._preview', ['config' => $item->configuration, 'data' => [], 'card' => null])
                        </span>
                        <span class="mt-2 block truncate px-1 text-[12px] font-medium text-fog">{{ $item->name }}</span>
                        <span class="block truncate px-1 text-[10px] text-fog-3">{{ $item->category }}</span>
                    </span>
                    <span class="absolute right-4 top-4 grid h-4 w-4 place-items-center rounded-full border border-[var(--tk-line-3)] bg-ink/80 text-[9px] text-accent opacity-0 peer-checked:opacity-100">✓</span>
                </label>
            @endforeach
        </div>
    </section>

    {{-- Informations --}}
    <aside class="glass h-fit rounded-[20px] p-5">
        <p class="label-cap mb-4">Informations</p>
        <div class="space-y-3">
            <label class="block">
                <span class="label-cap mb-1.5 block">Nom de la carte</span>
                <input class="ui-input" name="name" value="{{ old('name', 'Ma carte') }}" required>
            </label>
            <label class="block">
                <span class="label-cap mb-1.5 block">Nombre de cartes</span>
                <input class="ui-input" type="number" name="quantity" min="1" max="20" value="{{ old('quantity', 1) }}">
                <span class="mt-1 block text-[10px] leading-relaxed text-fog-3">Plusieurs exemplaires de la même carte (identifiant et QR uniques).</span>
            </label>
            @foreach(['full_name' => 'Nom complet', 'job_title' => 'Fonction', 'company' => 'Entreprise', 'email' => 'Email', 'phone' => 'Téléphone', 'identifier' => 'Identifiant'] as $key => $label)
                <label class="block">
                    <span class="label-cap mb-1.5 block">{{ $label }}</span>
                    <input class="ui-input" name="data[{{ $key }}]" value="{{ old('data.'.$key) }}">
                </label>
            @endforeach
        </div>
        <button class="btn-primary mt-5 w-full">Enregistrer</button>
    </aside>
</form>
@endsection