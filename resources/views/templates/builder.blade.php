@extends('layouts.app')

@section('content')
@php($initial = [
    'width' => 1050,
    'height' => 600,
    'background' => '#0b1220',
    'elements' => [
        ['type' => 'shape', 'x' => 0, 'y' => 0, 'width' => 1050, 'height' => 12, 'color' => '#13C878', 'z_index' => 0],
        ['type' => 'logo', 'field' => 'logo', 'x' => 60, 'y' => 360, 'width' => 130, 'height' => 52, 'z_index' => 2],
        ['type' => 'text', 'field' => 'full_name', 'x' => 270, 'y' => 140, 'width' => 540, 'height' => 48, 'color' => '#f2f6fa', 'font_size' => 36, 'z_index' => 3],
        ['type' => 'text', 'field' => 'job_title', 'x' => 270, 'y' => 196, 'width' => 440, 'height' => 22, 'color' => '#a7b7c8', 'font_size' => 15, 'z_index' => 3],
        ['type' => 'text', 'field' => 'email', 'x' => 270, 'y' => 360, 'width' => 420, 'height' => 20, 'color' => '#a7b7c8', 'font_size' => 15, 'z_index' => 3],
        ['type' => 'text', 'field' => 'phone', 'x' => 270, 'y' => 392, 'width' => 420, 'height' => 20, 'color' => '#a7b7c8', 'font_size' => 15, 'z_index' => 3],
        ['type' => 'image', 'field' => 'photo', 'x' => 830, 'y' => 360, 'width' => 160, 'height' => 200, 'z_index' => 2],
        ['type' => 'qr_code', 'x' => 830, 'y' => 120, 'width' => 160, 'height' => 160, 'z_index' => 4],
    ],
])
<div x-data="templateBuilder(@js($initial))" class="space-y-6">

    <form method="POST" action="{{ route('templates.store') }}" @submit="syncConfig($el)" class="flex flex-wrap items-end justify-between gap-3">
        @csrf
        <input type="hidden" name="configuration" value="">

        <div class="flex min-w-0 items-center gap-3">
            <a href="{{ route('templates.index') }}" class="icon-btn" title="Retour">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            </a>
            <div class="min-w-0">
                <p class="label-cap">Créateur</p>
                <h1 class="truncate text-[15px] font-semibold tracking-[0.08em] text-fog">MODÈLE PERSONNALISÉ</h1>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <input class="ui-input !w-56" name="name" x-model="name" placeholder="Nom du modèle" required>
            <button class="btn-primary" :disabled="! elements.length">✦ Enregistrer</button>
        </div>
    </form>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,300px)]">

        {{-- CANVAS --}}
        <section class="flex min-w-0 flex-col">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <p class="label-cap">Canvas — glissez les éléments, cliquez pour modifier</p>
                <label class="flex items-center gap-2 text-[11px] text-fog-2">
                    <span class="label-cap">Fond</span>
                    <input type="color" class="h-7 w-10 cursor-pointer rounded-lg border border-[var(--tk-line-2)] bg-transparent p-0.5" x-model="background">
                </label>
            </div>

            <div class="stage flex min-h-[420px] flex-1 items-center justify-center overflow-hidden p-5">
                <div x-ref="stageEl" class="relative max-w-[620px] flex-1 overflow-hidden rounded-[14px] bg-white shadow-[0_30px_90px_rgba(0,0,0,.5)]"
                    :style="{ aspectRatio: width + '/' + height, background: background }"
                    @pointerdown.self="selected = null">

                    <template x-for="(el, i) in elements" :key="i">
                        <div class="absolute overflow-hidden"
                            :style="elStyle(el)"
                            @pointerdown="beginDrag(i, $event)">

                            <div class="pointer-events-none absolute inset-0" :style="selected === i ? 'outline:2px dashed var(--color-accent); outline-offset:-2px; border-radius:4px;' : ''"></div>

                            <template x-if="el.type === 'shape' || el.type === 'background'">
                                <div class="h-full w-full" :style="{ backgroundColor: el.color || '#eeeeee' }"></div>
                            </template>

                            <template x-if="el.type === 'text'">
                                <span class="block whitespace-nowrap font-semibold leading-none" :style="{ color: el.color || '#111827', fontSize: (el.font_size || 22) + 'px' }" x-text="elVal(el) || 'Texte'"></span>
                            </template>

                            <template x-if="el.type === 'qr_code'">
                                <div class="grid h-full w-full place-items-center bg-white text-center text-[11px] font-bold text-[#0a241a]">QR<br>CODE</div>
                            </template>

                            <template x-if="el.type === 'image' || el.type === 'logo'">
                                <div class="grid h-full w-full place-items-center border border-dashed text-center" style="border-color:rgba(0,0,0,.3);background:rgba(0,0,0,.07);color:rgba(0,0,0,.5);font-size:11px;"
                                    x-text="el.field === 'logo' ? 'Logo' : 'Photo'"></div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            {{-- BOÎTE À OUTILS --}}
            <div class="glass mt-4 rounded-[18px] p-3">
                <p class="label-cap mb-2.5">Ajouter un élément</p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="pill" @click="add('qr_code')">QR code</button>
                    <button type="button" class="pill" @click="add('text')">Texte</button>
                    <button type="button" class="pill" @click="add('full_name')">Nom</button>
                    <button type="button" class="pill" @click="add('email')">Email</button>
                    <button type="button" class="pill" @click="add('phone')">Téléphone</button>
                    <button type="button" class="pill" @click="add('company')">Société</button>
                    <button type="button" class="pill" @click="add('job_title')">Fonction</button>
                    <button type="button" class="pill" @click="add('logo')">Logo</button>
                    <button type="button" class="pill" @click="add('photo')">Photo</button>
                    <button type="button" class="pill" @click="add('shape')">Bande</button>
                </div>
            </div>
        </section>

        {{-- PANNEAU --}}
        <aside class="glass h-fit rounded-[20px] p-5">
            <p class="label-cap mb-4">Élément sélectionné</p>

            <template x-if="sel">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[13px] font-medium text-fog" x-text="selLabel(sel)"></p>
                        <button type="button" class="pill !py-1 !text-[rgba(255,120,90,.9)]" @click="remove(selected)">Supprimer</button>
                    </div>

                    <template x-if="sel.type === 'text' || sel.type === 'image' || sel.type === 'logo'">
                        <label class="block">
                            <span class="label-cap mb-1.5 block">Donnée liée</span>
                            <select class="ui-select" x-model="sel.field">
                                <option value="">Texte libre</option>
                                <option value="full_name">Nom complet</option>
                                <option value="job_title">Fonction</option>
                                <option value="company">Société</option>
                                <option value="email">Email</option>
                                <option value="phone">Téléphone</option>
                                <option value="identifier">Identifiant</option>
                                <option value="photo">Photo</option>
                                <option value="logo">Logo</option>
                            </select>
                        </label>
                    </template>

                    <template x-if="sel.type === 'text'">
                        <label class="block">
                            <span class="label-cap mb-1.5 block">Texte libre</span>
                            <input class="ui-input" x-model="sel.content" placeholder="Laisser vide = champ de la carte">
                        </label>
                    </template>

                    <div class="grid grid-cols-2 gap-2">
                        <label class="block"><span class="label-cap mb-1 block">X</span><input class="ui-input" type="number" x-model.number="sel.x"></label>
                        <label class="block"><span class="label-cap mb-1 block">Y</span><input class="ui-input" type="number" x-model.number="sel.y"></label>
                        <label class="block"><span class="label-cap mb-1 block">Larg.</span><input class="ui-input" type="number" x-model.number="sel.width"></label>
                        <label class="block"><span class="label-cap mb-1 block">Haut.</span><input class="ui-input" type="number" x-model.number="sel.height"></label>
                    </div>

                    <template x-if="sel.type === 'text' || sel.type === 'shape' || sel.type === 'background'">
                        <div class="flex items-center gap-2">
                            <label class="block flex-1">
                                <span class="label-cap mb-1 block">Couleur</span>
                                <input type="color" class="h-8 w-14 cursor-pointer rounded-lg border border-[var(--tk-line-2)] bg-transparent p-0.5" x-model="sel.color">
                            </label>
                            <label class="block flex-1" x-show="sel.type === 'text'">
                                <span class="label-cap mb-1 block">Police</span>
                                <input class="ui-input" type="number" x-model.number="sel.font_size">
                            </label>
                        </div>
                    </template>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="pill" @click="move(selected, -10, 0)">←</button>
                        <button type="button" class="pill" @click="move(selected, 10, 0)">→</button>
                        <button type="button" class="pill" @click="move(selected, 0, -10)">↑</button>
                        <button type="button" class="pill" @click="move(selected, 0, 10)">↓</button>
                        <button type="button" class="pill" @click="expand(selected, 1)">Zoom +</button>
                        <button type="button" class="pill" @click="expand(selected, -1)">Zoom −</button>
                    </div>
                </div>
            </template>

            <template x-if="! sel">
                <p class="text-[12px] leading-relaxed text-fog-3">Cliquez sur un élément du canvas, ou ajoutez-en un avec la boîte à outils.</p>
            </template>
        </aside>
    </div>
</div>

<script>
function templateBuilder(initial) {
    const samples = {
        full_name: 'Votre Nom',
        job_title: 'Directrice artistique',
        company: 'Votre Entreprise',
        email: 'email@entreprise.com',
        phone: '+221 77 000 00 00',
        identifier: 'CM-XXXXXX',
    };
    return {
        width: initial.width,
        height: initial.height,
        background: initial.background,
        elements: initial.elements.map((e) => ({ ...e })),
        name: '',
        selected: null,

        get sel() {
            return this.elements[this.selected] || null;
        },

        selLabel(el) {
            return ({ qr_code: 'QR code', shape: 'Bande', background: 'Fond', logo: 'Logo', image: 'Photo' })[el.type] || (el.field === 'full_name' ? 'Texte — Nom' : el.field ? 'Texte — ' + el.field : 'Texte libre');
        },

        elVal(el) {
            return samples[el.field] || el.content || '';
        },

        elStyle(el) {
            return {
                left: ((el.x || 0) / this.width * 100) + '%',
                top: ((el.y || 0) / this.height * 100) + '%',
                width: ((el.width || this.width) / this.width * 100) + '%',
                height: ((el.height || 40) / this.height * 100) + '%',
                zIndex: el.z_index || 0,
            };
        },

        select(i) {
            this.selected = i;
        },

        remove(i) {
            this.elements.splice(i, 1);
            this.selected = null;
        },

        add(type) {
            const el = this.defaults(type);
            if (!el) {
                return;
            }
            this.elements.push(el);
            this.selected = this.elements.length - 1;
        },

        defaults(type) {
            const text = (field, y) => ({ type: 'text', field, x: 270, y, width: 440, height: 22, color: '#a7b7c8', font_size: 15, z_index: 3 });
            const map = {
                qr_code: { type: 'qr_code', x: 830, y: 120, width: 160, height: 160, z_index: 4 },
                text: { type: 'text', x: 270, y: 140, width: 540, height: 48, color: '#f2f6fa', font_size: 36, z_index: 3, content: 'Texte libre' },
                full_name: { ...text('full_name', 140), color: '#f2f6fa', font_size: 36, width: 540, height: 48 },
                email: text('email', 140),
                phone: text('phone', 172),
                company: text('company', 204),
                job_title: text('job_title', 236),
                logo: { type: 'logo', field: 'logo', x: 60, y: 360, width: 130, height: 52, z_index: 2 },
                photo: { type: 'image', field: 'photo', x: 830, y: 360, width: 160, height: 200, z_index: 2 },
                shape: { type: 'shape', x: 0, y: 560, width: this.width, height: 14, color: '#13C878', z_index: 0 },
            };
            return { ...(map[type] || null) };
        },

        move(i, dx, dy) {
            const el = this.elements[i];
            if (!el) {
                return;
            }
            el.x = Math.max(0, Math.min(this.width - el.width, Math.round((el.x || 0) + dx)));
            el.y = Math.max(0, Math.min(this.height - el.height, Math.round((el.y || 0) + dy)));
        },

        expand(i, factor) {
            const el = this.elements[i];
            if (!el) {
                return;
            }
            el.width = Math.max(10, Math.min(this.width, Math.round((el.width || 0) + factor)));
            el.height = Math.max(10, Math.min(this.height, Math.round((el.height || 0) + factor)));
        },

        beginDrag(i, event) {
            this.select(i);
            const el = this.elements[i];
            const rect = this.$refs.stageEl.getBoundingClientRect();
            const scale = rect.width / this.width;
            const base = { i, ox: event.clientX, oy: event.clientY, sx: el.x, sy: el.y };
            event.preventDefault();

            const move = (ev) => {
                const target = this.elements[base.i];
                if (!target) {
                    return;
                }
                target.x = Math.round(Math.min(this.width - target.width, Math.max(0, base.sx + (ev.clientX - base.ox) / scale)));
                target.y = Math.round(Math.min(this.height - target.height, Math.max(0, base.sy + (ev.clientY - base.oy) / scale)));
            };
            const up = () => {
                window.removeEventListener('pointermove', move);
                window.removeEventListener('pointerup', up);
            };
            window.addEventListener('pointermove', move);
            window.addEventListener('pointerup', up);
        },

        syncConfig(form) {
            form.querySelector('input[name="configuration"]').value = JSON.stringify({
                width: this.width,
                height: this.height,
                background: this.background,
                elements: this.elements.map((el) => ({ ...el })),
            });
        },
    };
}
</script>
@endsection