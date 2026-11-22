<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect()->route('login');
        }

        $user = $request->user() ?? auth()->user();

        if ($user && isset($user->status) && strtolower($user->status) === 'inactive') {
            auth()->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akun tidak aktif.',
                ], 403);
            }

            return redirect()->route('login')->withErrors([
                'username' => 'Akun Anda dinonaktifkan. Silakan hubungi admin.',
            ]);
        }

        if (empty($roles)) {
            return $next($request);
        }

        $userRole = $this->normalizeRole($user?->role ?? '');

        $allowedRoles = [];
        foreach ($roles as $roleGroup) {
            $parts = preg_split('/[,|]/', $roleGroup);
            if ($parts === false) {
                continue;
            }

            foreach ($parts as $part) {
                $trimmed = trim($part);
                if ($trimmed !== '') {
                    $allowedRoles[] = $this->normalizeRole($trimmed);
                }
            }
        }

        if (in_array($userRole, $allowedRoles, true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak: Anda tidak memiliki izin untuk halaman ini.',
            ], 403);
        }

        abort(403, 'Akses ditolak: Anda tidak memiliki izin untuk halaman ini.');
    }

    /**
     * Normalisasi nama role dan aliasnya ke format standar sistem.
     */
    private function normalizeRole(string $role): string
    {
        $normalized = strtolower(trim($role));

        return match ($normalized) {
            'siswa' => 'student',
            'guru', 'petugas', 'petugas_sppg' => 'sppg',
            default => $normalized,
        };
    }
}
