@extends('layouts.guest')

@section('content')
<div class="glass rounded-[20px] p-7 sm:p-8">
    <p class="label-cap">Retour au studio</p>
    <h1 class="mb-6 mt-1 text-[15px] font-semibold tracking-[0.08em] text-fog">CONNEXION</h1>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <label class="block">
            <span class="label-cap mb-1.5 block">Email</span>
            <input class="ui-input" type="email" name="email" value="{{ old('email') }}" placeholder="vous@studio.co" required autofocus>
        </label>
        <label class="block">
            <span class="label-cap mb-1.5 block">Mot de passe</span>
            <input class="ui-input" type="password" name="password" placeholder="••••••••" required>
        </label>
        <label class="flex cursor-pointer items-center gap-2 text-[12px] text-fog-2">
            <input class="ui-check" type="checkbox" name="remember"> Se souvenir de moi
        </label>
        <button class="btn-primary w-full">Se connecter</button>
    </form>

    <div class="mt-5 flex items-center justify-between text-[12px]">
        <a class="text-fog-3 underline-offset-4 transition hover:text-fog-2 hover:underline" href="{{ route('password.request') }}">Mot de passe oublié ?</a>
        <a class="text-accent underline-offset-4 hover:underline" href="{{ route('register') }}">Créer un compte</a>
    </div>
</div>
@endsection
