@extends('layouts.guest')

@section('content')
<div class="glass rounded-[20px] p-7 sm:p-8">
    <p class="label-cap">Nouveau secret</p>
    <h1 class="mb-6 mt-1 text-[15px] font-semibold tracking-[0.08em] text-fog">NOUVEAU MOT DE PASSE</h1>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label class="block">
            <span class="label-cap mb-1.5 block">Email</span>
            <input class="ui-input" type="email" name="email" value="{{ $email }}" required>
        </label>
        <label class="block">
            <span class="label-cap mb-1.5 block">Mot de passe</span>
            <input class="ui-input" type="password" name="password" placeholder="8 caractères minimum" required>
        </label>
        <label class="block">
            <span class="label-cap mb-1.5 block">Confirmation</span>
            <input class="ui-input" type="password" name="password_confirmation" required>
        </label>
        <button class="btn-primary w-full">Réinitialiser</button>
    </form>
</div>
@endsection
