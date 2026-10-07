<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminTemplateController extends Controller
{
    public function index()
    {
        return view('admin.templates.index', ['templates' => Template::whereNull('created_by')->latest()->paginate(20)]);
    }

    public function create()
    {
        return view('admin.templates.form', ['template' => new Template]);
    }

    public function store(Request $request)
    {
        $template = $this->validated($request);
        $template['slug'] = Str::slug($template['name']).'-'.Str::lower(Str::random(5));
        Template::create($template);

        return redirect()->route('admin.templates.index')->with('success', 'Template créé.');
    }

    public function edit(Template $template)
    {
        return view('admin.templates.form', compact('template'));
    }

    public function update(Request $request, Template $template)
    {
        $template->update($this->validated($request));

        return redirect()->route('admin.templates.index')->with('success', 'Template mis à jour.');
    }

    public function destroy(Template $template)
    {
        abort_if($template->cards()->exists(), 409, 'Ce template est utilisé par des cartes.');
        $template->delete();

        return back()->with('success', 'Template supprimé.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string', 'max:1000'], 'category' => ['required', 'string', 'max:50'], 'configuration' => ['required', 'json'], 'is_active' => ['sometimes', 'boolean'], 'is_premium' => ['sometimes', 'boolean']]);
        $data['configuration'] = json_decode($data['configuration'], true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($data['configuration']) || ! is_numeric($data['configuration']['width'] ?? null) || ! is_numeric($data['configuration']['height'] ?? null)) {
            throw ValidationException::withMessages(['configuration' => 'La configuration doit contenir une largeur et une hauteur valides.']);
        }
        $width = (int) $data['configuration']['width'];
        $height = (int) $data['configuration']['height'];
        if ($width < 100 || $height < 100 || $width > 5000 || $height > 5000 || ! is_array($data['configuration']['elements'] ?? [])) {
            throw ValidationException::withMessages(['configuration' => 'Les dimensions doivent être comprises entre 100 et 5000, avec une liste d’éléments valide.']);
        }
        $data['is_active'] = $request->boolean('is_active');
        $data['is_premium'] = $request->boolean('is_premium');

        return $data;
    }
}
