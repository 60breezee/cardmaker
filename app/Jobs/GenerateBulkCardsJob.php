<?php

namespace App\Jobs;

use App\Models\BulkGeneration;
use App\Models\Template;
use App\Models\User;
use App\Notifications\CardGeneratedNotification;
use App\Services\CardGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use ZipArchive;

class GenerateBulkCardsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public bool $failOnTimeout = true;

    public int $uniqueFor = 3600;

    private const MAX_ROWS = 5000;

    public function __construct(public int $userId, public int $templateId, public string $csvPath, public ?int $generationId = null) {}

    public function uniqueId(): string
    {
        return (string) ($this->generationId ?: $this->userId.'|'.$this->templateId.'|'.$this->csvPath);
    }

    public function handle(CardGeneratorService $generator): void
    {
        $generation = $this->generationId ? BulkGeneration::find($this->generationId) : null;
        $user = User::findOrFail($this->userId);
        $template = Template::findOrFail($this->templateId);
        $zipPath = 'bulk/'.Str::uuid().'.zip';
        $zipTemporaryPath = tempnam(sys_get_temp_dir(), 'cardmaker-zip-');
        $csvTemporaryPath = tempnam(sys_get_temp_dir(), 'cardmaker-csv-');
        if ($zipTemporaryPath === false || $csvTemporaryPath === false) {
            throw new \RuntimeException('Impossible de préparer les fichiers temporaires.');
        }
        $zip = new ZipArchive;
        Storage::disk()->makeDirectory('bulk');
        if (file_put_contents($csvTemporaryPath, Storage::disk()->get($this->csvPath)) === false) {
            @unlink($zipTemporaryPath);
            @unlink($csvTemporaryPath);
            throw new \RuntimeException('Impossible de préparer le fichier CSV.');
        }
        if ($zip->open($zipTemporaryPath, ZipArchive::CREATE) !== true) {
            @unlink($zipTemporaryPath);
            @unlink($csvTemporaryPath);
            throw new \RuntimeException('Impossible de créer l’archive bulk.');
        }
        $handle = fopen($csvTemporaryPath, 'r');
        if ($handle === false) {
            $zip->close();
            @unlink($zipTemporaryPath);
            @unlink($csvTemporaryPath);
            throw new \RuntimeException('Impossible de lire le fichier CSV.');
        }
        $archive = false;
        try {
            $headers = fgetcsv($handle);
            $headers = array_map(fn ($header) => Str::lower(trim((string) $header)), $headers ?: []);
            if (! in_array('nom', $headers, true)) {
                throw new \RuntimeException('Le CSV doit contenir une colonne nom.');
            }
            $generation?->update(['status' => 'processing']);
            $processed = 0;
            while (($row = fgetcsv($handle)) !== false) {
                if ($processed >= self::MAX_ROWS) {
                    throw new \RuntimeException('Le CSV dépasse la limite de 5000 cartes.');
                }
                if (count($row) !== count($headers)) {
                    throw new \RuntimeException('Une ligne CSV contient un nombre de colonnes invalide.');
                }
                $data = array_combine($headers, $row);
                $card = $user->cards()->create(['template_id' => $template->id, 'name' => Str::limit(trim((string) ($data['nom'] ?: 'Carte bulk')), 120, ''), 'data' => $data]);
                $export = $generator->generate($card);
                $zip->addFromString(Str::slug($card->name).'-'.$card->id.'.png', Storage::disk()->get($export->file_path));
                $processed++;
                $generation?->update(['total' => $processed, 'processed' => $processed]);
            }
        } finally {
            fclose($handle);
            $zip->close();
            $archive = file_get_contents($zipTemporaryPath);
            @unlink($zipTemporaryPath);
            @unlink($csvTemporaryPath);
        }
        if ($archive === false) {
            throw new \RuntimeException('Impossible de lire l’archive bulk.');
        }
        Storage::disk()->put($zipPath, $archive);
        Storage::disk()->delete($this->csvPath);
        $generation?->update(['status' => 'completed', 'zip_path' => $zipPath]);
        if ($generation) {
            $user->notify(new CardGeneratedNotification($user->cards()->latest()->first(), 'ZIP'));
        }
    }

    public function failed(Throwable $exception): void
    {
        if ($this->generationId) {
            BulkGeneration::whereKey($this->generationId)->update(['status' => 'failed', 'error' => $exception->getMessage()]);
        }
    }
}
