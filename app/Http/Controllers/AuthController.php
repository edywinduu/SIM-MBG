<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $identifier = $credentials['username'];
        $user = User::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        $passwordMatches = false;
        if ($user && !empty($user->password_hash)) {
            if (Hash::check($credentials['password'], $user->password_hash)) {
                $passwordMatches = true;
            } elseif ($credentials['password'] === $user->password_hash) {
                $passwordMatches = true;
            }
        }

        if ($user && $passwordMatches) {
            if ($user->status === 'inactive') {
                if ($request->wantsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Akun tidak aktif.'
                    ], 403);
                }
                return back()->withErrors(['username' => 'Akun tidak aktif.']);
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Login berhasil.',
                    'role' => $user->role,
                    'redirect' => route('dashboard')
                ]);
            }

            return redirect()->intended(route('dashboard'));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username atau password salah.'
            ], 401);
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Logout berhasil.'
            ]);
        }

        return redirect()->route('login');
    }
}
