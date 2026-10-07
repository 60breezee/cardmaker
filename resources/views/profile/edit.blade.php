@extends('layouts.app')

@section('content')
<div class="mb-5">
    <p class="label-cap">Compte</p>
    <h1 class="text-[15px] font-semibold tracking-[0.08em] text-fog">PARAMÈTRES</h1>
</div>

<div class="grid gap-5 lg:grid-cols-2">
    <form method="POST" action="{{ route('profile.update') }}" class="glass h-fit rounded-[20px] p-6">
        @csrf
        @method('PUT')
        <p class="label-cap mb-4">Profil</p>
        <div class="space-y-3">
            <label class="block">
                <span class="label-cap mb-1.5 block">Nom</span>
                <input class="ui-input" name="name" value="{{ auth()->user()->name }}" required>
            </label>
            <label class="block">
                <span class="label-cap mb-1.5 block">Email</span>
                <input class="ui-input" type="email" name="email" value="{{ auth()->user()->email }}" required>
            </label>
            <button class="btn-primary w-full">Enregistrer</button>
        </div>
    </form>

    <form method="POST" action="{{ route('profile.password') }}" class="glass h-fit rounded-[20px] p-6">
        @csrf
        @method('PUT')
        <p class="label-cap mb-4">Mot de passe</p>
        <div class="space-y-3">
            <label class="block">
                <span class="label-cap mb-1.5 block">Actuel</span>
                <input class="ui-input" type="password" name="current_password" required>
            </label>
            <label class="block">
                <span class="label-cap mb-1.5 block">Nouveau</span>
                <input class="ui-input" type="password" name="password" required>
            </label>
            <label class="block">
                <span class="label-cap mb-1.5 block">Confirmation</span>
                <input class="ui-input" type="password" name="password_confirmation" required>
            </label>
            <button class="btn-primary w-full">Modifier</button>
        </div>
    </form>
</div>

<div class="mt-5 flex justify-end">
    <form method="POST" action="{{ route('profile.destroy') }}">
        @csrf
        @method('DELETE')
        <button class="pill !border-[rgba(255,120,90,.3)] !text-[rgba(255,140,110,.85)]" onclick="return confirm('Supprimer définitivement le compte ?')">Supprimer mon compte</button>
    </form>
</div>
@endsection