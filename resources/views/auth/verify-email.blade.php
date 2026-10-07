@extends('layouts.guest')

@section('content')
<div class="glass rounded-[20px] p-7 text-center sm:p-8">
    <span class="mx-auto mb-4 grid h-10 w-10 place-items-center rounded-full border border-[rgba(19,200,120,.4)] bg-[rgba(19,200,120,.1)] text-accent shadow-[0_0_24px_rgba(19,200,120,.25)]">✦</span>
    <p class="label-cap">Vérification</p>
    <h1 class="mb-3 mt-1 text-[15px] font-semibold tracking-[0.08em] text-fog">ADRESSE EMAIL</h1>
    <p class="mb-6 text-[13px] leading-relaxed text-fog-2">Un lien de confirmation vous a été envoyé.</p>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="btn-primary">Renvoyer le lien</button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button class="pill">Déconnexion</button>
    </form>
</div>
@endsection
