<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Menampilkan halaman dashboard siswa.
     */
    public function index(): View
    {
        $user = Auth::user();
        $student = $user?->student;

        // 1. Ambil sekolah terkait siswa
        $schoolId = $student?->school_id;
        if (! $schoolId && $user?->institution) {
            $schoolId = $this->resolveSchool($student, $user)?->id;
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

        // 6. Data statistik siswa untuk kartu analitik
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

    /**
     * Menampilkan daftar pilihan menu hari ini untuk siswa.
     */
    public function menu(): View
    {
        $user = Auth::user();
        $student = $user?->student;

        // 1. Ambil data sekolah dan SPPG penyedia makanan
        $school = $this->resolveSchool($student, $user);
        $sppgId = $school?->sppg_id;
        $sppgName = 'SPPG Wilayah Sekolah';
        if ($sppgId) {
            $sppgName = DB::table('sppg_units')->where('id', $sppgId)->value('name') ?? $sppgName;
        }

        // 2. Ambil jadwal menu hari ini (dengan fallback ke jadwal terbaru jika hari ini belum dijadwalkan)
        $schedule = $this->resolveActiveSchedule($sppgId);

        // Format waktu batas akhir pemesanan (cut-off) dan tanggal target menu
        $cutoffTime = '20:00 WITA';
        if ($schedule && ! empty($schedule->cutoff_time)) {
            $cutoffTime = Carbon::parse($schedule->cutoff_time)->format('H:i').' WITA';
        }

        $targetDate = $schedule?->date ? Carbon::parse($schedule->date) : today();
        $targetDateFormatted = $targetDate->translatedFormat('l, d F Y');

        // 3. Ambil daftar menu hari ini
        $scheduledMenuIds = [];
        if ($schedule) {
            $scheduledMenuIds = DB::table('menu_schedules')
                ->where('sppg_id', $schedule->sppg_id)
                ->whereDate('date', $schedule->date)
                ->pluck('menu_id')
                ->toArray();
        }

        // Ambil menu terjadwal, dan jika kurang dari 3 menu, lengkapi dengan menu aktif lainnya
        if (! empty($scheduledMenuIds)) {
            $menus = Menu::whereIn('id', $scheduledMenuIds)->where('is_active', 1)->get();
            if ($menus->count() < 3) {
                $additionalMenus = Menu::whereNotIn('id', $scheduledMenuIds)
                    ->where('is_active', 1)
                    ->take(3 - $menus->count())
                    ->get();
                $menus = $menus->merge($additionalMenus);
            }
        } else {
            $menus = Menu::where('is_active', 1)->take(3)->get();
        }

        // 4. Ambil menu yang saat ini sudah dipilih siswa
        $selectedMenuId = null;
        if ($student) {
            $selection = DB::table('menu_selections')
                ->where('student_id', $student->id)
                ->when($schedule, fn ($q) => $q->where('schedule_id', $schedule->id))
                ->first();

            // Fallback ke pilihan menu terakhir siswa jika belum ada pilihan di jadwal hari ini
            if (! $selection) {
                $selection = DB::table('menu_selections')
                    ->where('student_id', $student->id)
                    ->latest('created_at')
                    ->first();
            }

            $selectedMenuId = $selection?->menu_id;
        }

        // 5. Ambil data profil alergi siswa
        $allergies = [];
        if ($student) {
            $allergies = DB::table('student_allergies')
                ->join('allergens', 'student_allergies.allergen_id', '=', 'allergens.id')
                ->where('student_allergies.student_id', $student->id)
                ->pluck('allergens.name')
                ->toArray();
        }

        return view('student.menu', compact(
            'student',
            'menus',
            'selectedMenuId',
            'schedule',
            'cutoffTime',
            'sppgName',
            'targetDate',
            'targetDateFormatted',
            'allergies'
        ));
    }

    /**
     * Menyimpan atau memperbarui pilihan paket menu siswa untuk jadwal harian.
     */
    public function selectMenu(Request $request): JsonResponse
    {
        $request->validate([
            'menu_id' => 'required|integer|exists:menus,id',
            'schedule_id' => 'nullable|integer',
        ]);

        $user = Auth::user();
        $student = $user?->student;

        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'Profil siswa tidak ditemukan. Pastikan Anda masuk sebagai akun siswa.',
            ], 404);
        }

        $menuId = (int) $request->input('menu_id');
        $scheduleId = $request->input('schedule_id');

        // Jika schedule_id tidak disertakan, cari jadwal aktif untuk SPPG/sekolah siswa
        if (! $scheduleId) {
            $sppgId = $this->resolveSchool($student, $user)?->sppg_id;
            $scheduleId = $this->resolveActiveSchedule($sppgId)?->id;
        }

        if (! $scheduleId) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal distribusi menu untuk hari ini belum tersedia.',
            ], 422);
        }

        // Pengecekan alergen pada menu terhadap profil alergi siswa
        $conflictAllergens = DB::table('student_allergies')
            ->join('allergens', 'student_allergies.allergen_id', '=', 'allergens.id')
            ->where('student_allergies.student_id', $student->id)
            ->whereIn('student_allergies.allergen_id', function ($query) use ($menuId) {
                $query->select('allergen_id')
                    ->from('menu_allergens')
                    ->where('menu_id', $menuId)
                    ->where('type', 'contains');
            })
            ->pluck('allergens.name')
            ->toArray();

        $hasConflict = ! empty($conflictAllergens);
        $consumptionStatus = $hasConflict ? 'medical_review' : 'active';

        // Simpan atau update pilihan menu di tabel menu_selections
        DB::table('menu_selections')->updateOrInsert(
            [
                'student_id' => $student->id,
                'schedule_id' => $scheduleId,
            ],
            [
                'menu_id' => $menuId,
                'consumption_status' => $consumptionStatus,
                'created_at' => now(),
            ]
        );

        $selectedMenu = Menu::find($menuId);
        $menuName = $selectedMenu?->name ?? 'Menu';

        if ($hasConflict) {
            $allergenList = implode(', ', $conflictAllergens);
            $message = "Pilihan disimpan. Peringatan: Menu {$menuName} mengandung {$allergenList}. Status dialihkan ke tinjauan medis (medical review).";
        } else {
            $message = "Berhasil memilih {$menuName}!";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'has_allergy_warning' => $hasConflict,
            'consumption_status' => $consumptionStatus,
            'menu_id' => $menuId,
            'menu_name' => $menuName,
        ]);
    }

    /**
     * Menampilkan halaman riwayat pengambilan & ulasan siswa.
     */
    public function history(): View
    {
        return view('student.history');
    }

    /**
     * Menampilkan halaman profil siswa.
     */
    public function profile(): View
    {
        return view('student.profile');
    }

    /**
     * Menampilkan halaman ulasan siswa.
     */
    public function review(): View
    {
        return view('student.review');
    }

    /**
     * Mencari data sekolah siswa berdasarkan school_id atau nama institusi user.
     */
    private function resolveSchool(?Student $student, ?User $user): ?object
    {
        if ($student?->school_id) {
            return DB::table('schools')->where('id', $student->school_id)->first();
        }

        if ($user?->institution) {
            return DB::table('schools')->where('name', $user->institution)->first();
        }

        return null;
    }

    /**
     * Mengambil jadwal menu hari ini atau fallback ke jadwal terbaru yang tersedia.
     */
    private function resolveActiveSchedule(?int $sppgId): ?object
    {
        $schedule = null;

        if ($sppgId) {
            $schedule = DB::table('menu_schedules')
                ->where('sppg_id', $sppgId)
                ->whereDate('date', today())
                ->first();

            if (! $schedule) {
                $schedule = DB::table('menu_schedules')
                    ->where('sppg_id', $sppgId)
                    ->latest('date')
                    ->first();
            }
        }

        if (! $schedule) {
            $schedule = DB::table('menu_schedules')->latest('date')->first();
        }

        return $schedule;
    }
}
