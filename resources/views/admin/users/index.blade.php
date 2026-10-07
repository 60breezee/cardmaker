@extends('layouts.app')

@section('content')
<div class="mb-5">
    <p class="label-cap">Administration</p>
    <h1 class="text-[15px] font-semibold tracking-[0.08em] text-fog">UTILISATEURS</h1>
</div>

<div class="glass overflow-hidden rounded-[20px]">
    <table class="w-full text-left">
        <thead>
            <tr class="border-b border-[var(--tk-line-2)]">
                <th class="label-cap p-4 font-medium">Nom</th>
                <th class="label-cap p-4 font-medium">Email</th>
                <th class="label-cap p-4 font-medium">Rôle</th>
                <th class="label-cap p-4 font-medium">Statut</th>
                <th class="p-4"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr class="row-dark">
                    <td class="p-4 text-[13px] text-fog">{{ $user->name }}</td>
                    <td class="p-4 text-[12px] text-fog-2">{{ $user->email }}</td>
                    <td class="p-4"><span class="pill !cursor-default">{{ $user->role }}</span></td>
                    <td class="p-4"><span class="pill !cursor-default {{ $user->is_active ? 'is-active' : '' }}">{{ $user->is_active ? 'Actif' : 'Désactivé' }}</span></td>
                    <td class="p-4 text-right">
                        <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                            @csrf
                            @method('PATCH')
                            <button class="pill !py-1.5">{{ $user->is_active ? 'Désactiver' : 'Activer' }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($users->hasPages())
    <div class="mt-6">{{ $users->links() }}</div>
@endif
@endsection