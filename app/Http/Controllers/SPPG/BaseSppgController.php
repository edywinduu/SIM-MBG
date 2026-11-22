<?php

namespace App\Http\Controllers\SPPG;

use App\Http\Controllers\Controller;
use App\Models\SppgStaff;
use App\Models\SppgUnit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

abstract class BaseSppgController extends Controller
{
    /**
     * Menentukan ID unit SPPG berdasarkan institusi atau relasi sppg_staff pengguna.
     */
    protected function getSppgId(): int
    {
        /** @var User|null $user */
        $user = Auth::user();
        $sppgId = null;

        if ($user && $user->institution) {
            $sppgId = SppgUnit::where('name', $user->institution)->value('id');
        }

        if (! $sppgId && $user) {
            $sppgId = $user->sppgStaff?->sppg_id
                ?? SppgStaff::where('user_id', $user->id)->value('sppg_id');
        }

        return (int) ($sppgId ?: 1);
    }
}
