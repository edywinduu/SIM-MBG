<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\SPPGDashboardController;
use App\Http\Controllers\StudentController;
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
        Route::post('/review/{id}/respond', [SPPGDashboardController::class, 'respondReview'])->name('review.respond');
    });

    // ----------------------------------------------------------------------
    // Dashboard & Fitur Khusus Siswa (Student)
    // ----------------------------------------------------------------------
    Route::middleware(['role:student,siswa'])->prefix('student')->name('student.')->group(function () {
        Route::get('/', function () {
            return redirect()->route('student.dashboard');
        });
        Route::get('/dashboard', [StudentController::class, 'index'])->name('dashboard');
        Route::post('/pickup/claim', [StudentController::class, 'claimPickup'])->name('pickup.claim');
        Route::get('/menu', [StudentController::class, 'menu'])->name('menu');
        Route::post('/menu/select', [StudentController::class, 'selectMenu'])->name('menu.select');
        Route::get('/history', [StudentController::class, 'history'])->name('history');
        Route::post('/review/save', [StudentController::class, 'saveReview'])->name('review.save');
        Route::delete('/review/{id}', [StudentController::class, 'deleteReview'])->name('review.delete');
        Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
        Route::post('/profile/allergies', [StudentController::class, 'updateAllergies'])->name('profile.allergies');
        Route::post('/profile/name', [StudentController::class, 'updateProfileName'])->name('profile.name');
        Route::post('/profile/avatar', [StudentController::class, 'updateAvatar'])->name('profile.avatar');
        Route::get('/review', [StudentController::class, 'review'])->name('review');
    });
});
