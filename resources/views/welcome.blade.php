<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cardmaker — Studio de cartes</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="mx-auto flex min-h-screen max-w-[1480px] flex-col p-3 sm:p-5">
    <div class="app-plate flex flex-1 flex-col fade-up">

        <header class="flex items-center justify-between gap-4 px-6 py-5 sm:px-10">
            <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
                <span class="brand-dot">✦</span>
                <span class="text-[12px] font-semibold tracking-[0.22em] text-fog">CARDMAKER</span>
            </a>
            <nav class="hidden items-center gap-2 md:flex">
                <a href="#atelier" class="pill">Atelier</a>
                <a href="#templates" class="pill">Modèles</a>
                <a href="#systeme" class="pill">Système</a>
            </nav>
            <div class="flex items-center gap-2">
                <button type="button" class="icon-btn" title="Clair / Sombre"
                    x-data="{ light: document.documentElement.classList.contains('light') }"
                    @click="light = !light; cardmakerMode.apply(light ? 'light' : 'dark')">
                    <svg x-show="!light" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
                    <svg x-show="light" style="display:none" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                </button>
                <a href="{{ route('login') }}" class="pill">Connexion</a>
                <a href="{{ route('register') }}" class="btn-primary">Créer un compte</a>
            </div>
        </header>

        <main class="flex-1 px-6 pb-14 pt-8 sm:px-10">

            {{-- HERO --}}
            <section class="relative flex flex-col items-center py-10 sm:py-16">
                <p class="label-cap mb-5">Studio de création · cartes & identités</p>
                <h1 class="mb-3 text-center text-[13px] font-medium tracking-[0.3em] text-fog-2">CARD MAKER</h1>
                <p class="mb-12 max-w-md text-center text-[13px] leading-relaxed text-fog-3">
                    Un atelier spatial pour composer, générer et exporter vos cartes.
                </p>

                <div class="relative w-full max-w-[560px]">
                    <div class="stage relative overflow-hidden p-3">
                        <div class="relative w-full overflow-hidden rounded-[10px] border border-[rgba(255,255,255,.08)] shadow-[0_30px_80px_rgba(0,0,0,.5)]" style="aspect-ratio:1050/600; background:linear-gradient(135deg,#0b2f22 0%,#061a13 60%,#04120d 100%)">
                            <div class="absolute left-[6%] top-[13%] h-[8%] w-[16%] rounded-[4px] border border-accent/40 bg-accent/12"></div>
                            <div class="absolute left-[6%] top-[45%] text-[11px] font-semibold tracking-[0.18em] text-white/85 sm:text-[14px]">MARIE DUPONT</div>
                            <div class="absolute left-[6%] top-[58%] text-[9px] uppercase tracking-[0.2em] text-white/40 sm:text-[10px]">Directrice artistique</div>
                            <div class="absolute bottom-[14%] left-[6%] flex gap-4 text-[8px] tracking-[0.12em] text-white/30 sm:text-[9px]">
                                <span>marie@studio.co</span><span>+221 77 000 00 00</span>
                            </div>
                            <div class="absolute right-[7%] top-1/2 grid h-[26%] w-[16%] -translate-y-1/2 grid-cols-4 gap-[2px] rounded-[6px] border border-white/15 bg-white/90 p-[6px]">
                                @foreach(range(1, 16) as $i)
                                    <span class="rounded-[1px] {{ in_array($i, [1, 2, 4, 7, 8, 11, 13, 14, 16], true) ? 'bg-[#061a13]' : 'rounded-full bg-[#061a13]/0' }}"></span>
                                @endforeach
                            </div>
                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_80%_20%,rgba(19,200,120,.12),transparent_45%)]"></div>
                        </div>
                    </div>

                    <div class="glass mx-auto -mt-6 flex w-[min(420px,92%)] items-center justify-between gap-3 rounded-[20px] px-4 py-3">
                        <div class="flex items-center gap-3">
                            <span class="grid h-8 w-8 place-items-center rounded-full border border-accent/50 bg-accent/15 text-[12px] text-accent">✦</span>
                            <div>
                                <p class="label-cap">Prêt à générer</p>
                                <p class="text-[12px] text-fog-2">Canvas 1050 × 600</p>
                            </div>
                        </div>
                        <a href="{{ route('register') }}" class="btn-primary pulse-glow">Créer ma carte</a>
                    </div>
                </div>
            </section>

            {{-- TRIPTYQUE --}}
            <section id="atelier" class="mt-20 grid gap-5 lg:grid-cols-3">
                <div class="glass-soft rounded-[20px] p-5">
                    <p class="label-cap mb-4">01 — Modèles</p>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(range(1, 6) as $i)
                            <div class="thumb aspect-[7/4] {{ $i === 2 ? 'thumb-plus' : '' }}">
                                @if($i === 2)
                                    <span class="text-base">+</span>
                                @else
                                    <div class="h-full w-full" style="background:linear-gradient({{ $i * 45 }}deg,#0a2a1e,{{ $i % 2 ? '#061a13' : '#123324' }})"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-4 text-[12px] leading-relaxed text-fog-3">Une grille compacte de prêts-à-emploi. Cliquez, composez.</p>
                </div>

                <div class="glass-soft rounded-[20px] p-5">
                    <p class="label-cap mb-4">02 — Canvas</p>
                    <div class="stage aspect-[7/4] p-3">
                        <div class="relative h-full w-full overflow-hidden rounded-[8px] border border-[rgba(255,255,255,.08)]" style="background:linear-gradient(140deg,#0d3324,#051710)">
                            <div class="absolute left-[8%] top-[30%] h-[10%] w-[45%] rounded-[3px] bg-white/70"></div>
                            <div class="absolute left-[8%] top-[52%] h-[6%] w-[30%] rounded-[3px] bg-white/25"></div>
                            <div class="absolute bottom-[14%] right-[8%] h-[30%] w-[18%] rounded-[4px] border border-accent/40 bg-accent/10"></div>
                        </div>
                    </div>
                    <p class="mt-4 text-[12px] leading-relaxed text-fog-3">La carte occupe le centre. Les outils flottent autour, jamais dessus.</p>
                </div>

                <div class="glass-soft rounded-[20px] p-5">
                    <p class="label-cap mb-4">03 — Variantes</p>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(range(1, 6) as $i)
                            <div class="thumb aspect-[7/4] {{ $i === 1 ? 'is-selected' : '' }}">
                                <div class="h-full w-full" style="background:linear-gradient({{ 180 + $i * 30 }}deg,#0b2c20,{{ $i % 3 ? '#061a13' : '#14382a' }})"></div>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-4 text-[12px] leading-relaxed text-fog-3">Chaque génération produit des variantes. Choisissez au contour.</p>
                </div>
            </section>

            {{-- PALETTES --}}
            <section id="systeme" class="glass-soft mt-5 rounded-[20px] px-6 py-7">
                <div class="flex flex-wrap items-center justify-between gap-5">
                    <div>
                        <p class="label-cap mb-2">Palettes</p>
                        <p class="text-[13px] text-fog-2">Un seul accent, quatre ambiances.</p>
                    </div>
                    <div class="flex flex-wrap gap-2.5">
                        @foreach([
                            ['Emerald', '#13C878', '#0A241A'],
                            ['Midnight', '#5B8CFF', '#0A1424'],
                            ['Amber', '#F0A03C', '#241A0A'],
                            ['Mono', '#E8E8E8', '#1A1A1A'],
                        ] as [$name, $dot, $bg])
                            <span class="pill !cursor-default">
                                <span class="h-3 w-6 rounded-full border border-[rgba(255,255,255,.15)]" style="background:linear-gradient(90deg,{{ $dot }},{{ $bg }})"></span>
                                {{ $name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- CTA --}}
            <section class="mt-20 flex flex-col items-center gap-6 pb-6">
                <p class="label-cap">Prêt en 10 secondes</p>
                <a href="{{ route('register') }}" class="btn-primary !px-8 !py-3 !text-[13px]">Ouvrir le studio</a>
                <p class="text-[11px] text-fog-3">Plan gratuit · 20 cartes / mois</p>
            </section>

        </main>

        <footer class="flex items-center justify-between gap-3 border-t border-[var(--tk-line)] px-6 py-4 sm:px-10">
            <span class="flex items-center gap-2 text-[10px] uppercase tracking-[0.16em] text-fog-3">
                <span class="status-dot"></span> Studio en ligne
            </span>
            <span class="text-[10px] uppercase tracking-[0.16em] text-fog-3">Cardmaker — Studio créatif</span>
        </footer>

    </div>
</div>
</body>
</html>