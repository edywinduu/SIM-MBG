<?php

namespace App\Http\Controllers\Student;

use App\Models\Allergen;
use App\Models\SppgUnit;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends BaseStudentController
{
    /**
     * Menampilkan halaman profil siswa.
     */
    public function index(): View
    {
        /** @var User|null $user */
        $user = Auth::user();
        $student = $this->resolveStudent($user);
        $school = $this->resolveSchool($student, $user);

        $sppgUnit = $school?->sppgUnit ?? SppgUnit::first();

        $studentAllergies = [];
        $medicalNotes = '';
        if ($student) {
            $allergies = $student->allergies;
            $studentAllergies = $allergies->pluck('name')->toArray();
            $medicalNotes = $allergies->first(fn ($a) => ! empty($a->pivot?->medical_notes))?->pivot?->medical_notes ?? '';
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
        $student = $this->resolveStudent(Auth::user());

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

        // Sinkronisasi alergen menggunakan sync()
        $syncData = [];
        if (! empty($selectedAllergens)) {
            foreach ($selectedAllergens as $allergenName) {
                $allergen = Allergen::firstOrCreate(['name' => $allergenName]);
                $syncData[$allergen->id] = [
                    'medical_notes' => $medicalNotes,
                    'verification_status' => 'active',
                    'verified_by' => null,
                ];
            }
        }

        $student->allergies()->sync($syncData);

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
    public function updateName(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|min:2|max:255',
        ]);

        $user = Auth::user();
        $newName = trim($request->input('name'));

        if ($user) {
            $user->update([
                'full_name' => $newName,
            ]);

            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $student->update([
                    'name' => $newName,
                ]);
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

        $student = $this->resolveStudent(Auth::user());

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

            $student->update([
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
}
