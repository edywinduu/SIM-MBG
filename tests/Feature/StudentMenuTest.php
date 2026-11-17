<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentMenuTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_student_menu_page_defaults_to_tomorrow_in_center_and_today_is_expired(): void
    {
        $user = User::where('role', 'student')->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user)->get(route('student.menu'));

        $response->assertStatus(200);

        $today = today();
        $tomorrow = $today->copy()->addDay();

        $tomorrowFormatted = $tomorrow->translatedFormat('j M Y');
        $todayFormatted = $today->translatedFormat('j M Y');

        // Pastikan tanggal esok hari tampil aktif
        $response->assertSee($tomorrowFormatted);
        $response->assertSee('Besok (Aktif)');

        // Pastikan tanggal hari ini berstatus expired / berakhir
        $response->assertSee($todayFormatted);
        $response->assertSee('Hari Ini (Berakhir)');
    }

    public function test_student_can_select_menu_for_tomorrow(): void
    {
        $this->withoutMiddleware();

        $user = User::where('role', 'student')->first();
        $student = DB::table('students')->where('user_id', $user->id)->first();
        $menu = DB::table('menus')->where('is_active', 1)->first();

        $tomorrow = today()->addDay()->toDateString();
        $schedule = DB::table('menu_schedules')->whereDate('date', $tomorrow)->first();

        if (! $schedule) {
            $scheduleId = DB::table('menu_schedules')->insertGetId([
                'menu_id' => $menu->id,
                'sppg_id' => 1,
                'date' => $tomorrow,
                'cycle' => 'Minggu 1',
                'cutoff_time' => '20:00:00',
            ]);
        } else {
            $scheduleId = $schedule->id;
        }

        $response = $this->actingAs($user)->postJson(route('student.menu.select'), [
            'menu_id' => $menu->id,
            'schedule_id' => $scheduleId,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('menu_selections', [
            'student_id' => $student->id,
            'schedule_id' => $scheduleId,
            'menu_id' => $menu->id,
        ]);
    }
}
