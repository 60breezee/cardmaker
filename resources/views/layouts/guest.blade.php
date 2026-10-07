<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Cardmaker' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="mx-auto flex min-h-screen max-w-[1480px] flex-col p-3 sm:p-5">
    <div class="app-plate flex flex-1 flex-col fade-up">

        <header class="flex items-center justify-between px-6 py-5 sm:px-10">
            <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
                <span class="brand-dot">✦</span>
                <span class="text-[12px] font-semibold tracking-[0.22em] text-fog">CARDMAKER</span>
            </a>
            <div class="flex items-center gap-2">
                <button type="button" class="icon-btn" title="Clair / Sombre"
                    x-data="{ light: document.documentElement.classList.contains('light') }"
                    @click="light = !light; cardmakerMode.apply(light ? 'light' : 'dark')">
                    <svg x-show="!light" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
                    <svg x-show="light" style="display:none" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                </button>
                <a href="{{ route('login') }}" class="pill">Connexion</a>
                <a href="{{ route('register') }}" class="pill">Créer un compte</a>
            </div>
        </header>

        <main class="flex flex-1 items-center justify-center px-6 py-12 sm:px-10">
            <div class="w-full max-w-md">
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
            </div>
        </main>

    </div>
</div>
</body>
</html>