<?php

namespace App\Jobs;

use App\Models\BulkGeneration;
use App\Models\Template;
use App\Models\User;
use App\Notifications\CardGeneratedNotification;
use App\Services\CardGeneratorService;
use App\Services\QuotaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
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

    public function handle(CardGeneratorService $generator, QuotaService $quota): void
    {
        $generation = $this->generationId ? BulkGeneration::find($this->generationId) : null;
        $user = User::findOrFail($this->userId);
        $template = Template::findOrFail($this->templateId);
        $zipPath = 'bulk/'.Str::uuid().'.zip';
        $zipTemporaryPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'cardmaker-bulk-'.Str::uuid().'.zip';
        $csvTemporaryPath = tempnam(sys_get_temp_dir(), 'cardmaker-csv-');
        if ($csvTemporaryPath === false) {
            throw new RuntimeException('Impossible de préparer les fichiers temporaires.');
        }
        $zip = new ZipArchive;
        Storage::disk()->makeDirectory('bulk');
        if (file_put_contents($csvTemporaryPath, Storage::disk()->get($this->csvPath)) === false) {
            @unlink($csvTemporaryPath);
            throw new RuntimeException('Impossible de préparer le fichier CSV.');
        }
        if ($zip->open($zipTemporaryPath, ZipArchive::CREATE) !== true) {
            @unlink($zipTemporaryPath);
            @unlink($csvTemporaryPath);
            throw new RuntimeException('Impossible de créer l’archive bulk.');
        }
        $handle = fopen($csvTemporaryPath, 'r');
        if ($handle === false) {
            $zip->close();
            @unlink($zipTemporaryPath);
            @unlink($csvTemporaryPath);
            throw new RuntimeException('Impossible de lire le fichier CSV.');
        }
        $archive = false;
        $zipOpened = true;
        try {
            $delimiter = $this->detectDelimiter((string) fgets($handle));
            rewind($handle);

            $headers = fgetcsv($handle, 0, $delimiter);
            $headers = array_map(fn ($header) => Str::lower(trim((string) $header)), $headers ?: []);
            if (! in_array('nom', $headers, true)) {
                throw new RuntimeException('Le CSV doit contenir une colonne nom.');
            }

            $rows = [];
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (count($row) !== count($headers)) {
                    throw new RuntimeException('Une ligne CSV contient un nombre de colonnes invalide.');
                }
                if (count($rows) >= self::MAX_ROWS) {
                    throw new RuntimeException('Le CSV dépasse la limite de 5000 cartes.');
                }
                $rows[] = $row;
            }

            $limit = $quota->cardLimit($user);
            if ($limit !== null) {
                $used = $user->cards()
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count();
                $allowed = max(0, $limit - $used);
                if ($allowed === 0) {
                    throw new RuntimeException('Votre quota mensuel de cartes est atteint.');
                }
                if (count($rows) > $allowed) {
                    $rows = array_slice($rows, 0, $allowed);
                }
            }

            $generation?->update(['status' => 'processing', 'total' => count($rows)]);
            $processed = 0;
            $lastCard = null;
            foreach ($rows as $row) {
                $data = $this->mapRow($headers, $row);
                if (empty($data['identifier'])) {
                    $data['identifier'] = 'CM-'.strtoupper(Str::random(6));
                }
                $cardName = trim((string) ($data['full_name'] ?: ($row[array_search('nom', $headers, true)] ?? 'Carte bulk')));
                $card = $user->cards()->create(['template_id' => $template->id, 'name' => Str::limit($cardName !== '' ? $cardName : 'Carte bulk', 120, ''), 'data' => $data]);
                $lastCard = $card;
                $export = $generator->generate($card);
                $zip->addFromString(Str::slug($card->name).'-'.$card->id.'.png', Storage::disk()->get($export->file_path));
                $processed++;
                $generation?->update(['processed' => $processed]);
            }

            $zip->close();
            $zipOpened = false;
            $archive = file_get_contents($zipTemporaryPath);
            if ($archive === false) {
                throw new RuntimeException('Impossible de lire l’archive bulk.');
            }
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
            if ($zipOpened) {
                $zip->close();
            }
            @unlink($zipTemporaryPath);
            @unlink($csvTemporaryPath);
        }
        Storage::disk()->put($zipPath, $archive);
        Storage::disk()->delete($this->csvPath);
        $generation?->update(['status' => 'completed', 'zip_path' => $zipPath]);
        if ($generation && $lastCard) {
            $user->notify(new CardGeneratedNotification($lastCard, 'ZIP'));
        }
    }

    public function failed(Throwable $exception): void
    {
        if ($this->generationId) {
            BulkGeneration::whereKey($this->generationId)->update(['status' => 'failed', 'error' => $exception->getMessage()]);
        }
    }

    private function detectDelimiter(string $line): string
    {
        $semicolons = substr_count($line, ';');
        $commas = substr_count($line, ',');

        return $semicolons >= $commas ? ';' : ',';
    }

    private function mapRow(array $headers, array $row): array
    {
        $columnMap = [
            'nom' => 'full_name',
            'prenom' => 'full_name',
            'fonction' => 'job_title',
            'telephone' => 'phone',
            'tel' => 'phone',
            'entreprise' => 'company',
            'societe' => 'company',
        ];
        $data = [];
        foreach ($headers as $index => $header) {
            $value = trim((string) ($row[$index] ?? ''));
            if ($value === '' || ! isset($columnMap[$header])) {
                if ($value !== '') {
                    $data[$header] = $value;
                }

                continue;
            }
            $key = $columnMap[$header];
            $data[$key] = isset($data[$key]) ? $data[$key].' '.$value : $value;
        }

        return $data;
    }
}
