<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        // 1. Ambil data siswa melalui relasi Eloquent dan cari sekolah terkait
        $student = $user?->student;

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

        // 5. Ambil menu yang dipilih siswa untuk hari ini melalui relasi Eloquent
        $selectedMenu = null;
        if ($student) {
            $selectedMenu = $student->selectedMenus()
                ->join('menu_schedules', 'menu_selections.schedule_id', '=', 'menu_schedules.id')
                ->whereDate('menu_schedules.date', today())
                ->select('menus.*')
                ->first();

            // Fallback ke pilihan menu terakhir jika belum ada pilihan untuk hari ini
            if (! $selectedMenu) {
                $selectedMenu = $student->selectedMenus()
                    ->latest('menu_selections.created_at')
                    ->first();
            }
        }

        return view('student.dashboard', compact(
            'distribution',
            'distributionTime',
            'cutoffTime',
            'student',
            'selectedMenu'
        ));
    }

    public function menu(): View
    {
        return view('student.menu');
    }

    public function history(): View
    {
        return view('student.history');
    }

    public function profile(): View
    {
        return view('student.profile');
    }

    public function review(): View
    {
        return view('student.review');
    }
}
