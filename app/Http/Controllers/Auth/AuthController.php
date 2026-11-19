<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $rememberedUser = $request->cookie('remembered_username', '');

        return view('auth.login', compact('rememberedUser'));
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
            'remember' => ['nullable'],
            'rememberMe' => ['nullable'],
            'remember_me' => ['nullable'],
        ]);

        $remember = $request->boolean('remember')
            || $request->boolean('rememberMe')
            || $request->boolean('remember_me');

        $identifier = $credentials['username'];
        $user = User::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (! $user) {
            $student = Student::where('nisn', $identifier)->first();
            if ($student?->user_id) {
                $user = $student->user ?? User::find($student->user_id);
            }
        }

        $passwordMatches = false;
        if ($user && ! empty($user->password_hash)) {
            if (Hash::check($credentials['password'], $user->password_hash)) {
                $passwordMatches = true;
            } elseif ($credentials['password'] === $user->password_hash) {
                $passwordMatches = true;
                $user->password_hash = Hash::make($credentials['password']);
                $user->save();
            }
        }

        if ($user && $passwordMatches) {
            if ($user->status === 'inactive') {
                if ($request->wantsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Akun tidak aktif.',
                    ], 403);
                }

                return back()->withErrors(['username' => 'Akun tidak aktif.']);
            }

            if ($remember) {
                Auth::login($user, true);
                // Simpan username kredensial ke cookie 30 hari
                Cookie::queue('remembered_username', $identifier, 60 * 24 * 30);
            } else {
                Auth::login($user, false);
                // Hapus recaller cookie & kredensial tersimpan saat remember me dimatikan
                Cookie::queue(Cookie::forget(Auth::getRecallerName()));
                Cookie::queue(Cookie::forget('remembered_username'));

                if (! empty($user->remember_token)) {
                    $user->setRememberToken(null);
                    $user->save();
                }
            }

            $request->session()->regenerate();

            $targetUrl = match (strtolower($user->role ?? '')) {
                'admin' => route('admin.dashboard'),
                'sppg', 'guru', 'petugas' => route('sppg.dashboard'),
                'student', 'siswa' => route('student.dashboard'),
                default => route('dashboard'),
            };

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Login berhasil.',
                    'role' => $user->role,
                    'redirect' => $targetUrl,
                    'redirect_url' => $targetUrl,
                    'remember' => $remember,
                    'data' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'full_name' => $user->full_name,
                        'role' => $user->role,
                        'institution' => $user->institution,
                    ],
                ]);
            }

            return redirect()->intended($targetUrl);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username atau password salah.',
            ], 401);
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->setRememberToken(null);
            $user->save();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Cookie::queue(Cookie::forget(Auth::getRecallerName()));

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Logout berhasil.',
            ]);
        }

        return redirect()->route('login');
    }
}
