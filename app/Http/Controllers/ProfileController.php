<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit');
    }

    public function update(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user())]]);
        $emailChanged = $data['email'] !== $request->user()->email;
        if ($emailChanged) {
            $data['email_verified_at'] = null;
        }
        $request->user()->forceFill($data)->save();
        if ($emailChanged) {
            $request->user()->sendEmailVerificationNotification();
        }

        return back()->with('success', 'Profil mis à jour.');
    }

    public function password(Request $request)
    {
        $data = $request->validate(['current_password' => ['required'], 'password' => ['required', 'confirmed', 'min:8']]);
        abort_unless(Hash::check($data['current_password'], $request->user()->password), 422, 'Mot de passe actuel incorrect.');
        $request->user()->update(['password' => $data['password']]);

        return back()->with('success', 'Mot de passe modifié.');
    }

    public function destroy(Request $request)
    {
        $user = $request->user();
        foreach ($user->cards()->with('exports')->get() as $card) {
            foreach (['photo', 'logo'] as $asset) {
                if (! empty($card->data[$asset])) {
                    Storage::disk()->delete($card->data[$asset]);
                }
            }
            foreach ($card->exports as $export) {
                Storage::disk()->delete($export->file_path);
            }
        }
        foreach ($user->bulkGenerations()->get() as $generation) {
            Storage::disk()->delete(array_filter([$generation->csv_path, $generation->zip_path]));
        }
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();

        return redirect('/');
    }
}
