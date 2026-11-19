<?php

namespace App\Http\Controllers\SPPG;

use App\Models\MenuSchedule;
use Illuminate\View\View;

class DashboardController extends BaseSppgController
{
    public function index(): View
    {
        $sppgId = $this->getSppgId();
        $today = today();

        $todaySchedule = MenuSchedule::with('menu')
            ->where('sppg_id', $sppgId)
            ->whereDate('date', $today)
            ->first();

        if (! $todaySchedule) {
            $todaySchedule = MenuSchedule::with('menu')
                ->whereDate('date', $today)
                ->first();
        }

        $todayMenu = $todaySchedule?->menu;

        return view('sppg.dashboard', compact('todaySchedule', 'todayMenu'));
    }
}
