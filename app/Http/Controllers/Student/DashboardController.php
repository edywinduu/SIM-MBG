<?php

namespace App\Http\Controllers\Student;

use App\Models\Distribution;
use App\Models\MenuSchedule;
use App\Models\Pickup;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends BaseStudentController
{
    /**
     * Menampilkan halaman dashboard siswa.
     */
    public function index(): View
    {
        $user = Auth::user();
        $student = $this->resolveStudent($user);
        $school = $this->resolveSchool($student, $user);

        // 1. Ambil jadwal distribusi sekolah hari ini melalui Model Distribution
        $distribution = null;
        if ($school) {
            $distribution = $school->distributions()->today()->first()
                ?? $school->distributions()->latest('date')->first();
        }

        // 2. Format jam distribusi
        $distributionTime = '11:30 - 12:30 WITA';
        if ($distribution && $distribution->eta_start && $distribution->eta_end) {
            $start = Carbon::parse($distribution->eta_start)->format('H:i');
            $end = Carbon::parse($distribution->eta_end)->format('H:i');
            $distributionTime = "{$start} - {$end} WITA";
        }

        // 3. Ambil batas waktu pemilihan menu dari Model MenuSchedule
        $tomorrowDate = today()->addDay()->toDateString();
        $sppgId = $distribution?->sppg_id ?? $school?->sppg_id;

        $tomorrowSchedule = null;
        if ($sppgId) {
            $tomorrowSchedule = MenuSchedule::where('sppg_id', $sppgId)
                ->whereDate('date', $tomorrowDate)
                ->first()
                ?? MenuSchedule::where('sppg_id', $sppgId)->latest('date')->first();
        }

        $cutoffTime = '20:00 WITA';
        if ($tomorrowSchedule && ! empty($tomorrowSchedule->cutoff_time)) {
            $cutoffTime = Carbon::parse($tomorrowSchedule->cutoff_time)->format('H:i').' WITA';
        }

        // 4. Ambil menu yang dipilih siswa untuk hari ini melalui relasi Eloquent
        $selectedMenu = null;
        if ($student) {
            $selectedMenu = $student->selectedMenus()
                ->join('menu_schedules', 'menu_selections.schedule_id', '=', 'menu_schedules.id')
                ->whereDate('menu_schedules.date', today())
                ->select('menus.*')
                ->first()
                ?? $student->selectedMenus()->latest('menu_selections.created_at')->first();
        }

        // 5. Data statistik siswa untuk kartu analitik
        $totalPickups = 18;
        $missedPickups = 0;
        $attendanceRate = 100;
        $reviewCount = 13;
        $pendingReviewCount = 5;
        $reviewPercentage = 72;
        $avgRating = '4.8';
        $ratingPercentage = 96;

        if ($student) {
            $totalPickupsCount = $student->pickups()
                ->where('status', 'picked_up')
                ->count();

            $pendingPickupsCount = $student->pickups()
                ->where('status', 'pending')
                ->where('date', '<', today())
                ->count();

            if ($totalPickupsCount > 0 || $pendingPickupsCount > 0) {
                $totalSchedules = $totalPickupsCount + $pendingPickupsCount;
                $totalPickups = $totalPickupsCount;
                $missedPickups = $pendingPickupsCount;
                $attendanceRate = $totalSchedules > 0 ? (int) round(($totalPickups / $totalSchedules) * 100) : 100;
            }

            $userReviewsCount = $student->reviews()->count();

            if ($userReviewsCount > 0) {
                $reviewCount = $userReviewsCount;
                $pendingReviewCount = max(0, $totalPickups - $reviewCount);
                $reviewPercentage = $totalPickups > 0 ? min(100, (int) round(($reviewCount / $totalPickups) * 100)) : 100;

                $userAvg = $student->reviews()->avg('rating');

                if ($userAvg) {
                    $avgRating = number_format($userAvg, 1);
                    $ratingPercentage = min(100, (int) round(($userAvg / 5) * 100));
                }
            }
        }

        // 6. Status pengambilan makan siang hari ini oleh siswa
        $todayPickup = null;
        if ($student) {
            $todayPickup = $student->pickups()->today()->first();
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
            'ratingPercentage',
            'todayPickup'
        ));
    }

    /**
     * Mencatat klaim pengambilan makan siang hari ini oleh siswa ke tabel pickups.
     */
    public function claimPickup(Request $request): JsonResponse
    {
        $student = $this->resolveStudent(Auth::user());

        if (! $student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profil siswa tidak ditemukan.',
            ], 404);
        }

        /** @var Pickup|null $pickup */
        $pickup = $student->pickups()->today()->first();

        if ($pickup && $pickup->isClaimed()) {
            $timeFormatted = $pickup->pickup_time ? Carbon::parse($pickup->pickup_time)->format('H:i').' WITA' : 'Hari Ini';

            return response()->json([
                'status' => 'info',
                'message' => 'Makan siang sudah diambil sebelumnya pada '.$timeFormatted,
                'pickup_time' => $timeFormatted,
            ]);
        }

        if (! $pickup) {
            $pickup = $student->pickups()->create([
                'date' => today(),
                'status' => 'picked_up',
                'pickup_time' => now(),
            ]);
        } else {
            $pickup->markAsClaimed();
        }

        $timeFormatted = Carbon::parse($pickup->pickup_time)->format('H:i').' WITA';

        return response()->json([
            'status' => 'success',
            'message' => 'Status pengambilan makan siang diperbarui: Sudah Diambil!',
            'pickup_time' => $timeFormatted,
        ]);
    }
}
