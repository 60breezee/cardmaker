@extends('layouts.app')

@section('content')
<div class="mb-5">
    <p class="label-cap">Administration</p>
    <h1 class="text-[15px] font-semibold tracking-[0.08em] text-fog">{{ $template->exists ? 'MODIFIER' : 'CRÉER' }} UN TEMPLATE</h1>
</div>

<form method="POST" action="{{ $template->exists ? route('admin.templates.update', $template) : route('admin.templates.store') }}" class="glass max-w-3xl space-y-4 rounded-[20px] p-6">
    @csrf
    @if($template->exists)
        @method('PUT')
    @endif
    <label class="block">
        <span class="label-cap mb-1.5 block">Nom</span>
        <input class="ui-input" name="name" value="{{ old('name', $template->name) }}" required>
    </label>
    <label class="block">
        <span class="label-cap mb-1.5 block">Description</span>
        <textarea class="ui-textarea" name="description">{{ old('description', $template->description) }}</textarea>
    </label>
    <label class="block">
        <span class="label-cap mb-1.5 block">Catégorie</span>
        <input class="ui-input" name="category" value="{{ old('category', $template->category ?: 'professional') }}" required>
    </label>
    <label class="block">
        <span class="label-cap mb-1.5 block">Configuration JSON</span>
        <textarea class="ui-textarea h-72 font-mono text-[12px]" name="configuration" required>{{ old('configuration', json_encode($template->configuration ?: ['width' => 1050, 'height' => 600, 'elements' => []], JSON_PRETTY_PRINT)) }}</textarea>
    </label>
    <div class="flex flex-wrap items-center gap-5 text-[12px] text-fog-2">
        <label class="flex cursor-pointer items-center gap-2">
            <input class="ui-check" type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->exists ? $template->is_active : true))> Actif
        </label>
        <label class="flex cursor-pointer items-center gap-2">
            <input class="ui-check" type="checkbox" name="is_premium" value="1" @checked(old('is_premium', $template->is_premium))> Premium
        </label>
    </div>
    <button class="btn-primary">Enregistrer</button>
</form>
@endsection