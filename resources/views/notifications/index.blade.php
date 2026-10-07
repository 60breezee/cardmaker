@extends('layouts.app')

@section('content')
<div class="mb-5">
    <p class="label-cap">Centre</p>
    <h1 class="text-[15px] font-semibold tracking-[0.08em] text-fog">NOTIFICATIONS</h1>
</div>

<div class="mx-auto max-w-2xl space-y-3">
    @forelse($notifications as $notification)
        <div class="glass rounded-[18px] p-4 {{ ! $notification->read_at ? 'border-accent/30' : '' }}">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[13px] font-medium text-fog">{{ $notification->data['title'] ?? 'Notification' }}</p>
                    <p class="mt-0.5 text-[12px] text-fog-2">{{ $notification->data['message'] ?? '' }}</p>
                    <p class="mt-1 text-[10px] text-fog-3">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
                @if(! $notification->read_at)
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        @method('PATCH')
                        <button class="pill">Marquer lue</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="glass rounded-[20px] px-6 py-12 text-center">
            <span class="status-dot mb-3 inline-block"></span>
            <p class="text-[13px] text-fog-3">Vous êtes à jour.</p>
        </div>
    @endforelse
</div>

@if($notifications->hasPages())
    <div class="mt-6">{{ $notifications->links() }}</div>
@endif
@endsection