<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\MenuSchedule;
use App\Models\School;
use App\Models\Student;
use App\Models\User;

abstract class BaseStudentController extends Controller
{
    /**
     * Mengambil profil model Student dari user yang sedang login.
     */
    protected function resolveStudent(?User $user): ?Student
    {
        if (! $user) {
            return null;
        }

        return $user->student ?? Student::where('user_id', $user->id)->first();
    }

    /**
     * Mencari data sekolah siswa berdasarkan school_id atau nama institusi user.
     */
    protected function resolveSchool(?Student $student, ?User $user): ?School
    {
        if ($student?->school_id) {
            return School::find($student->school_id);
        }

        if ($user?->institution) {
            return School::where('name', $user->institution)->first();
        }

        return null;
    }

    /**
     * Mengambil jadwal menu hari ini atau fallback ke jadwal terbaru yang tersedia.
     */
    protected function resolveActiveSchedule(?int $sppgId): ?MenuSchedule
    {
        $schedule = null;

        if ($sppgId) {
            $schedule = MenuSchedule::where('sppg_id', $sppgId)
                ->whereDate('date', today())
                ->first();

            if (! $schedule) {
                $schedule = MenuSchedule::where('sppg_id', $sppgId)
                    ->latest('date')
                    ->first();
            }
        }

        if (! $schedule) {
            $schedule = MenuSchedule::latest('date')->first();
        }

        return $schedule;
    }
}
