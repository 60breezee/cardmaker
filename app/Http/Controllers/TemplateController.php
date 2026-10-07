<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomTemplateRequest;
use App\Models\Template;
use App\Services\QuotaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TemplateController extends Controller
{
    private const ALLOWED_ELEMENT_KEYS = ['type', 'field', 'x', 'y', 'width', 'height', 'color', 'font_size', 'content', 'z_index'];

    public function index(Request $request, QuotaService $quota): View
    {
        $user = $request->user();
        $customs = Template::where('created_by', $user->id)
            ->where('is_active', true)
            ->latest()
            ->get();

        $templates = Template::where('is_active', true)
            ->whereNull('created_by')
            ->when(! $quota->canUsePremiumTemplates($user), fn ($query) => $query->where('is_premium', false))
            ->when($request->search, fn ($q, $v) => $q->where(fn ($q) => $q->where('name', 'like', "%$v%")->orWhere('category', 'like', "%$v%")))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('templates.index', compact('customs', 'templates'));
    }

    public function builder(Request $request, QuotaService $quota): View
    {
        $quota->ensureCanCreateTemplate($request->user());

        return view('templates.builder');
    }

    public function storeCustom(StoreCustomTemplateRequest $request, QuotaService $quota): RedirectResponse
    {
        $quota->ensureCanCreateTemplate($request->user());
        $data = $request->validated();

        $user = $request->user();
        $configuration = $data['configuration'];

        $elements = array_map(function (array $element): array {
            return array_intersect_key($element, array_flip(self::ALLOWED_ELEMENT_KEYS));
        }, $configuration['elements'] ?? []);

        $name = $request->string('name')->toString();
        $slug = $this->uniqueSlug($name);

        $template = Template::create([
            'name' => $name,
            'slug' => $slug,
            'description' => 'Modèle personnalisé',
            'category' => 'custom',
            'configuration' => [
                'width' => 1050,
                'height' => 600,
                'background' => is_string($configuration['background'] ?? null) ? $configuration['background'] : '#0b1220',
                'elements' => $elements,
            ],
            'is_active' => true,
            'is_premium' => false,
            'created_by' => $user->id,
        ]);

        return redirect()->route('cards.create', ['template' => $template->id])->with('success', 'Modèle « '.$template->name.' » créé.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name, '-') ?: 'modele';
        $slug = $base;
        while (Template::where('slug', $slug)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(4));
        }

        return $slug;
    }
}
