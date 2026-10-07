@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between">
    <div>
        <p class="text-sm text-slate-500">Tableau de bord</p>
        <h1 class="text-3xl font-bold">Bonjour, {{ auth()->user()->name }}</h1>
    </div>
    <a class="rounded bg-slate-900 px-4 py-2 text-white" href="{{ route('cards.create') }}">Créer une carte</a>
</div>

<div class="mt-8 grid gap-4 md:grid-cols-3">
    <div class="rounded-xl bg-white p-5 shadow">
        <p class="text-sm text-slate-500">Cartes créées</p>
        <p class="text-3xl font-bold">{{ $cardsCreated }}</p>
    </div>
    <div class="rounded-xl bg-white p-5 shadow">
        <p class="text-sm text-slate-500">Exports</p>
        <p class="text-3xl font-bold">{{ $exportsCount }}</p>
    </div>
    <div class="rounded-xl bg-white p-5 shadow">
        <p class="text-sm text-slate-500">Cartes restantes ce mois</p>
        <p class="text-3xl font-bold">{{ $cardsRemaining ?? 'Illimité' }}</p>
    </div>
</div>

<h2 class="mt-10 text-xl font-bold">Mes cartes</h2>
@include('cards._grid', ['cards' => $cards])
@endsection
