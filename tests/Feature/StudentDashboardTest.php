<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentDashboardTest extends TestCase
{
    use DatabaseTransactions;

    private function getStudentUser(): User
    {
        $user = User::where('role', 'student')->first();
        if (! $user) {
            $user = User::factory()->create([
                'role' => 'student',
                'username' => 'siswa_test',
            ]);
        }

        $student = Student::where('user_id', $user->id)->first();
        if (! $student) {
            $student = Student::create([
                'user_id' => $user->id,
                'nisn' => '0081239999',
                'name' => 'Siswa Test',
                'recipient_status' => 'active',
            ]);
        }

        return $user;
    }

    public function test_student_dashboard_renders_with_database_data(): void
    {
        $user = $this->getStudentUser();

        $response = $this->actingAs($user)->get(route('student.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Distribusi Makan Siang');
        $response->assertSee('pickupStatusBtn');
    }

    public function test_student_can_claim_pickup_today(): void
    {
        $this->withoutMiddleware();

        $user = $this->getStudentUser();
        $student = Student::where('user_id', $user->id)->first();

        // Pastikan belum ada pickup hari ini
        DB::table('pickups')
            ->where('student_id', $student->id)
            ->whereDate('date', today())
            ->delete();

        $response = $this->actingAs($user)->postJson(route('student.pickup.claim'));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('pickups', [
            'student_id' => $student->id,
            'date' => today()->toDateString(),
            'status' => 'picked_up',
        ]);
    }
}
