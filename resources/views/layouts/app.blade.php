<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Cardmaker — Studio de cartes' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="mx-auto flex min-h-screen max-w-[1480px] flex-col p-3 sm:p-5">
    <div class="app-plate flex min-h-[calc(100vh-40px)] flex-col fade-up">

        <header class="flex items-center justify-between gap-4 px-6 py-5 sm:px-10">
            <a href="{{ route('dashboard') }}" class="group flex shrink-0 items-center gap-2.5">
                <span class="brand-dot">✦</span>
                <span class="text-[12px] font-semibold tracking-[0.22em] text-fog">CARDMAKER</span>
            </a>

            @php($nav = [
                ['dashboard', 'Atelier'],
                ['cards.index', 'Cartes'],
                ['templates.index', 'Modèles'],
                ['bulk.create', 'Génération'],
                ['billing.index', 'Abonnement'],
            ])

            @auth
            <nav class="hidden items-center gap-2 md:flex">
                @foreach ($nav as [$route, $label])
                    <a href="{{ route($route) }}" class="pill {{ request()->routeIs($route) ? 'is-active' : '' }}">{{ $label }}</a>
                @endforeach
            </nav>
            @endauth

            <div class="flex items-center gap-2.5">
                <button type="button" class="icon-btn" title="Clair / Sombre"
                    x-data="{ light: document.documentElement.classList.contains('light') }"
                    @click="light = !light; cardmakerMode.apply(light ? 'light' : 'dark')">
                    <svg x-show="!light" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
                    <svg x-show="light" style="display:none" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                </button>
                @auth
                    <a href="{{ route('notifications.index') }}" class="icon-btn relative" title="Notifications">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                        @if(auth()->user()->unreadNotifications->count())
                            <span class="absolute -right-0.5 -top-0.5 h-1.5 w-1.5 rounded-full bg-accent shadow-[0_0_8px_rgba(19,200,120,.9)]"></span>
                        @endif
                    </a>

                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-2 rounded-full border border-[var(--tk-line)] bg-[var(--tk-chip)] py-1 pl-1 pr-3 transition hover:border-[var(--tk-line-3)]">
                            <span class="grid h-6 w-6 place-items-center rounded-full bg-accent/15 text-[10px] font-semibold uppercase text-accent">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            <span class="hidden text-[11px] text-fog-2 sm:block">{{ Str::limit(auth()->user()->name, 12, '') }}</span>
                            <svg width="9" height="6" viewBox="0 0 10 6" fill="none" class="text-fog-3"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                        </button>
                        <div x-show="open" x-transition.opacity.duration.150ms style="display:none" class="glass absolute right-0 top-9 z-30 w-52 rounded-2xl p-2">
                            <a href="{{ route('profile.edit') }}" class="block rounded-xl px-3 py-2 text-[12px] text-fog-2 transition hover:bg-[var(--tk-hover)] hover:text-fog">Paramètres</a>
                            <a href="{{ route('notifications.index') }}" class="block rounded-xl px-3 py-2 text-[12px] text-fog-2 transition hover:bg-[var(--tk-hover)] hover:text-fog">Notifications</a>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.index') }}" class="block rounded-xl px-3 py-2 text-[12px] text-fog-2 transition hover:bg-[var(--tk-hover)] hover:text-fog">Administration</a>
                            @endif
                            <div class="my-1.5 h-px bg-[var(--tk-line)]"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="w-full rounded-xl px-3 py-2 text-left text-[12px] text-fog-2 transition hover:bg-[var(--tk-hover)] hover:text-fog">Déconnexion</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="pill">Connexion</a>
                    <a href="{{ route('register') }}" class="btn-primary">Créer un compte</a>
                @endauth
            </div>
        </header>

        @auth
        <nav class="flex gap-2 overflow-x-auto px-5 pb-1 md:hidden">
            @foreach ($nav as [$route, $label])
                <a href="{{ route($route) }}" class="pill shrink-0 {{ request()->routeIs($route) ? 'is-active' : '' }}">{{ $label }}</a>
            @endforeach
        </nav>
        @endauth

        <main class="flex-1 px-6 pb-14 pt-6 sm:px-10">
            @if(session('success'))
                <div class="flash mb-5">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="flash flash-error mb-5">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="flash flash-error mb-5">
                    <ul class="list-inside list-disc space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </main>

        <footer class="flex items-center justify-between gap-3 border-t border-[var(--tk-line)] px-6 py-4 sm:px-10">
            <span class="flex items-center gap-2 text-[10px] uppercase tracking-[0.16em] text-fog-3">
                <span class="status-dot"></span> Synchronisé
            </span>
            <span class="text-[10px] uppercase tracking-[0.16em] text-fog-3">Cardmaker — Studio créatif</span>
        </footer>

    </div>
</div>
</body>
</html>