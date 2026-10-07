<?php

namespace App\Services;

use App\Models\Card;
use App\Models\CardExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ExportService
{
    public function __construct(private readonly CardGeneratorService $generator) {}

    public function generate(Card $card, string $format): CardExport
    {
        $format = strtolower($format);

        if (in_array($format, ['png', 'jpg', 'jpeg'], true)) {
            return $this->generator->generate($card, $format);
        }

        if ($format === 'pdf') {
            return $this->generatePdf($card);
        }

        throw new RuntimeException('Format non supporté.');
    }

    public function generatePdf(Card $card): CardExport
    {
        $card->loadMissing('template');
        $image = 'data:image/png;base64,'.base64_encode($this->generator->renderImage($card));
        $contents = Pdf::loadView('cards.pdf', ['card' => $card, 'image' => $image])->output();
        $path = 'cards/'.$card->id.'/'.Str::uuid().'.pdf';

        Storage::disk()->put($path, $contents);
        $card->update(['status' => 'generated']);

        return $card->exports()->create([
            'user_id' => $card->user_id,
            'format' => 'PDF',
            'file_path' => $path,
            'file_size' => strlen($contents),
        ]);
    }
}
