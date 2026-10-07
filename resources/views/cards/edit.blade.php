@extends('layouts.app')

@section('content')
@php($exportFormat = strtolower((string) session('export_format', '')))
<div x-data="cardEditor(@js($card->data), @js($card->template->configuration), @js(['photo' => !empty($card->data['photo']) ? route('cards.asset', [$card, 'photo']) : null, 'logo' => !empty($card->data['logo']) ? route('cards.asset', [$card, 'logo']) : null]))" class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-slate-500">Éditeur de carte</p>
            <h1 class="text-3xl font-bold">{{ $card->name }}</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <a class="rounded border px-3 py-2 text-sm" href="{{ route('cards.history', $card) }}">Historique</a>
            @if(in_array($exportFormat, ['png', 'jpg', 'pdf'], true))
                <a class="rounded bg-emerald-600 px-3 py-2 text-sm text-white" href="{{ route('cards.download', [$card, $exportFormat]) }}">Télécharger {{ strtoupper($exportFormat) }}</a>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[180px_minmax(0,1fr)_300px]">
        <aside class="order-2 rounded-xl bg-white p-4 shadow lg:order-1">
            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Outils</p>
            <div class="space-y-2 text-sm">
                <div class="rounded bg-slate-100 p-3 font-medium">Modèle<br><span class="text-slate-500">{{ $card->template->name }}</span></div>
                <div class="rounded border p-3">Informations</div>
                <div class="rounded border p-3">Photo / Logo</div>
                <div class="rounded border p-3">QR Code</div>
            </div>
        </aside>

        <section class="order-1 flex min-h-[420px] items-center justify-center overflow-auto rounded-xl bg-slate-200 p-5 shadow-inner lg:order-2">
            <div class="relative w-full max-w-[700px] overflow-hidden rounded-lg bg-white shadow-xl" :style="{ aspectRatio: (config.width || 1050) + '/' + (config.height || 600), backgroundColor: config.background || '#fff' }">
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
                            <div class="grid h-full w-full place-items-center border-4 border-slate-900 bg-white text-center text-xs font-bold">QR<br>CODE</div>
                        </template>
                        <template x-if="element.type === 'image' || element.type === 'logo'">
                            <template x-if="assetUrls[element.field]">
                                <img class="h-full w-full object-contain" :src="assetUrls[element.field]" :alt="element.field || 'Image'">
                            </template>
                            <template x-if="!assetUrls[element.field]">
                                <div class="grid h-full w-full place-items-center border border-dashed border-slate-400 bg-slate-100 text-xs text-slate-500" x-text="element.field === 'logo' ? 'Logo' : 'Photo'"></div>
                            </template>
                        </template>
                    </div>
                </template>
                <div x-show="!(config.elements || []).length" class="grid h-full place-items-center text-slate-500">Prévisualisation du modèle</div>
            </div>
        </section>

        <aside class="order-3 rounded-xl bg-white p-5 shadow">
            <form method="POST" action="{{ route('cards.update', $card) }}" class="space-y-3">
                @csrf
                @method('PUT')
                <label class="block text-sm font-medium">Nom de la carte
                    <input class="mt-1 w-full rounded border p-2" name="name" value="{{ $card->name }}" required>
                </label>
                @foreach(['full_name'=>'Nom complet','job_title'=>'Fonction','company'=>'Entreprise','email'=>'Email','phone'=>'Téléphone','identifier'=>'Identifiant'] as $key=>$label)
                    <label class="block text-sm font-medium">{{ $label }}
                        <input class="mt-1 w-full rounded border p-2" name="data[{{ $key }}]" x-model="data.{{ $key }}" value="{{ $card->data[$key] ?? '' }}">
                    </label>
                @endforeach
                <label class="flex gap-2 text-sm"><input type="checkbox" name="is_public" value="1" @checked($card->is_public)> Vérification publique</label>
                <button class="w-full rounded bg-slate-900 px-4 py-2 text-white">Sauvegarder</button>
            </form>

            <form method="POST" enctype="multipart/form-data" action="{{ route('cards.upload', $card) }}" class="mt-4 border-t pt-4">
                @csrf
                <label class="block text-sm font-medium">Photo
                    <input class="mt-1 w-full rounded border p-2 text-sm" type="file" name="photo" accept="image/jpeg,image/png,image/webp" required>
                </label>
                <button class="mt-2 rounded border px-3 py-2 text-sm">Ajouter</button>
            </form>

            <form method="POST" action="{{ route('cards.generate', $card) }}" class="mt-4">
                @csrf
                <label class="block text-sm font-medium">Format
                    <select class="mt-1 w-full rounded border p-2" name="format">
                        <option value="png">PNG</option>
                        <option value="jpg">JPG</option>
                        <option value="pdf">PDF</option>
                    </select>
                </label>
                <button class="mt-3 w-full rounded bg-emerald-600 px-4 py-2 text-white">Générer la carte</button>
            </form>
        </aside>
    </div>
</div>

<div class="mt-4 flex flex-wrap gap-4">
    <form method="POST" enctype="multipart/form-data" action="{{ route('cards.upload-asset', [$card, 'logo']) }}" class="rounded-xl bg-white p-6 shadow">
        @csrf
        <label class="block text-sm font-medium">Logo
            <input class="mt-1 w-full rounded border p-2 text-sm" type="file" name="logo" accept="image/jpeg,image/png,image/webp" required>
        </label>
        <button class="mt-3 rounded border px-4 py-2 text-sm">Ajouter le logo</button>
    </form>
    <form method="POST" action="{{ route('cards.destroy', $card) }}" class="self-end">
        @csrf
        @method('DELETE')
        <button class="text-sm text-red-600" onclick="return confirm('Supprimer cette carte ?')">Supprimer cette carte</button>
    </form>
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
