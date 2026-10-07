<?php

namespace App\Http\Controllers;

use App\Models\CardExport;
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

        return view('dashboard', compact('cards', 'cardsCreated', 'exportsCount', 'cardsRemaining'));
    }
}
