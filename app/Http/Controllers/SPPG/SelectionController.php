<?php

namespace App\Http\Controllers\SPPG;

use App\Models\ActivityLog;
use App\Models\Allergen;
use App\Models\Menu;
use App\Models\MenuSchedule;
use App\Models\MenuSelection;
use App\Models\School;
use App\Models\SppgStaff;
use App\Models\Student;
use App\Models\StudentAllergy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SelectionController extends BaseSppgController
{
    public function index(): View
    {
        $dbStudents = Student::with(['school', 'allergies', 'selections.menu'])
            ->orderBy('id')
            ->get();

        $students = $dbStudents->map(fn (Student $st) => $this->formatStudent($st))->values()->all();

        $totalStudents = count($students);
        $specialCount = count(array_filter($students, fn (array $s) => in_array($s['type'], ['khusus', 'evaluasi'], true) || $s['note'] !== ''));
        $regularCount = max(0, $totalStudents - $specialCount);

        $regularPct = $totalStudents > 0 ? (int) round(($regularCount / $totalStudents) * 100) : 90;
        $specialPct = $totalStudents > 0 ? max(0, 100 - $regularPct) : 10;
        $connectedSchools = School::where('status', 'active')->count();

        $kpis = [
            'total_students' => $totalStudents,
            'regular_count' => $regularCount,
            'regular_pct' => $regularPct,
            'special_count' => $specialCount,
            'special_pct' => $specialPct,
            'connected_schools' => $connectedSchools,
        ];

        $schoolsList = School::orderBy('name')->pluck('name')->all();

        return view('sppg.selections', compact('students', 'kpis', 'schoolsList'));
    }

    /**
     * Menyimpan atau memperbarui data preferensi menu & catatan alergi siswa.
     */
    public function save(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'nisn' => 'required|string|max:20',
            'kelas' => 'required|string|max:20',
            'sekolah' => 'required|string|max:120',
            'type' => 'required|string',
            'status' => 'required|string',
            'note' => 'nullable|string|max:255',
        ]);

        $school = School::where('name', trim($validated['sekolah']))->first()
            ?? School::where('name', 'like', '%'.trim($validated['sekolah']).'%')->first()
            ?? School::first();

        if (! $school) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data sekolah tidak ditemukan di database.',
            ], 422);
        }

        $student = Student::updateOrCreate(
            ['nisn' => trim($validated['nisn'])],
            [
                'name' => trim($validated['nama']),
                'school_id' => $school->id,
                'class_name' => trim($validated['kelas']),
                'recipient_status' => 'active',
            ]
        );

        $isReview = ($validated['status'] === 'review' || $validated['type'] === 'evaluasi');
        $consumptionStatus = $isReview ? 'medical_review' : 'active';

        // Tentukan paket menu sesuai pilihan kategori
        $targetCategory = in_array($validated['type'], ['khusus', 'evaluasi'], true) ? 'special' : 'regular';
        $menuQuery = Menu::where('category', $targetCategory)->where('is_active', true)->orderBy('id');
        if ($validated['type'] === 'reguler-b') {
            $menu = $menuQuery->skip(1)->first() ?? Menu::where('is_active', true)->first();
        } else {
            $menu = $menuQuery->first() ?? Menu::where('is_active', true)->first();
        }

        $sppgId = $school->sppg_id ?: $this->getSppgId();
        $schedule = MenuSchedule::where('sppg_id', $sppgId)
            ->whereDate('date', today())
            ->first()
            ?? MenuSchedule::where('sppg_id', $sppgId)->latest('date')->first();

        if (! $schedule && $menu) {
            $schedule = MenuSchedule::create([
                'menu_id' => $menu->id,
                'sppg_id' => $sppgId,
                'date' => today()->toDateString(),
                'cycle' => 'Siklus Aktif',
                'cutoff_time' => '20:00:00',
            ]);
        }

        if ($schedule && $menu) {
            MenuSelection::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'schedule_id' => $schedule->id,
                ],
                [
                    'menu_id' => $menu->id,
                    'consumption_status' => $consumptionStatus,
                    'created_at' => now(),
                ]
            );
        }

        $note = trim((string) ($validated['note'] ?? ''));
        if ($note !== '') {
            $existingAllergy = StudentAllergy::where('student_id', $student->id)->first();
            if ($existingAllergy) {
                StudentAllergy::where('student_id', $student->id)
                    ->where('allergen_id', $existingAllergy->allergen_id)
                    ->update([
                        'medical_notes' => $note,
                        'verification_status' => $consumptionStatus,
                    ]);
            } else {
                $allergen = $this->resolveAllergenFromNote($note);
                StudentAllergy::updateOrInsert(
                    [
                        'student_id' => $student->id,
                        'allergen_id' => $allergen->id,
                    ],
                    [
                        'medical_notes' => $note,
                        'verification_status' => $consumptionStatus,
                        'verified_by' => $this->resolveStaffId(),
                    ]
                );
            }
        } elseif ($validated['type'] === 'reguler' || $validated['type'] === 'reguler-b') {
            StudentAllergy::where('student_id', $student->id)->delete();
        }

        $freshStudent = $student->fresh(['school', 'allergies', 'selections.menu']);

        return response()->json([
            'status' => 'success',
            'message' => "Preferensi menu {$student->name} berhasil disimpan ke database.",
            'student' => $this->formatStudent($freshStudent),
        ]);
    }

    /**
     * Memverifikasi status review medis alergi/diet siswa menjadi aktif.
     */
    public function verify(string $nisn): JsonResponse
    {
        $student = Student::where('nisn', $nisn)->first();
        if (! $student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        $staffId = $this->resolveStaffId();

        StudentAllergy::where('student_id', $student->id)->update([
            'verification_status' => 'active',
            'verified_by' => $staffId,
        ]);

        MenuSelection::where('student_id', $student->id)->update([
            'consumption_status' => 'active',
        ]);

        ActivityLog::create([
            'sppg_id' => $this->getSppgId(),
            'title' => "Verifikasi diet medis selesai • {$student->name}",
            'description' => "NISN {$student->nisn} telah diverifikasi oleh petugas SPPG",
            'status' => 'Delivered',
            'created_at' => now(),
        ]);

        $freshStudent = $student->fresh(['school', 'allergies', 'selections.menu']);

        return response()->json([
            'status' => 'success',
            'message' => "Preferensi diet {$student->name} berhasil diverifikasi!",
            'student' => $this->formatStudent($freshStudent),
        ]);
    }

    private function resolveStaffId(): ?int
    {
        $user = Auth::user();
        if (! $user) {
            return SppgStaff::value('id');
        }

        return SppgStaff::where('user_id', $user->id)->value('id')
            ?? SppgStaff::value('id');
    }

    private function resolveAllergenFromNote(string $note): Allergen
    {
        $noteLower = strtolower($note);
        $keywords = [
            'kacang' => 'Kacang',
            'udang' => 'Seafood',
            'ikan' => 'Seafood',
            'seafood' => 'Seafood',
            'laut' => 'Seafood',
            'telur' => 'Telur',
            'susu' => 'Laktosa',
            'laktosa' => 'Laktosa',
            'gandum' => 'Gluten',
            'gluten' => 'Gluten',
            'terigu' => 'Gluten',
        ];

        foreach ($keywords as $key => $allergenName) {
            if (str_contains($noteLower, $key)) {
                return Allergen::firstOrCreate(['name' => $allergenName]);
            }
        }

        return Allergen::first() ?? Allergen::create(['name' => 'Alergen Khusus']);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatStudent(Student $student): array
    {
        $latestSelection = $student->selections->sortByDesc('id')->first();
        $menu = $latestSelection?->menu;

        $notes = [];
        $hasMedicalReviewAllergy = false;
        foreach ($student->allergies as $allergy) {
            $medNote = trim((string) ($allergy->pivot?->medical_notes ?? ''));
            $notes[] = $medNote !== '' ? $medNote : 'Alergi '.$allergy->name;
            if ($allergy->pivot?->verification_status === 'medical_review') {
                $hasMedicalReviewAllergy = true;
            }
        }

        $noteString = implode('; ', array_unique(array_filter($notes)));
        $isReview = $hasMedicalReviewAllergy || ($latestSelection?->consumption_status === 'medical_review');

        $type = 'reguler';
        if ($isReview) {
            $type = 'evaluasi';
        } elseif ($student->allergies->isNotEmpty() || $menu?->category === 'special') {
            $type = 'khusus';
        }

        $menuLabel = $menu?->name;
        if (! $menuLabel) {
            $menuLabel = match ($type) {
                'khusus' => 'Diet / Alergi Khusus',
                'evaluasi' => 'Dalam Evaluasi Menu',
                default => 'Paket Reguler A',
            };
        }

        return [
            'id' => $student->id,
            'nama' => $student->name,
            'nisn' => $student->nisn,
            'sekolah' => $student->school?->name ?? 'SMP Negeri 1 Denpasar',
            'wilayah' => $student->school?->city ?? 'Kota Denpasar',
            'kelas' => $student->class_name,
            'type' => $type,
            'menuLabel' => $menuLabel,
            'note' => $noteString,
            'status' => $isReview ? 'review' : 'aktif',
        ];
    }
}
