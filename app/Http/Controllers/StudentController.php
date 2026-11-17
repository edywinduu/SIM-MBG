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

        // 7. Status pengambilan makan siang hari ini oleh siswa
        $todayPickup = null;
        if ($student) {
            $todayPickup = DB::table('pickups')
                ->where('student_id', $student->id)
                ->whereDate('date', today())
                ->first();
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
        $user = Auth::user();
        $student = $user?->student;
        if (! $student && $user) {
            $student = Student::where('user_id', $user->id)->first();
        }

        if (! $student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profil siswa tidak ditemukan.',
            ], 404);
        }

        $now = now();
        $todayStr = today()->toDateString();

        $existing = DB::table('pickups')
            ->where('student_id', $student->id)
            ->whereDate('date', $todayStr)
            ->first();

        if ($existing && $existing->status === 'picked_up') {
            $timeFormatted = $existing->pickup_time ? Carbon::parse($existing->pickup_time)->format('H:i').' WITA' : 'Hari Ini';

            return response()->json([
                'status' => 'info',
                'message' => 'Makan siang sudah diambil sebelumnya pada '.$timeFormatted,
                'pickup_time' => $timeFormatted,
            ]);
        }

        DB::table('pickups')->updateOrInsert(
            [
                'student_id' => $student->id,
                'date' => $todayStr,
            ],
            [
                'status' => 'picked_up',
                'pickup_time' => $now,
            ]
        );

        $timeFormatted = $now->format('H:i').' WITA';

        return response()->json([
            'status' => 'success',
            'message' => 'Status pengambilan makan siang diperbarui: Sudah Diambil!',
            'pickup_time' => $timeFormatted,
        ]);
    }

    /**
     * Menampilkan daftar pilihan menu hari ini untuk siswa.
     */
    public function menu(Request $request): View
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

        // 2. Tanggal target menu (default: besok / esok hari, atau sesuai tanggal yang dipilih di tab)
        $defaultDate = today()->addDay();
        $targetDate = $request->filled('date') ? Carbon::parse($request->query('date')) : $defaultDate;
        $targetDateFormatted = $targetDate->translatedFormat('l, d F Y');

        // Pastikan tabel menu_schedules pada database menyesuaikan dengan tanggal yang dipilih pada tab
        $schedule = null;
        if ($sppgId) {
            $schedule = DB::table('menu_schedules')
                ->where('sppg_id', $sppgId)
                ->whereDate('date', $targetDate)
                ->first();
        }

        // Jika belum ada jadwal di tabel menu_schedules untuk tanggal yang dipilih di tab, buatkan otomatis
        if (! $schedule) {
            $defaultMenuId = DB::table('menus')->where('is_active', 1)->value('id') ?? 1;
            $newScheduleId = DB::table('menu_schedules')->insertGetId([
                'menu_id' => $defaultMenuId,
                'sppg_id' => $sppgId ?? 1,
                'date' => $targetDate->toDateString(),
                'cycle' => 'Minggu '.$targetDate->weekOfYear,
                'cutoff_time' => '20:00:00',
            ]);

            $schedule = DB::table('menu_schedules')->where('id', $newScheduleId)->first();
        }

        // Format waktu batas akhir pemesanan (cut-off)
        $cutoffTime = '20:00 WITA';
        if ($schedule && ! empty($schedule->cutoff_time)) {
            $cutoffTime = Carbon::parse($schedule->cutoff_time)->format('H:i').' WITA';
        }

        // 3. Ambil daftar menu untuk tanggal terkait
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

        // 4. Ambil menu yang sudah dipilih siswa pada jadwal tanggal yang dipilih
        $selectedMenuId = null;
        if ($student && $schedule) {
            $selection = DB::table('menu_selections')
                ->where('student_id', $student->id)
                ->where('schedule_id', $schedule->id)
                ->first();

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
        $user = Auth::user();
        $student = $user?->student;

        if (! $student && $user) {
            $student = Student::where('user_id', $user->id)->first();
        }

        $attendanceRate = '100%';
        $totalReviews = 0;
        $avgScore = '4.8 / 5.0';
        $historyData = [];

        if ($student) {
            // 1. Ringkasan Kehadiran
            $totalPickups = DB::table('pickups')->where('student_id', $student->id)->count();
            $pickedUp = DB::table('pickups')
                ->where('student_id', $student->id)
                ->where('status', 'picked_up')
                ->count();
            if ($totalPickups > 0) {
                $attendanceRate = round(($pickedUp / $totalPickups) * 100).'%';
            }

            // 2. Ringkasan Ulasan
            $totalReviews = DB::table('student_reviews')->where('student_id', $student->id)->count();
            $avgRating = DB::table('student_reviews')->where('student_id', $student->id)->avg('rating');
            if ($avgRating) {
                $avgScore = number_format((float) $avgRating, 1, '.', '').' / 5.0';
            }

            // 3. Data Riwayat Ulasan
            $reviews = DB::table('student_reviews')
                ->join('menus', 'student_reviews.menu_id', '=', 'menus.id')
                ->where('student_reviews.student_id', $student->id)
                ->select(
                    'student_reviews.*',
                    'menus.name as menu_name',
                    'menus.category as menu_category',
                    'menus.calories',
                    'menus.protein',
                    'menus.fiber',
                    'menus.image as menu_image'
                )
                ->orderByDesc('student_reviews.created_at')
                ->get();

            $pickupsByDate = DB::table('pickups')
                ->where('student_id', $student->id)
                ->get()
                ->keyBy(fn ($p) => Carbon::parse($p->date)->toDateString());

            foreach ($reviews as $rev) {
                $dateObj = ! empty($rev->created_at) ? Carbon::parse($rev->created_at) : now();
                $dateStr = $dateObj->toDateString();
                $pickup = $pickupsByDate->get($dateStr);

                $formattedDate = $dateObj->translatedFormat('l, d F Y');
                $pickupTime = $pickup && ! empty($pickup->pickup_time)
                    ? Carbon::parse($pickup->pickup_time)->format('H:i').' WITA'
                    : '12:10 WITA';

                $menuCode = match ($rev->menu_category) {
                    'special' => 'Paket B',
                    'backup' => 'Paket Cadangan',
                    default => 'Paket A',
                };

                $macros = "{$rev->calories} kkal | {$rev->protein}g Protein | ".((float) ($rev->fiber ?? 0)).'g Serat';

                $historyData[] = [
                    'id' => $rev->id,
                    'menu_id' => $rev->menu_id,
                    'date' => $formattedDate,
                    'pickupTime' => $pickupTime,
                    'status' => 'Sudah Diambil',
                    'menuCode' => $menuCode,
                    'menuName' => $rev->menu_name,
                    'rating' => (int) $rev->rating,
                    'review' => $rev->comment ?? '',
                    'macros' => $macros,
                    'image' => $rev->menu_image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80',
                ];
            }

            // Tambahkan juga riwayat menu terpilih yang belum diulas (pending review)
            $reviewedMenuIds = $reviews->pluck('menu_id')->toArray();
            $pendingSelections = DB::table('menu_selections')
                ->join('menus', 'menu_selections.menu_id', '=', 'menus.id')
                ->join('menu_schedules', 'menu_selections.schedule_id', '=', 'menu_schedules.id')
                ->where('menu_selections.student_id', $student->id)
                ->whereNotIn('menu_selections.menu_id', $reviewedMenuIds)
                ->select(
                    'menu_selections.*',
                    'menu_schedules.date as schedule_date',
                    'menus.name as menu_name',
                    'menus.category as menu_category',
                    'menus.calories',
                    'menus.protein',
                    'menus.fiber',
                    'menus.image as menu_image'
                )
                ->orderByDesc('menu_schedules.date')
                ->get();

            foreach ($pendingSelections as $sel) {
                $dateObj = Carbon::parse($sel->schedule_date);
                $formattedDate = $dateObj->translatedFormat('l, d F Y');

                $menuCode = match ($sel->menu_category) {
                    'special' => 'Paket B',
                    'backup' => 'Paket Cadangan',
                    default => 'Paket A',
                };

                $macros = "{$sel->calories} kkal | {$sel->protein}g Protein | ".((float) ($sel->fiber ?? 0)).'g Serat';

                $historyData[] = [
                    'id' => null,
                    'menu_id' => $sel->menu_id,
                    'date' => $formattedDate,
                    'pickupTime' => '12:00 WITA',
                    'status' => 'Sudah Diambil',
                    'menuCode' => $menuCode,
                    'menuName' => $sel->menu_name,
                    'rating' => 0,
                    'review' => 'Belum ada ulasan untuk menu ini. Berikan masukan Anda kepada penyedia dapur SPPG.',
                    'macros' => $macros,
                    'image' => $sel->menu_image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80',
                ];
            }
        }

        return view('student.history', compact('attendanceRate', 'totalReviews', 'avgScore', 'historyData'));
    }

    /**
     * Menyimpan atau memperbarui ulasan makanan siswa.
     */
    public function saveReview(Request $request): JsonResponse
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'review_id' => 'nullable|integer',
            'menu_id' => 'nullable|integer',
        ]);

        $user = Auth::user();
        $student = $user?->student;
        if (! $student && $user) {
            $student = Student::where('user_id', $user->id)->first();
        }

        if (! $student) {
            return response()->json(['status' => 'error', 'message' => 'Data profil siswa tidak ditemukan.'], 403);
        }

        $reviewId = $request->input('review_id');
        $menuId = $request->input('menu_id');
        $rating = (int) $request->input('rating');
        $comment = $request->input('comment', '');

        if ($reviewId) {
            $existing = DB::table('student_reviews')
                ->where('id', $reviewId)
                ->where('student_id', $student->id)
                ->first();

            if ($existing) {
                DB::table('student_reviews')->where('id', $existing->id)->update([
                    'rating' => $rating,
                    'comment' => $comment,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Ulasan berhasil diperbarui!',
                    'review_id' => $existing->id,
                ]);
            }
        }

        if (! $menuId) {
            return response()->json(['status' => 'error', 'message' => 'ID menu tidak valid.'], 422);
        }

        $newId = DB::table('student_reviews')->insertGetId([
            'student_id' => $student->id,
            'menu_id' => $menuId,
            'rating' => $rating,
            'comment' => $comment,
            'created_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ulasan berhasil disimpan!',
            'review_id' => $newId,
        ]);
    }

    /**
     * Menghapus ulasan makanan siswa.
     */
    public function deleteReview(int $id): JsonResponse
    {
        $user = Auth::user();
        $student = $user?->student;
        if (! $student && $user) {
            $student = Student::where('user_id', $user->id)->first();
        }

        if (! $student) {
            return response()->json(['status' => 'error', 'message' => 'Data profil siswa tidak ditemukan.'], 403);
        }

        $deleted = DB::table('student_reviews')
            ->where('id', $id)
            ->where('student_id', $student->id)
            ->delete();

        if ($deleted) {
            return response()->json([
                'status' => 'success',
                'message' => 'Ulasan berhasil dihapus.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Ulasan tidak ditemukan atau Anda tidak memiliki akses.',
        ], 404);
    }

    /**
     * Menampilkan halaman profil siswa.
     */
    public function profile(): View
    {
        $user = Auth::user();
        $student = $user?->student;
        if (! $student && $user) {
            $student = Student::where('user_id', $user->id)->first();
        }

        $school = $this->resolveSchool($student, $user);

        $sppgUnit = null;
        if ($school && ! empty($school->sppg_id)) {
            $sppgUnit = DB::table('sppg_units')->where('id', $school->sppg_id)->first();
        }
        if (! $sppgUnit) {
            $sppgUnit = DB::table('sppg_units')->first();
        }

        $studentAllergies = [];
        $medicalNotes = '';
        if ($student) {
            $allergies = DB::table('student_allergies')
                ->join('allergens', 'student_allergies.allergen_id', '=', 'allergens.id')
                ->where('student_allergies.student_id', $student->id)
                ->select('allergens.name as allergen_name', 'student_allergies.medical_notes')
                ->get();

            $studentAllergies = $allergies->pluck('allergen_name')->toArray();
            $medicalNotes = $allergies->first(fn ($a) => ! empty($a->medical_notes))?->medical_notes ?? '';
        }

        return view('student.profile', compact(
            'user',
            'student',
            'school',
            'sppgUnit',
            'studentAllergies',
            'medicalNotes'
        ));
    }

    /**
     * Memperbarui data preferensi alergi & catatan medis siswa.
     */
    public function updateAllergies(Request $request): JsonResponse
    {
        $user = Auth::user();
        $student = $user?->student;
        if (! $student && $user) {
            $student = Student::where('user_id', $user->id)->first();
        }

        if (! $student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data profil siswa tidak ditemukan.',
            ], 403);
        }

        $request->validate([
            'allergens' => 'nullable|array',
            'allergens.*' => 'string|max:50',
            'medical_notes' => 'nullable|string|max:1000',
        ]);

        $selectedAllergens = $request->input('allergens', []);
        $medicalNotes = $request->input('medical_notes', '');

        // Hapus alergi lama siswa
        DB::table('student_allergies')->where('student_id', $student->id)->delete();

        // Masukkan alergi baru yang dipilih
        if (! empty($selectedAllergens)) {
            $allergenMap = DB::table('allergens')->pluck('id', 'name');

            foreach ($selectedAllergens as $allergenName) {
                $allergenId = $allergenMap[$allergenName] ?? null;
                if (! $allergenId) {
                    $allergenId = DB::table('allergens')->insertGetId([
                        'name' => $allergenName,
                    ]);
                    $allergenMap[$allergenName] = $allergenId;
                }

                DB::table('student_allergies')->insert([
                    'student_id' => $student->id,
                    'allergen_id' => $allergenId,
                    'medical_notes' => $medicalNotes,
                    'verification_status' => 'active',
                    'verified_by' => null,
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data preferensi alergi berhasil diperbarui!',
            'allergies' => $selectedAllergens,
            'medical_notes' => $medicalNotes,
        ]);
    }

    /**
     * Memperbarui nama profil siswa (full_name di users dan name di students).
     */
    public function updateProfileName(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|min:2|max:255',
        ]);

        $user = Auth::user();
        $newName = trim($request->input('name'));

        if ($user) {
            DB::table('users')->where('id', $user->id)->update([
                'full_name' => $newName,
                'updated_at' => now(),
            ]);

            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $student->name = $newName;
                $student->save();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Nama profil berhasil diubah menjadi \"{$newName}\"!",
            'name' => $newName,
        ]);
    }

    /**
     * Mengunggah dan memperbarui foto profil siswa.
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|file|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $user = Auth::user();
        $student = $user?->student;
        if (! $student && $user) {
            $student = Student::where('user_id', $user->id)->first();
        }

        if (! $student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data profil siswa tidak ditemukan.',
            ], 403);
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'avatar_'.$student->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/avatars');

            if (! file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $avatarUrl = asset('uploads/avatars/'.$filename);

            DB::table('students')->where('id', $student->id)->update([
                'profile_picture' => $avatarUrl,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Foto profil berhasil diperbarui!',
                'avatar_url' => $avatarUrl,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Tidak ada berkas gambar yang diunggah.',
        ], 400);
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
