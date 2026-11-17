<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SPPGDashboardTest extends TestCase
{
    public function test_sppg_dashboard_displays_today_scheduled_menu(): void
    {
        $user = User::where('role', 'sppg')->first();
        if (! $user) {
            $user = User::factory()->create([
                'role' => 'sppg',
                'institution' => 'SPPG Denpasar Barat 01',
            ]);
        }

        $response = $this->actingAs($user)->get(route('sppg.dashboard'));

        $response->assertStatus(200);

        // Cari schedule hari ini
        $todaySchedule = DB::table('menu_schedules')
            ->whereDate('date', today())
            ->first();

        if ($todaySchedule) {
            $menu = Menu::find($todaySchedule->menu_id);
            if ($menu) {
                $response->assertSee($menu->name);
                $response->assertSee($todaySchedule->cycle);
                $response->assertSee((string) $menu->calories);
            }
        }
    }

    public function test_sppg_dashboard_displays_empty_state_when_no_menu_selected(): void
    {
        $user = User::where('role', 'sppg')->first();
        if (! $user) {
            $user = User::factory()->create([
                'role' => 'sppg',
                'institution' => 'SPPG Denpasar Barat 01',
            ]);
        }

        DB::beginTransaction();

        try {
            $todayScheduleIds = DB::table('menu_schedules')->whereDate('date', today())->pluck('id');
            DB::table('menu_selections')->whereIn('schedule_id', $todayScheduleIds)->delete();
            DB::table('menu_schedules')->whereDate('date', today())->delete();

            $response = $this->actingAs($user)->get(route('sppg.dashboard'));

            $response->assertStatus(200);
            $response->assertSee('Belum Memilih Menu');
            $response->assertDontSee('sppg-comp-list');
            $response->assertDontSee('sppg-macro-3');
            $response->assertDontSee('sppg-today-img');
        } finally {
            DB::rollBack();
        }
    }
}
