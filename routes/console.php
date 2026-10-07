<?php

use App\Models\BulkGeneration;
use App\Models\CardExport;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('cardmaker:cleanup', function () {
    CardExport::where('created_at', '<', now()->subDays(30))->chunkById(100, function ($exports) {
        foreach ($exports as $export) {
            Storage::disk()->delete($export->file_path);
        }
        CardExport::whereKey($exports->modelKeys())->delete();
    });
    BulkGeneration::where('created_at', '<', now()->subDays(7))->chunkById(100, function ($generations) {
        foreach ($generations as $generation) {
            Storage::disk()->delete(array_filter([$generation->csv_path, $generation->zip_path]));
        }
        BulkGeneration::whereKey($generations->modelKeys())->delete();
    });
    $this->info('CardMaker temporary files cleaned.');
})->purpose('Clean expired CardMaker exports and bulk files');

Schedule::command('cardmaker:cleanup')->daily();
