<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\School;
use App\Models\SppgUnit;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        return User::create([
            'username' => 'admin_test',
            'email' => 'admin_test@sim-mbg.test',
            'password_hash' => bcrypt('password123'),
            'full_name' => 'Admin Penguji',
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    public function test_guest_is_redirected_when_accessing_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_user_cannot_access_admin_dashboard(): void
    {
        $studentUser = User::create([
            'username' => 'siswa_user',
            'email' => 'siswa@sim-mbg.test',
            'password_hash' => bcrypt('password123'),
            'full_name' => 'Siswa Penguji',
            'role' => 'student',
            'status' => 'active',
        ]);

        $response = $this->actingAs($studentUser)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_dashboard_with_dynamic_data(): void
    {
        $admin = $this->createAdminUser();

        $sppg = SppgUnit::create([
            'code' => 'SPPG-DPS-01',
            'name' => 'SPPG Denpasar Barat 01',
            'region' => 'Kota Denpasar',
            'address' => 'Jl. Gunung Agung No. 45',
            'capacity' => 3000,
            'contact_person' => 'I Wayan Sudarma',
            'phone' => '081234567891',
            'status' => 'active',
        ]);

        $school = School::create([
            'npsn' => '50103120',
            'name' => 'SMP Negeri 1 Denpasar',
            'education_level' => 'SMP',
            'district' => 'Denpasar Barat',
            'city' => 'Kota Denpasar',
            'address' => 'Jl. Surapati No. 2',
            'pic_name' => 'I Nyoman Sukra',
            'sppg_id' => $sppg->id,
            'status' => 'active',
        ]);

        Student::create([
            'user_id' => $admin->id,
            'nisn' => '0081234567',
            'name' => 'I Putu Test',
            'school_id' => $school->id,
            'class_name' => 'VII-A',
            'recipient_status' => 'active',
        ]);

        $log = ActivityLog::create([
            'sppg_id' => $sppg->id,
            'title' => 'Serah terima batch selesai',
            'description' => '240 porsi diserahterimakan.',
            'status' => 'Selesai',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertViewHas('kpi');
        $response->assertViewHas('schools');
        $response->assertViewHas('activityLogs');

        // Check if data is rendered in the view
        $response->assertSee('Dashboard Administrator');
        $response->assertSee('SMP Negeri 1 Denpasar');
        $response->assertSee('50103120');
        $response->assertSee('Serah terima batch selesai');
        $response->assertSee('240 porsi diserahterimakan.');
    }
}
