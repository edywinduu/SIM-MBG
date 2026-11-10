<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = Auth::id();

        // 1. Cari data siswa berdasarkan user_id atau relasi sekolah dari institusi user
        $student = DB::table('students')
            ->where('user_id', $userId)
            ->first();

        $schoolId = $student?->school_id;
        if (! $schoolId && $user?->institution) {
            $schoolId = DB::table('schools')
                ->where('name', $user->institution)
                ->value('id');
        }

        // 2. Ambil jadwal distribusi sekolah hari ini dari tabel 'distributions'
        $distribution = null;
        if ($schoolId) {
            $distribution = DB::table('distributions')
                ->where('school_id', $schoolId)
                ->whereDate('date', today())
                ->first();

            // Fallback ke jadwal distribusi terbaru jika jadwal hari ini belum ada
            if (! $distribution) {
                $distribution = DB::table('distributions')
                    ->where('school_id', $schoolId)
                    ->latest('date')
                    ->first();
            }
        }

        // 3. Format jam distribusi
        $distributionTime = '11:30 - 12:30 WITA';
        if ($distribution && $distribution->eta_start && $distribution->eta_end) {
            $start = Carbon::parse($distribution->eta_start)->format('H:i');
            $end = Carbon::parse($distribution->eta_end)->format('H:i');
            $distributionTime = "{$start} - {$end} WITA";
        }

        // 4. Ambil batas waktu pemilihan menu dari tabel 'menu_schedules'
        $tomorrowDate = today()->addDay()->toDateString();
        $tomorrowSchedule = null;
        if (! empty($distribution?->sppg_id)) {
            $tomorrowSchedule = DB::table('menu_schedules')
                ->where('sppg_id', $distribution->sppg_id)
                ->whereDate('date', $tomorrowDate)
                ->first();
        }

        if (! $tomorrowSchedule) {
            $tomorrowSchedule = DB::table('menu_schedules')
                ->when(! empty($distribution?->sppg_id), fn ($q) => $q->where('sppg_id', $distribution->sppg_id))
                ->latest('date')
                ->first();
        }

        $cutoffTime = '20:00 WITA';
        if ($tomorrowSchedule && ! empty($tomorrowSchedule->cutoff_time)) {
            $cutoffTime = Carbon::parse($tomorrowSchedule->cutoff_time)->format('H:i').' WITA';
        }

        return view('student.dashboard', compact('distribution', 'distributionTime', 'cutoffTime', 'student'));
    }

    public function menu()
    {
        return view('student.menu');
    }

    public function history()
    {
        return view('student.history');
    }

    public function profile()
    {
        return view('student.profile');
    }

    public function review()
    {
        return view('student.review');
    }
}
