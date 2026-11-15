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

        // 6. Data statistik siswa untuk kartu analitik (Opsi A)
        $totalPickups = 18;
        $missedPickups = 0;
        $attendanceRate = 100;
        $reviewCount = 13;
        $pendingReviewCount = 5;
        $reviewPercentage = 72;
        $avgRating = '4.8';
        $ratingPercentage = 96;

        if ($student) {
            $totalPickupsCount = DB::table('pickups')
                ->where('student_id', $student->id)
                ->where('status', 'picked_up')
                ->count();

            $pendingPickupsCount = DB::table('pickups')
                ->where('student_id', $student->id)
                ->where('status', 'pending')
                ->where('date', '<', today())
                ->count();

            if ($totalPickupsCount > 0 || $pendingPickupsCount > 0) {
                $totalSchedules = $totalPickupsCount + $pendingPickupsCount;
                $totalPickups = $totalPickupsCount;
                $missedPickups = $pendingPickupsCount;
                $attendanceRate = $totalSchedules > 0 ? (int) round(($totalPickups / $totalSchedules) * 100) : 100;
            }

            $userReviewsCount = DB::table('student_reviews')
                ->where('student_id', $student->id)
                ->count();

            if ($userReviewsCount > 0) {
                $reviewCount = $userReviewsCount;
                $pendingReviewCount = max(0, $totalPickups - $reviewCount);
                $reviewPercentage = $totalPickups > 0 ? min(100, (int) round(($reviewCount / $totalPickups) * 100)) : 100;

                $userAvg = DB::table('student_reviews')
                    ->where('student_id', $student->id)
                    ->avg('rating');

                if ($userAvg) {
                    $avgRating = number_format($userAvg, 1);
                    $ratingPercentage = min(100, (int) round(($userAvg / 5) * 100));
                }
            }
        }

        return view('student.dashboard', compact(
            'distribution',
            'distributionTime',
            'cutoffTime',
            'student',
            'selectedMenu',
            'totalPickups',
            'missedPickups',
            'attendanceRate',
            'reviewCount',
            'pendingReviewCount',
            'reviewPercentage',
            'avgRating',
            'ratingPercentage'
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
