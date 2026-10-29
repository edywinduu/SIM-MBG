<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Gerbang Tunggal Redirect berdasarkan role pengguna
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $rawRole = strtolower(Auth::user()->role ?? 'student');
        $role = ($rawRole === 'siswa') ? 'student' : $rawRole;

        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'sppg' => redirect()->route('sppg.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            default => redirect('/'),
        };
    }
}
