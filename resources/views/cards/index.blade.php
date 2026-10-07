@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between gap-3">
    <div>
        <p class="label-cap">Atelier</p>
        <h1 class="text-[15px] font-semibold tracking-[0.08em] text-fog">MES CARTES</h1>
    </div>
    <a href="{{ route('cards.create') }}" class="btn-primary">✦ Nouvelle carte</a>
</div>
@include('cards._grid', ['cards' => $cards])
@endsection