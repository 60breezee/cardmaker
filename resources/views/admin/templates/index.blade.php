@extends('layouts.app')

@section('content')
<div class="mb-5 flex items-center justify-between gap-3">
    <div>
        <p class="label-cap">Administration</p>
        <h1 class="text-[15px] font-semibold tracking-[0.08em] text-fog">TEMPLATES</h1>
    </div>
    <a href="{{ route('admin.templates.create') }}" class="btn-primary">✦ Nouveau template</a>
</div>

<div class="glass overflow-hidden rounded-[20px]">
    <table class="w-full text-left">
        <thead>
            <tr class="border-b border-[var(--tk-line-2)]">
                <th class="label-cap p-4 font-medium">Nom</th>
                <th class="label-cap p-4 font-medium">Catégorie</th>
                <th class="label-cap p-4 font-medium">Actif</th>
                <th class="label-cap p-4 font-medium">Premium</th>
                <th class="p-4"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($templates as $template)
                <tr class="row-dark">
                    <td class="p-4 text-[13px] text-fog">{{ $template->name }}</td>
                    <td class="p-4 text-[12px] text-fog-2">{{ $template->category }}</td>
                    <td class="p-4"><span class="pill !cursor-default {{ $template->is_active ? 'is-active' : '' }}">{{ $template->is_active ? 'Oui' : 'Non' }}</span></td>
                    <td class="p-4 text-[12px] text-fog-2">{{ $template->is_premium ? 'Oui' : '—' }}</td>
                    <td class="p-4 text-right">
                        <a href="{{ route('admin.templates.edit', $template) }}" class="pill !py-1.5">Modifier</a>
                        <form class="inline" method="POST" action="{{ route('admin.templates.destroy', $template) }}">
                            @csrf
                            @method('DELETE')
                            <button class="pill !py-1.5 !border-[rgba(255,120,90,.3)] !text-[rgba(255,140,110,.85)]" onclick="return confirm('Supprimer ?')">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($templates->hasPages())
    <div class="mt-6">{{ $templates->links() }}</div>
@endif
@endsection