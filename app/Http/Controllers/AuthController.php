<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Mencari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Membuat token menggunakan Auth (session-based)
        Auth::login($user);

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ],
            'token' => $user->createToken('API Token')->plainTextToken
        ]);
    }
}
