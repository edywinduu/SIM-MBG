<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StudentProfileTest extends TestCase
{
    private function getStudentUser(): User
    {
        $user = User::where('role', 'student')->first();
        if (! $user) {
            $user = User::factory()->create([
                'role' => 'student',
                'username' => 'siswa_profile_test',
                'full_name' => 'Alya Profil Test',
            ]);
        }

        $student = Student::where('user_id', $user->id)->first();
        if (! $student) {
            $student = Student::create([
                'user_id' => $user->id,
                'nisn' => '0081238888',
                'name' => $user->full_name,
                'class_name' => 'XI IPA 1',
                'recipient_status' => 'active',
            ]);
        }

        return $user;
    }

    public function test_student_profile_page_renders_with_database_data(): void
    {
        $user = $this->getStudentUser();
        $student = Student::where('user_id', $user->id)->first();

        $response = $this->actingAs($user)->get(route('student.profile'));

        $response->assertStatus(200);
        $response->assertSee('Alergi & Pantangan Makanan Siswa', false);
        $response->assertSee($student->name);
        $response->assertSee($student->nisn);
    }

    public function test_student_can_update_allergies(): void
    {
        $this->withoutMiddleware();

        $user = $this->getStudentUser();
        $student = Student::where('user_id', $user->id)->first();

        $response = $this->actingAs($user)->postJson(route('student.profile.allergies'), [
            'allergens' => ['Kacang', 'Seafood'],
            'medical_notes' => 'Alergi kacang tanah dan udang laut',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('student_allergies', [
            'student_id' => $student->id,
            'medical_notes' => 'Alergi kacang tanah dan udang laut',
        ]);
    }

    public function test_student_can_update_profile_name(): void
    {
        $this->withoutMiddleware();

        $user = $this->getStudentUser();
        $student = Student::where('user_id', $user->id)->first();

        $response = $this->actingAs($user)->postJson(route('student.profile.name'), [
            'name' => 'Nama Baru Siswa',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'name' => 'Nama Baru Siswa',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'full_name' => 'Nama Baru Siswa',
        ]);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'Nama Baru Siswa',
        ]);
    }

    public function test_student_can_update_avatar(): void
    {
        $this->withoutMiddleware();

        $user = $this->getStudentUser();
        $student = Student::where('user_id', $user->id)->first();

        $file = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->post(route('student.profile.avatar'), [
            'avatar' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
        ]);
    }
}
