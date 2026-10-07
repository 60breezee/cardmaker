<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateBulkCardsJob;
use App\Models\BulkGeneration;
use App\Models\Template;
use App\Services\QuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BulkGenerationController extends Controller
{
    public function create(Request $request, QuotaService $quota)
    {
        return view('bulk.create', ['templates' => Template::where('is_active', true)->get()->filter(fn (Template $template) => $quota->canUseTemplate($request->user(), $template)), 'generations' => $request->user()->bulkGenerations()->latest()->take(10)->get()]);
    }

    public function store(Request $request, QuotaService $quota)
    {
        $quota->ensureCanBulkGenerate($request->user());
        $data = $request->validate(['template_id' => ['required', 'exists:templates,id'], 'csv' => ['required', 'file', 'mimes:csv,txt', 'max:10240']]);
        $template = Template::findOrFail($data['template_id']);
        $quota->ensureCanUseTemplate($request->user(), $template);
        $path = $data['csv']->store('bulk');
        $generation = $request->user()->bulkGenerations()->create(['template_id' => $data['template_id'], 'csv_path' => $path]);
        GenerateBulkCardsJob::dispatch($request->user()->id, (int) $data['template_id'], $path, $generation->id);

        return back()->with('success', 'Votre génération bulk a été mise en file d’attente.');
    }

    public function download(Request $request, BulkGeneration $generation)
    {
        abort_unless($generation->user_id === $request->user()->id && $generation->status === 'completed', 404);

        return Storage::disk()->download($generation->zip_path, 'cardmaker-bulk-'.$generation->id.'.zip');
    }
}
