<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Template;
use App\Models\User;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index', ['stats' => ['users' => User::count(), 'cards' => Card::count(), 'templates' => Template::count(), 'exports' => Card::withCount('exports')->get()->sum('exports_count')]]);
    }
}
