<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Allergen;
use App\Models\School;
use App\Models\SppgUnit;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Display a listing of students with search, filters, pagination, and KPI recap.
     */
    public function index(Request $request): View|JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $schoolFilter = $request->input('school_id');
        $menuFilter = $request->input('menu_type');

        // Query builder
        $query = Student::with(['school', 'allergies']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('class_name', 'like', "%{$search}%");
            });
        }

        if (! empty($schoolFilter)) {
            $query->where('school_id', $schoolFilter);
        }

        if ($menuFilter === 'Reguler') {
            $query->whereDoesntHave('allergies');
        } elseif ($menuFilter === 'Menu Khusus') {
            $query->whereHas('allergies');
        }

        $students = $query->orderByDesc('id')->paginate(8)->withQueryString();

        // Rekapitulasi KPI Siswa
        $totalStudents = Student::count();
        $specialDietCount = Student::has('allergies')->count();
        $regularDietCount = max(0, $totalStudents - $specialDietCount);

        $regularPercent = $totalStudents > 0 ? round(($regularDietCount / $totalStudents) * 100) : 0;
        $specialPercent = $totalStudents > 0 ? round(($specialDietCount / $totalStudents) * 100) : 0;

        $recap = [
            'total' => $totalStudents,
            'regular' => $regularDietCount,
            'regular_percent' => $regularPercent,
            'special' => $specialDietCount,
            'special_percent' => $specialPercent,
        ];

        // List semua sekolah untuk dropdown filter & form
        $schools = School::orderBy('name')->get(['id', 'name']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'recap' => $recap,
                'students' => $students->items(),
                'pagination' => [
                    'current_page' => $students->currentPage(),
                    'last_page' => $students->lastPage(),
                    'per_page' => $students->perPage(),
                    'total' => $students->total(),
                    'from' => $students->firstItem() ?? 0,
                    'to' => $students->lastItem() ?? 0,
                ],
            ]);
        }

        return view('admin.students', compact('students', 'recap', 'schools', 'search', 'schoolFilter', 'menuFilter'));
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nisn' => 'required|string|max:12|unique:students,nisn',
            'name' => 'required|string|max:100',
            'school_id' => 'required|exists:schools,id',
            'class_name' => 'required|string|max:20',
            'recipient_status' => 'required|string',
            'menu_type' => 'nullable|string',
            'allergies' => 'nullable|string',
        ]);

        $status = in_array(strtolower($validated['recipient_status']), ['active', 'aktif'], true)
            ? 'active'
            : 'inactive';

        $student = DB::transaction(function () use ($validated, $status, $request) {
            $student = Student::create([
                'nisn' => $validated['nisn'],
                'name' => $validated['name'],
                'school_id' => $validated['school_id'],
                'class_name' => $validated['class_name'],
                'recipient_status' => $status,
            ]);

            // Sync allergies if specified
            $this->syncStudentAllergies($student, $request->input('allergies'), $request->input('menu_type'));

            // Activity Log
            $school = School::find($validated['school_id']);
            $sppgId = $school?->sppg_id ?? SppgUnit::value('id') ?? 1;

            ActivityLog::create([
                'sppg_id' => $sppgId,
                'title' => 'Siswa Baru Ditambahkan',
                'description' => "Siswa baru {$student->name} ({$student->class_name}) dari {$school?->name} berhasil didaftarkan.",
                'status' => 'Terverifikasi',
                'created_at' => now(),
            ]);

            return $student;
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Siswa \"{$student->name}\" berhasil ditambahkan!",
                'data' => $student->load(['school', 'allergies']),
            ], 201);
        }

        return redirect()->route('admin.students')->with('success', "Siswa \"{$student->name}\" berhasil ditambahkan!");
    }

    /**
     * Display the specified student for viewing/editing.
     */
    public function show(int $id): JsonResponse
    {
        $student = Student::with(['school', 'allergies'])->findOrFail($id);

        $allergyNames = $student->allergies->pluck('name')->join(', ');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $student->id,
                'nisn' => $student->nisn,
                'name' => $student->name,
                'school_id' => $student->school_id,
                'class_name' => $student->class_name,
                'recipient_status' => $student->recipient_status === 'active' ? 'Aktif' : 'Nonaktif',
                'menu_type' => $student->allergies->isNotEmpty() ? 'Menu Khusus' : 'Reguler',
                'allergies' => $allergyNames,
            ],
        ]);
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'nisn' => 'required|string|max:12|unique:students,nisn,'.$student->id,
            'name' => 'required|string|max:100',
            'school_id' => 'required|exists:schools,id',
            'class_name' => 'required|string|max:20',
            'recipient_status' => 'required|string',
            'menu_type' => 'nullable|string',
            'allergies' => 'nullable|string',
        ]);

        $status = in_array(strtolower($validated['recipient_status']), ['active', 'aktif'], true)
            ? 'active'
            : 'inactive';

        DB::transaction(function () use ($student, $validated, $status, $request) {
            $student->update([
                'nisn' => $validated['nisn'],
                'name' => $validated['name'],
                'school_id' => $validated['school_id'],
                'class_name' => $validated['class_name'],
                'recipient_status' => $status,
            ]);

            $this->syncStudentAllergies($student, $request->input('allergies'), $request->input('menu_type'));

            $school = School::find($validated['school_id']);
            $sppgId = $school?->sppg_id ?? SppgUnit::value('id') ?? 1;

            ActivityLog::create([
                'sppg_id' => $sppgId,
                'title' => 'Data Siswa Diperbarui',
                'description' => "Data siswa {$student->name} ({$student->class_name}) berhasil diperbarui oleh admin.",
                'status' => 'Terverifikasi',
                'created_at' => now(),
            ]);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Data siswa \"{$student->name}\" berhasil diperbarui!",
                'data' => $student->fresh(['school', 'allergies']),
            ]);
        }

        return redirect()->route('admin.students')->with('success', "Data siswa \"{$student->name}\" berhasil diperbarui!");
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $student = Student::with('school')->findOrFail($id);
        $studentName = $student->name;
        $sppgId = $student->school?->sppg_id ?? SppgUnit::value('id') ?? 1;

        DB::transaction(function () use ($student, $studentName, $sppgId) {
            // Detach related allergies
            $student->allergies()->detach();

            // Delete student
            $student->delete();

            ActivityLog::create([
                'sppg_id' => $sppgId,
                'title' => 'Data Siswa Dihapus',
                'description' => "Siswa {$studentName} dihapus dari sistem oleh admin.",
                'status' => 'Pemeliharaan',
                'created_at' => now(),
            ]);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Data siswa \"{$studentName}\" berhasil dihapus!",
            ]);
        }

        return redirect()->route('admin.students')->with('success', "Data siswa \"{$studentName}\" berhasil dihapus!");
    }

    /**
     * Helper to parse and sync student allergies.
     */
    private function syncStudentAllergies(Student $student, ?string $allergyInput, ?string $menuType): void
    {
        // If menu type is strictly regular or allergies input is empty/dash, clear allergies
        $cleaned = trim((string) $allergyInput);
        if ($menuType === 'Reguler' || $cleaned === '' || $cleaned === '-') {
            $student->allergies()->detach();

            return;
        }

        $names = array_filter(array_map('trim', explode(',', $cleaned)));
        $allergenIds = [];

        foreach ($names as $name) {
            if ($name === '') {
                continue;
            }
            $allergen = Allergen::firstOrCreate(['name' => $name]);
            $allergenIds[$allergen->id] = [
                'medical_notes' => 'Catatan pantangan admin',
                'verification_status' => 'active',
            ];
        }

        $student->allergies()->sync($allergenIds);
    }
}
