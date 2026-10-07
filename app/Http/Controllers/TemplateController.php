<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Services\QuotaService;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index(Request $request, QuotaService $quota)
    {
        $templates = Template::where('is_active', true)
            ->when(! $quota->canUsePremiumTemplates($request->user()), fn ($query) => $query->where('is_premium', false))
            ->when($request->search, fn ($q, $v) => $q->where(fn ($q) => $q->where('name', 'like', "%$v%")->orWhere('category', 'like', "%$v%")))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('templates.index', compact('templates'));
    }
}
