<?php

namespace App\Services;

use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuotaService
{
    public function cardLimit(User $user): ?int
    {
        return $this->plan($user)['cards'];
    }

    public function cardsRemaining(User $user): ?int
    {
        $limit = $this->cardLimit($user);
        if ($limit === null) {
            return null;
        }

        return max(0, $limit - $user->cards()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count());
    }

    public function canCreateCard(User $user): bool
    {
        $limit = $this->cardLimit($user);

        return $limit === null || $user->cards()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() < $limit;
    }

    public function canExport(User $user): bool
    {
        $limit = $this->plan($user)['exports'];

        return $limit === null || DB::table('card_exports')->where('user_id', $user->id)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() < $limit;
    }

    public function canBulkGenerate(User $user): bool
    {
        return (bool) ($this->plan($user)['bulk'] ?? false);
    }

    public function canUseTemplate(User $user, Template $template): bool
    {
        if ($template->created_by !== null && (int) $template->created_by !== (int) $user->id) {
            return false;
        }

        if (! $template->is_active) {
            return false;
        }

        return ! $template->is_premium || (bool) ($this->plan($user)['premium_templates'] ?? false);
    }

    public function canUsePremiumTemplates(User $user): bool
    {
        return (bool) ($this->plan($user)['premium_templates'] ?? false);
    }

    public function canCreateTemplate(User $user): bool
    {
        return (bool) ($this->plan($user)['custom_templates'] ?? false);
    }

    public function ensureCanCreateTemplate(User $user): void
    {
        if (! $this->canCreateTemplate($user)) {
            throw ValidationException::withMessages(['template' => 'La création de modèles personnalisés est réservée aux abonnements premium.']);
        }
    }

    public function ensureCanUseTemplate(User $user, Template $template): void
    {
        if (! $this->canUseTemplate($user, $template)) {
            throw ValidationException::withMessages([
                'template_id' => 'Ce modèle n’est pas disponible avec votre abonnement.',
            ]);
        }
    }

    public function ensureCanCreateCard(User $user): void
    {
        if (! $this->canCreateCard($user)) {
            throw ValidationException::withMessages(['quota' => 'Votre quota mensuel de cartes est atteint.']);
        }
    }

    public function ensureCanExport(User $user): void
    {
        if (! $this->canExport($user)) {
            throw ValidationException::withMessages(['quota' => 'Votre quota mensuel d’exports est atteint.']);
        }
    }

    public function ensureCanBulkGenerate(User $user): void
    {
        if (! $this->canBulkGenerate($user)) {
            throw ValidationException::withMessages(['quota' => 'La génération en masse est réservée au plan Business.']);
        }
    }

    private function plan(User $user): array
    {
        if ($user->isAdmin()) {
            return config('plans.business');
        }
        $name = $user->subscriptions()
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest('started_at')
            ->value('plan') ?: 'free';

        return config('plans.'.$name, config('plans.free'));
    }
}
