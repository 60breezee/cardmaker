<?php

namespace App\Http\Controllers;

use App\Models\User;

class AdminUserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', ['users' => User::latest()->paginate(25)]);
    }

    public function toggle(User $user)
    {
        abort_if($user->is(auth()->user()), 422, 'Impossible de désactiver son propre compte.');
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'Statut utilisateur mis à jour.');
    }
}
