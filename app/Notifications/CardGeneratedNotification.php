<?php

namespace App\Notifications;

use App\Models\Card;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CardGeneratedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Card $card, private readonly string $format = 'PNG') {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return ['title' => 'Carte générée', 'message' => 'Votre carte « '.$this->card->name.' » est prête.', 'card_id' => $this->card->id, 'format' => $this->format];
    }
}
