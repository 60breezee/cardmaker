<?php

namespace App\Http\Controllers;

use App\Models\CardExport;
use App\Models\Template;
use App\Services\QuotaService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(QuotaService $quota): View
    {
        $user = auth()->user();
        $cards = $user->cards()->with('template')->latest()->paginate(12);
        $cardsCreated = $user->cards()->count();
        $exportsCount = CardExport::where('user_id', $user->id)->count();
        $cardsRemaining = $quota->cardsRemaining($user);
        $templates = Template::where('is_active', true)
            ->where(fn ($query) => $query->whereNull('created_by')->orWhere('created_by', $user->id))
            ->latest()
            ->get()
            ->filter(fn (Template $template) => $quota->canUseTemplate($user, $template))
            ->take(5);

        return view('dashboard', compact('cards', 'cardsCreated', 'exportsCount', 'cardsRemaining', 'templates'));
    }
}
