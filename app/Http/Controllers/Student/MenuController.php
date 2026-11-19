<?php

namespace App\Http\Controllers\Student;

use App\Models\Menu;
use App\Models\MenuSchedule;
use App\Models\MenuSelection;
use App\Models\SppgUnit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MenuController extends BaseStudentController
{
    /**
     * Menampilkan daftar pilihan menu untuk siswa.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $student = $this->resolveStudent($user);

        // 1. Ambil data sekolah dan SPPG penyedia makanan
        $school = $this->resolveSchool($student, $user);
        $sppgId = $school?->sppg_id;
        $sppgName = 'SPPG Wilayah Sekolah';
        if ($sppgId) {
            $sppgName = SppgUnit::where('id', $sppgId)->value('name') ?? $sppgName;
        }

        // 2. Tanggal target menu (default: besok / esok hari, atau sesuai tanggal yang dipilih di tab)
        $defaultDate = today()->addDay();
        $targetDate = $request->filled('date') ? Carbon::parse($request->query('date')) : $defaultDate;
        $targetDateFormatted = $targetDate->translatedFormat('l, d F Y');

        // Pastikan jadwal menu pada database menyesuaikan dengan tanggal yang dipilih pada tab
        $schedule = null;
        if ($sppgId) {
            $schedule = MenuSchedule::where('sppg_id', $sppgId)
                ->whereDate('date', $targetDate)
                ->first();
        }

        // Jika belum ada jadwal untuk tanggal yang dipilih di tab, buatkan otomatis melalui Model MenuSchedule
        if (! $schedule) {
            $defaultMenuId = Menu::where('is_active', 1)->value('id') ?? 1;
            $schedule = MenuSchedule::create([
                'menu_id' => $defaultMenuId,
                'sppg_id' => $sppgId ?? 1,
                'date' => $targetDate->toDateString(),
                'cycle' => 'Minggu '.$targetDate->weekOfYear,
                'cutoff_time' => '20:00:00',
            ]);
        }

        // Format waktu batas akhir pemesanan (cut-off)
        $cutoffTime = '20:00 WITA';
        if ($schedule && ! empty($schedule->cutoff_time)) {
            $cutoffTime = Carbon::parse($schedule->cutoff_time)->format('H:i').' WITA';
        }

        // 3. Ambil daftar menu untuk tanggal terkait
        $scheduledMenuIds = [];
        if ($schedule) {
            $scheduledMenuIds = MenuSchedule::where('sppg_id', $schedule->sppg_id)
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
            $selection = $student->selections()
                ->where('schedule_id', $schedule->id)
                ->first();

            $selectedMenuId = $selection?->menu_id;
        }

        // 5. Ambil data profil alergi siswa via relasi Model Student
        $allergies = [];
        if ($student) {
            $allergies = $student->allergies()->pluck('name')->toArray();
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
    public function select(Request $request): JsonResponse
    {
        $request->validate([
            'menu_id' => 'required|integer|exists:menus,id',
            'schedule_id' => 'nullable|integer',
        ]);

        $user = Auth::user();
        $student = $this->resolveStudent($user);

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

        // Pengecekan alergen pada menu terhadap profil alergi siswa via relasi
        $selectedMenu = Menu::with('allergens')->find($menuId);
        $menuAllergenIds = $selectedMenu?->allergens()
            ->wherePivot('type', 'contains')
            ->pluck('allergens.id')
            ->toArray() ?? [];

        $conflictAllergens = [];
        if (! empty($menuAllergenIds)) {
            $conflictAllergens = $student->allergies()
                ->whereIn('allergens.id', $menuAllergenIds)
                ->pluck('allergens.name')
                ->toArray();
        }

        $hasConflict = ! empty($conflictAllergens);
        $consumptionStatus = $hasConflict ? 'medical_review' : 'active';

        // Simpan atau update pilihan menu menggunakan Model MenuSelection
        $student->selections()->updateOrCreate(
            ['schedule_id' => $scheduleId],
            [
                'menu_id' => $menuId,
                'consumption_status' => $consumptionStatus,
                'created_at' => now(),
            ]
        );

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
}
