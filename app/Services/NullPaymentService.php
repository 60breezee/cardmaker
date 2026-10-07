<?php

namespace App\Services;

use App\Contracts\PaymentServiceInterface;
use App\Models\User;
use RuntimeException;

class NullPaymentService implements PaymentServiceInterface
{
    public function isConfigured(): bool
    {
        return false;
    }

    public function createCheckout(User $user, string $plan, string $currency): array
    {
        throw new RuntimeException('Aucun fournisseur de paiement n’est configuré.');
    }

    public function confirmWebhook(string $payload, string $signature): bool
    {
        return false;
    }
}
