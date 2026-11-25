<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\School;
use App\Models\SppgUnit;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Admin Dashboard with summary KPI, recent schools, and activity logs.
     */
    public function index(): View
    {
        $currentUser = Auth::user();

        // 1. KPI Statistics
        $totalStudents = Student::count();
        $totalSchools = School::count();
        $totalSppg = SppgUnit::count();
        $totalStaff = User::whereIn('role', ['admin', 'sppg', 'guru', 'petugas', 'petugas_sppg'])->count();

        $kpi = [
            'total_students' => $totalStudents,
            'total_schools' => $totalSchools,
            'total_sppg' => $totalSppg,
            'total_staff' => $totalStaff,
        ];

        // 2. Data Ringkas Sekolah Binaan (Eager loaded with SPPG and student counts)
        $schools = School::with('sppgUnit')
            ->withCount('students')
            ->orderBy('id', 'asc')
            ->take(6)
            ->get();

        // 3. Aktivitas Sistem Terbaru (Live Feed)
        $activityLogs = ActivityLog::with('sppgUnit')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(6)
            ->get();

        return view('admin.dashboard', [
            'currentUser' => $currentUser,
            'kpi' => $kpi,
            'schools' => $schools,
            'activityLogs' => $activityLogs,
        ]);
    }
}
