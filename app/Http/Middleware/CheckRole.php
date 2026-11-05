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
            return redirect()->route('login');
        }

        $userRole = strtolower(auth()->user()->role ?? '');
        if ($userRole === 'siswa') {
            $userRole = 'student';
        }
        if ($userRole === 'guru' || $userRole === 'petugas' || $userRole === 'petugas_sppg') {
            $userRole = 'sppg';
        }

        foreach ($roles as $role) {
            $check = strtolower($role);
            if ($check === 'siswa') {
                $check = 'student';
            }
            if ($check === 'guru' || $check === 'petugas' || $check === 'petugas_sppg') {
                $check = 'sppg';
            }

            if ($userRole === $check) {
                return $next($request);
            }
        }

        abort(403, 'Akses ditolak: Anda tidak memiliki izin untuk halaman ini.');
    }
}
