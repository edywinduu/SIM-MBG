<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SchoolController as AdminSchoolController;
use App\Http\Controllers\Admin\SppgController as AdminSppgController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\SPPG\DashboardController as SppgDashboardController;
use App\Http\Controllers\SPPG\MenuController as SppgMenuController;
use App\Http\Controllers\SPPG\ReviewController as SppgReviewController;
use App\Http\Controllers\SPPG\SelectionController as SppgSelectionController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\HistoryController as StudentHistoryController;
use App\Http\Controllers\Student\MenuController as StudentMenuController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Student\ReviewController as StudentReviewController;
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
        Route::get('/schools', [AdminSchoolController::class, 'index'])->name('schools');
        Route::get('/sppg', [AdminSppgController::class, 'index'])->name('sppg');
        Route::get('/students', [AdminStudentController::class, 'index'])->name('students');
        Route::post('/students', [AdminStudentController::class, 'store'])->name('students.store');
        Route::get('/students/{id}', [AdminStudentController::class, 'show'])->name('students.show');
        Route::put('/students/{id}', [AdminStudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{id}', [AdminStudentController::class, 'destroy'])->name('students.destroy');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users');
    });

    // ----------------------------------------------------------------------
    // Dashboard & Fitur Khusus Petugas SPPG
    // ----------------------------------------------------------------------
    Route::middleware(['role:sppg,guru'])->prefix('sppg')->name('sppg.')->group(function () {
        Route::get('/', function () {
            return redirect()->route('sppg.dashboard');
        });
        Route::get('/dashboard', [SppgDashboardController::class, 'index'])->name('dashboard');
        Route::get('/menu', [SppgMenuController::class, 'index'])->name('menu');
        Route::get('/selections', [SppgSelectionController::class, 'index'])->name('selections');
        Route::get('/pilihan', function () {
            return redirect()->route('sppg.selections');
        });
        Route::get('/review', [SppgReviewController::class, 'index'])->name('review');
        Route::post('/review/{id}/respond', [SppgReviewController::class, 'respond'])->name('review.respond');
    });

    // ----------------------------------------------------------------------
    // Dashboard & Fitur Khusus Siswa (Student)
    // ----------------------------------------------------------------------
    Route::middleware(['role:student,siswa'])->prefix('student')->name('student.')->group(function () {
        Route::get('/', function () {
            return redirect()->route('student.dashboard');
        });
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::post('/pickup/claim', [StudentDashboardController::class, 'claimPickup'])->name('pickup.claim');
        Route::get('/menu', [StudentMenuController::class, 'index'])->name('menu');
        Route::post('/menu/select', [StudentMenuController::class, 'select'])->name('menu.select');
        Route::get('/history', [StudentHistoryController::class, 'index'])->name('history');
        Route::post('/review/save', [StudentReviewController::class, 'save'])->name('review.save');
        Route::delete('/review/{id}', [StudentReviewController::class, 'destroy'])->name('review.delete');
        Route::get('/profile', [StudentProfileController::class, 'index'])->name('profile');
        Route::post('/profile/allergies', [StudentProfileController::class, 'updateAllergies'])->name('profile.allergies');
        Route::post('/profile/name', [StudentProfileController::class, 'updateName'])->name('profile.name');
        Route::post('/profile/avatar', [StudentProfileController::class, 'updateAvatar'])->name('profile.avatar');
        Route::get('/review', [StudentHistoryController::class, 'review'])->name('review');
    });
});
