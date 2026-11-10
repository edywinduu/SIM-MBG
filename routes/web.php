<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\SPPGDashboardController;
use App\Http\Controllers\StudentDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - SIM-MBG
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. RUTE PUBLIK (Dapat diakses tanpa login)
// ==========================================

// Landing Page / Beranda
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Autentikasi (Login & Register)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {

    // Gerbang Tunggal Redirect (Semua role diarahkan ke dashboard masing-masing setelah login)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ----------------------------------------------------------------------
    // Dashboard & Fitur Khusus Admin
    // ----------------------------------------------------------------------
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        });
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/schools', [AdminDashboardController::class, 'schools'])->name('schools');
        Route::get('/sppg', [AdminDashboardController::class, 'sppg'])->name('sppg');
        Route::get('/students', [AdminDashboardController::class, 'students'])->name('students');
        Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
    });

    // ----------------------------------------------------------------------
    // Dashboard & Fitur Khusus Petugas SPPG
    // ----------------------------------------------------------------------
    Route::middleware(['role:sppg,guru'])->prefix('sppg')->name('sppg.')->group(function () {
        Route::get('/', function () {
            return redirect()->route('sppg.dashboard');
        });
        Route::get('/dashboard', [SPPGDashboardController::class, 'index'])->name('dashboard');
        Route::get('/menu', [SPPGDashboardController::class, 'menu'])->name('menu');
        Route::get('/selections', [SPPGDashboardController::class, 'selections'])->name('selections');
        Route::get('/pilihan', function () {
            return redirect()->route('sppg.selections');
        });
        Route::get('/review', [SPPGDashboardController::class, 'review'])->name('review');
    });

    // ----------------------------------------------------------------------
    // Dashboard & Fitur Khusus Siswa (Student)
    // ----------------------------------------------------------------------
    Route::middleware(['role:student,siswa'])->prefix('student')->name('student.')->group(function () {
        Route::get('/', function () {
            return redirect()->route('student.dashboard');
        });
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/menu', [StudentDashboardController::class, 'menu'])->name('menu');
        Route::get('/history', [StudentDashboardController::class, 'history'])->name('history');
        Route::get('/profile', [StudentDashboardController::class, 'profile'])->name('profile');
        Route::get('/review', [StudentDashboardController::class, 'review'])->name('review');
    });
});
