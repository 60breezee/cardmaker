<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $title ?? 'CardMaker' }}</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="bg-slate-50 text-slate-900">
<nav class="border-b bg-white"><div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4"><a href="{{ route('dashboard') }}" class="text-xl font-bold">CardMaker</a><div class="flex gap-4 text-sm">
@auth
<a href="{{ route('cards.index') }}">Mes cartes</a><a href="{{ route('templates.index') }}">Modèles</a>
<a href="{{ route('profile.edit') }}">Paramètres</a>
<a href="{{ route('billing.index') }}">Abonnement</a>
<a href="{{ route('notifications.index') }}">Notifications</a>
@if(auth()->user()->isAdmin())<a href="{{ route('admin.index') }}">Admin</a>@endif
<form method="POST" action="{{ route('logout') }}">@csrf<button>Déconnexion</button></form>
@else
<a href="{{ route('login') }}">Connexion</a><a href="{{ route('register') }}">Créer un compte</a>
@endauth
</div></div></nav>
<main class="mx-auto max-w-6xl px-6 py-8">
@if(session('success'))<div class="mb-4 rounded bg-emerald-100 p-3 text-emerald-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-4 rounded bg-red-100 p-3 text-red-800"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
{{ $slot ?? '' }}
@yield('content')
</main></body></html>
