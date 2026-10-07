@extends('layouts.guest')

@section('content')
<div class="glass rounded-[20px] p-7 sm:p-8">
    <p class="label-cap">Ouvrir un studio</p>
    <h1 class="mb-6 mt-1 text-[15px] font-semibold tracking-[0.08em] text-fog">CRÉER UN COMPTE</h1>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <label class="block">
            <span class="label-cap mb-1.5 block">Nom</span>
            <input class="ui-input" name="name" value="{{ old('name') }}" required autofocus>
        </label>
        <label class="block">
            <span class="label-cap mb-1.5 block">Email</span>
            <input class="ui-input" type="email" name="email" value="{{ old('email') }}" required>
        </label>
        <label class="block">
            <span class="label-cap mb-1.5 block">Mot de passe</span>
            <input class="ui-input" type="password" name="password" placeholder="8 caractères minimum" required>
        </label>
        <label class="block">
            <span class="label-cap mb-1.5 block">Confirmation</span>
            <input class="ui-input" type="password" name="password_confirmation" required>
        </label>
        <button class="btn-primary w-full">Créer mon compte</button>
    </form>

    <p class="mt-5 text-[12px] text-fog-3">
        Déjà un compte ?
        <a class="text-accent underline-offset-4 hover:underline" href="{{ route('login') }}">Connexion</a>
    </p>
</div>
@endsection
