@extends('layouts.app')

@section('content')
@php($exportFormat = strtolower((string) session('export_format', '')))
@php($generateStatus = $card->status === 'generated' ? ($exportFormat !== '' ? 'Lancé · '.strtoupper($exportFormat) : 'Lancé') : 'En attente')
<div x-data="cardEditor(@js($card->data), @js($card->template->configuration), @js([
    'photo' => ! empty($card->data['photo']) ? route('cards.asset', [$card, 'photo']) : null,
    'logo' => ! empty($card->data['logo']) ? route('cards.asset', [$card, 'logo']) : null,
]))" class="space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex min-w-0 items-center gap-3">
            <a href="{{ route('cards.index') }}" class="icon-btn" title="Retour">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            </a>
            <div class="min-w-0">
                <p class="label-cap">Atelier</p>
                <h1 class="truncate text-[15px] font-semibold tracking-[0.08em] text-fog">{{ $card->name }}</h1>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('cards.history', $card) }}" class="pill">Historique</a>
            @if(in_array($exportFormat, ['png', 'jpg', 'pdf'], true))
                <a href="{{ route('cards.download', [$card, $exportFormat]) }}" class="pill is-active">Télécharger {{ strtoupper($exportFormat) }}</a>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,260px)]">

        {{-- CANVAS + panneau flottant --}}
        <section class="flex min-w-0 flex-col">
            <div class="mb-3 flex items-center justify-between">
                <p class="label-cap">Canvas</p>
                <span class="text-[10px] text-fog-3">{{ $card->template->name }} · {{ $card->template->configuration['width'] ?? 1050 }} × {{ $card->template->configuration['height'] ?? 600 }}</span>
            </div>

            <div class="stage flex min-h-[400px] flex-1 items-center justify-center overflow-hidden p-5">
                <div class="relative w-full max-w-[560px] overflow-hidden rounded-[14px] bg-white shadow-[0_30px_90px_rgba(0,0,0,.5)]"
                    :style="{ aspectRatio: (config.width || 1050) + '/' + (config.height || 600), backgroundColor: config.background || '#ffffff' }">
                    <template x-for="(element, index) in (config.elements || [])" :key="element.type + '-' + index">
                        <div class="absolute overflow-hidden"
                            :style="elementStyle(element)"
                            x-show="['shape', 'background', 'text', 'qr_code', 'image', 'logo'].includes(element.type)">
                            <template x-if="element.type === 'shape' || element.type === 'background'">
                                <div class="h-full w-full" :style="{ backgroundColor: element.color || '#eeeeee' }"></div>
                            </template>
                            <template x-if="element.type === 'text'">
                                <span class="block whitespace-nowrap font-semibold" :style="{ color: element.color || '#111827', fontSize: (element.font_size || 22) + 'px' }" x-text="elementValue(element) || 'Texte'"></span>
                            </template>
                            <template x-if="element.type === 'qr_code'">
                                <div class="grid h-full w-full place-items-center bg-white text-center text-[10px] font-bold text-[#0a241a]">QR<br>CODE</div>
                            </template>
                            <template x-if="element.type === 'image' || element.type === 'logo'">
                                <template x-if="assetUrls[element.field]">
                                    <img class="h-full w-full object-contain" :src="assetUrls[element.field]" :alt="element.field || 'Image'">
                                </template>
                                <template x-if="!assetUrls[element.field]">
                                    <div class="grid h-full w-full place-items-center border border-dashed border-black/25 bg-black/5 text-[10px] text-black/40" x-text="element.field === 'logo' ? 'Logo' : 'Photo'"></div>
                                </template>
                            </template>
                        </div>
                    </template>
                    <div x-show="!(config.elements || []).length" class="grid h-full place-items-center text-sm text-black/30">Aperçu du modèle</div>
                </div>
            </div>

            {{-- Panneau de contrôle flottant --}}
            <form method="POST" action="{{ route('cards.generate', $card) }}" class="glass z-10 mx-auto -mt-6 flex w-[min(520px,96%)] flex-wrap items-center justify-between gap-3 rounded-[20px] px-4 py-3">
                @csrf
                <div class="flex min-w-0 items-center gap-3">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full border border-accent/50 bg-accent/15 text-[11px] text-accent">✦</span>
                    <div class="min-w-0">
                        <p class="label-cap">Générateur</p>
                        <p class="truncate text-[12px] text-fog-2">{{ $generateStatus }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <select name="format" class="pill !cursor-pointer !py-1.5">
                        <option value="png">PNG</option>
                        <option value="jpg">JPG</option>
                        <option value="pdf">PDF</option>
                    </select>
                    <button class="btn-primary pulse-glow">Générer</button>
                </div>
            </form>
        </section>

        {{-- PANNEAU FORMAIRE --}}
        <aside class="space-y-4">
            <div class="glass rounded-[20px] p-5">
                <p class="label-cap mb-4">Informations</p>
                <form method="POST" action="{{ route('cards.update', $card) }}" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <label class="block">
                        <span class="label-cap mb-1.5 block">Nom de la carte</span>
                        <input class="ui-input" name="name" value="{{ $card->name }}" required>
                    </label>
                    @foreach(['full_name' => 'Nom complet', 'job_title' => 'Fonction', 'company' => 'Entreprise', 'email' => 'Email', 'phone' => 'Téléphone', 'identifier' => 'Identifiant'] as $key => $label)
                        <label class="block">
                            <span class="label-cap mb-1.5 block">{{ $label }}</span>
                            <input class="ui-input" name="data[{{ $key }}]" x-model="data.{{ $key }}" value="{{ $card->data[$key] ?? '' }}">
                        </label>
                    @endforeach
                    <label class="flex cursor-pointer items-center gap-2 text-[12px] text-fog-2">
                        <input class="ui-check" type="checkbox" name="is_public" value="1" @checked($card->is_public)> Vérification publique
                    </label>
                    <button class="btn-primary w-full">Sauvegarder</button>
                </form>
            </div>

            <div class="glass rounded-[20px] p-5">
                <p class="label-cap mb-4">Photo</p>
                <form method="POST" enctype="multipart/form-data" action="{{ route('cards.upload', $card) }}" class="space-y-3">
                    @csrf
                    <input class="ui-input" type="file" name="photo" accept="image/jpeg,image/png,image/webp" required>
                    <button class="pill w-full justify-center">Ajouter la photo</button>
                </form>
            </div>

            <div class="glass rounded-[20px] p-5">
                <p class="label-cap mb-4">Logo</p>
                <form method="POST" enctype="multipart/form-data" action="{{ route('cards.upload-asset', [$card, 'logo']) }}" class="space-y-3">
                    @csrf
                    <input class="ui-input" type="file" name="logo" accept="image/jpeg,image/png,image/webp" required>
                    <button class="pill w-full justify-center">Ajouter le logo</button>
                </form>
            </div>

            <form method="POST" action="{{ route('cards.destroy', $card) }}" class="flex justify-end">
                @csrf
                @method('DELETE')
                <button class="pill !border-[rgba(255,120,90,.3)] !text-[rgba(255,140,110,.85)]" onclick="return confirm('Supprimer cette carte ?')">Supprimer cette carte</button>
            </form>
        </aside>
    </div>
</div>

<script>
function cardEditor(data, config, assetUrls) {
    return {
        data: data || {},
        config: config || {},
        assetUrls: assetUrls || {},
        elementValue(element) {
            return this.data[element.field] || element.content || '';
        },
        elementStyle(element) {
            const width = this.config.width || 1050;
            const height = this.config.height || 600;
            return {
                left: ((element.x || 0) / width * 100) + '%',
                top: ((element.y || 0) / height * 100) + '%',
                width: ((element.width || width) / width * 100) + '%',
                height: ((element.height || 40) / height * 100) + '%',
                zIndex: element.z_index || 0,
            };
        },
    };
}
</script>
@endsection