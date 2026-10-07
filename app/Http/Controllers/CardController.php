<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCardRequest;
use App\Http\Requests\UpdateCardRequest;
use App\Models\Card;
use App\Models\Template;
use App\Notifications\CardGeneratedNotification;
use App\Services\ActivityLogger;
use App\Services\ExportService;
use App\Services\QuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CardController extends Controller
{
    public function index()
    {
        $cards = auth()->user()->cards()->with('template')->latest()->paginate(12);

        return view('cards.index', compact('cards'));
    }

    public function create(Request $request, QuotaService $quota)
    {
        $templates = Template::where('is_active', true)->get()->filter(fn (Template $template) => $quota->canUseTemplate($request->user(), $template));
        $template = $request->template ? Template::findOrFail($request->template) : $templates->first();

        abort_unless($template && $quota->canUseTemplate($request->user(), $template), 404);

        return view('cards.create', compact('templates', 'template'));
    }

    public function store(StoreCardRequest $request, QuotaService $quota, ActivityLogger $logger)
    {
        $template = Template::findOrFail($request->integer('template_id'));
        $quota->ensureCanUseTemplate($request->user(), $template);
        $quota->ensureCanCreateCard($request->user());
        $card = $request->user()->cards()->create(['template_id' => $request->integer('template_id'), 'name' => $request->string('name'), 'data' => $request->input('data', [])]);
        $logger->record('CARD_CREATED', $card, [], $request);

        return redirect()->route('cards.edit', $card)->with('success', 'Carte enregistrée.');
    }

    public function edit(Card $card)
    {
        $this->authorize('view', $card);
        $templates = Template::where('is_active', true)->get();

        return view('cards.edit', compact('card', 'templates'));
    }

    public function history(Card $card)
    {
        $this->authorize('view', $card);
        $activities = $card->activityLogs()->latest()->paginate(20);

        return view('cards.history', compact('card', 'activities'));
    }

    public function update(UpdateCardRequest $request, Card $card, ActivityLogger $logger)
    {
        $card->update(['name' => $request->string('name'), 'data' => $request->input('data', []), 'is_public' => $request->boolean('is_public')]);
        $logger->record('CARD_UPDATED', $card, [], $request);

        return back()->with('success', 'Carte mise à jour.');
    }

    public function upload(Request $request, Card $card)
    {
        $this->authorize('update', $card);
        $request->validate(['photo' => ['required', 'file', 'image', 'mimes:jpeg,png,webp', 'max:5120', 'dimensions:min_width=100,min_height=100,max_width=6000,max_height=6000']]);
        $data = $card->data;
        if (! empty($data['photo'])) {
            Storage::disk()->delete($data['photo']);
        }
        $data['photo'] = $request->file('photo')->store('uploads/'.$request->user()->id);
        $card->update(['data' => $data]);

        return back()->with('success', 'Photo ajoutée.');
    }

    public function uploadAsset(Request $request, Card $card, string $asset)
    {
        $this->authorize('update', $card);
        abort_unless(in_array($asset, ['photo', 'logo'], true), 404);
        $request->validate([$asset => ['required', 'file', 'image', 'mimes:jpeg,png,webp', 'max:5120', 'dimensions:min_width=50,min_height=50,max_width=6000,max_height=6000']]);
        $data = $card->data;
        if (! empty($data[$asset])) {
            Storage::disk()->delete($data[$asset]);
        }
        $data[$asset] = $request->file($asset)->store('uploads/'.$request->user()->id);
        $card->update(['data' => $data]);

        return back()->with('success', ucfirst($asset).' ajouté.');
    }

    public function duplicate(Request $request, Card $card, QuotaService $quota, ActivityLogger $logger)
    {
        $this->authorize('view', $card);
        $quota->ensureCanCreateCard($request->user());
        $copy = $card->replicate(['public_identifier']);
        $copy->name = $card->name.' (copie)';
        $copy->status = 'draft';
        $copy->save();
        $logger->record('CARD_CREATED', $copy, ['duplicated_from' => $card->id], $request);

        return redirect()->route('cards.edit', $copy);
    }

    public function destroy(Request $request, Card $card, ActivityLogger $logger)
    {
        $this->authorize('delete', $card);
        foreach (['photo', 'logo'] as $asset) {
            if (! empty($card->data[$asset])) {
                Storage::disk()->delete($card->data[$asset]);
            }
        }
        foreach ($card->exports as $export) {
            Storage::disk()->delete($export->file_path);
        }
        $logger->record('CARD_DELETED', $card, [], $request);
        $card->delete();

        return redirect()->route('cards.index')->with('success', 'Carte supprimée.');
    }

    public function generate(Request $request, Card $card, ExportService $exports, QuotaService $quota, ActivityLogger $logger)
    {
        $this->authorize('view', $card);
        $quota->ensureCanExport($request->user());
        $format = strtolower((string) $request->validate(['format' => ['required', 'in:png,jpg,pdf']])['format']);
        $export = $exports->generate($card, $format);
        $logger->record('CARD_EXPORTED', $card, ['format' => $format], $request);
        $card->user->notify(new CardGeneratedNotification($card, strtoupper($format)));

        return redirect()->route('cards.edit', $card)->with('export_format', strtolower((string) $export->format));
    }

    public function download(Card $card, string $format)
    {
        $this->authorize('view', $card);
        abort_unless(in_array(strtolower($format), ['png', 'jpg', 'pdf'], true), 404);
        $export = $card->exports()->where('format', strtoupper($format))->latest()->firstOrFail();

        return Storage::disk()->download($export->file_path, $card->name.'.'.$format);
    }

    public function asset(Card $card, string $asset)
    {
        $this->authorize('view', $card);
        abort_unless(in_array($asset, ['photo', 'logo'], true), 404);

        $path = $card->data[$asset] ?? null;
        abort_unless($path && Storage::disk()->exists($path), 404);

        return response(Storage::disk()->get($path), 200, [
            'Content-Type' => Storage::disk()->mimeType($path),
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    public function verify(string $identifier)
    {
        $card = Card::where('public_identifier', $identifier)->where('is_public', true)->with('template')->firstOrFail();

        return view('cards.verify', compact('card'));
    }
}
