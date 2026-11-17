<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentHistoryTest extends TestCase
{
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

    public function test_student_history_page_renders_with_database_data(): void
    {
        $user = $this->getStudentUser();

        $response = $this->actingAs($user)->get(route('student.history'));

        $response->assertStatus(200);
        $response->assertSee('Riwayat & Ulasan Menu', false);
        $response->assertSee('STUDENT_HISTORY_DATA');
    }

    public function test_student_can_save_and_update_review(): void
    {
        $this->withoutMiddleware();

        $user = $this->getStudentUser();
        $student = Student::where('user_id', $user->id)->first();
        $menu = Menu::first();
        $this->assertNotNull($menu);

        // 1. Simpan ulasan baru
        $response = $this->actingAs($user)->postJson(route('student.review.save'), [
            'menu_id' => $menu->id,
            'rating' => 5,
            'comment' => 'Menu ayamnya sangat lezat dan bumbunya pas!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $reviewId = $response->json('review_id');
        $this->assertNotNull($reviewId);

        $this->assertDatabaseHas('student_reviews', [
            'id' => $reviewId,
            'student_id' => $student->id,
            'rating' => 5,
            'comment' => 'Menu ayamnya sangat lezat dan bumbunya pas!',
        ]);

        // 2. Update ulasan yang sudah ada
        $updateResponse = $this->actingAs($user)->postJson(route('student.review.save'), [
            'review_id' => $reviewId,
            'rating' => 4,
            'comment' => 'Diedit: porsi cukup banyak dan mengenyangkan.',
        ]);

        $updateResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('student_reviews', [
            'id' => $reviewId,
            'rating' => 4,
            'comment' => 'Diedit: porsi cukup banyak dan mengenyangkan.',
        ]);
    }

    public function test_student_can_delete_review(): void
    {
        $this->withoutMiddleware();

        $user = $this->getStudentUser();
        $student = Student::where('user_id', $user->id)->first();
        $menu = Menu::first();

        $reviewId = DB::table('student_reviews')->insertGetId([
            'student_id' => $student->id,
            'menu_id' => $menu->id,
            'rating' => 3,
            'comment' => 'Untuk dihapus',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->deleteJson(route('student.review.delete', $reviewId));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseMissing('student_reviews', [
            'id' => $reviewId,
        ]);
    }
}
