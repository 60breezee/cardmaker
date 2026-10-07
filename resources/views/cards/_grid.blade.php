<div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
    @forelse($cards as $card)
        <a href="{{ route('cards.edit', $card) }}" class="glass group rounded-[18px] p-3 transition hover:border-[var(--tk-line-3)]">
            <div class="overflow-hidden rounded-[12px] border border-[var(--tk-line)]">
                @include('cards._preview', ['config' => $card->template->configuration, 'data' => $card->data, 'card' => $card])
            </div>
            <div class="flex items-center justify-between gap-2 px-1 pt-3 pb-1">
                <div class="min-w-0">
                    <p class="truncate text-[13px] font-medium text-fog">{{ $card->name }}</p>
                    <p class="text-[11px] text-fog-3">{{ $card->template->name }} · {{ $card->created_at->format('d/m/Y') }} · {{ $card->status }}</p>
                </div>
                <form method="POST" action="{{ route('cards.duplicate', $card) }}" onclick="event.stopPropagation()">
                    @csrf
                    <button class="icon-btn" title="Dupliquer">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect width="13" height="13" x="9" y="9" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    </button>
                </form>
            </div>
        </a>
    @empty
        <div class="glass col-span-full flex flex-col items-center gap-4 rounded-[20px] px-6 py-14 text-center">
            <span class="thumb-plus grid h-14 w-14 place-items-center rounded-2xl text-xl">+</span>
            <div>
                <p class="label-cap mb-1">Atelier vide</p>
                <p class="text-[13px] text-fog-2">Vous n’avez encore aucun projet.</p>
            </div>
            <a href="{{ route('cards.create') }}" class="btn-primary pulse-glow">✦ Créer ma première carte</a>
        </div>
    @endforelse
</div>
@if($cards->hasPages())
    <div class="mt-8">{{ $cards->links() }}</div>
@endif