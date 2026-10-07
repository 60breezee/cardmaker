<?php

namespace App\Contracts;

use App\Models\User;

interface PaymentServiceInterface
{
    public function isConfigured(): bool;

    public function createCheckout(User $user, string $plan, string $currency): array;

    public function confirmWebhook(string $payload, string $signature): bool;
}
