@extends('layouts.guest')

@section('content')
<div class="glass rounded-[20px] p-7 sm:p-8">
    <p class="label-cap">Récupération</p>
    <h1 class="mb-6 mt-1 text-[15px] font-semibold tracking-[0.08em] text-fog">MOT DE PASSE OUBLIÉ</h1>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <label class="block">
            <span class="label-cap mb-1.5 block">Email</span>
            <input class="ui-input" type="email" name="email" value="{{ old('email') }}" required autofocus>
        </label>
        <button class="btn-primary w-full">Recevoir le lien</button>
    </form>

    <a class="mt-5 block text-[12px] text-fog-3 underline-offset-4 transition hover:text-fog-2 hover:underline" href="{{ route('login') }}">Retour à la connexion</a>
</div>
@endsection
