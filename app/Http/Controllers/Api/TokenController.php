<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:100'],
        ]);

        $user = User::where('email', $credentials['email'])->first();
        if (! $user || ! $user->is_active || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Identifiants invalides.'], 401);
        }

        return response()->json([
            'token' => $user->createToken($credentials['device_name'])->plainTextToken,
            'user' => $user,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return response()->json(['message' => 'Token révoqué.']);
    }
}
