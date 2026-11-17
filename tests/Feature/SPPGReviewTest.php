<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SPPGReviewTest extends TestCase
{
    public function test_sppg_review_page_renders_with_database_data(): void
    {
        $user = User::where('role', 'sppg')->first();
        if (! $user) {
            $user = User::factory()->create([
                'role' => 'sppg',
                'institution' => 'SPPG Denpasar Barat 01',
            ]);
        }

        $response = $this->actingAs($user)->get(route('sppg.review'));

        $response->assertStatus(200);
        $response->assertSee('Review & Evaluasi Kepuasan Menu MBG', false);
        $response->assertSee('SPPG_FEEDBACKS');
        $response->assertSee('SPPG_RATINGS');
        $response->assertSee('SPPG_FAVORITES');
    }

    public function test_sppg_can_respond_to_school_feedback(): void
    {
        $this->withoutMiddleware();

        $user = User::where('role', 'sppg')->first();
        if (! $user) {
            $user = User::factory()->create([
                'role' => 'sppg',
                'institution' => 'SPPG Denpasar Barat 01',
            ]);
        }

        $feedback = DB::table('school_feedbacks')->first();
        $this->assertNotNull($feedback);

        $response = $this->actingAs($user)->postJson(route('sppg.review.respond', $feedback->id), [
            'response' => 'Tanggapan telah diteruskan ke tim pengolah dapur.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('school_feedbacks', [
            'id' => $feedback->id,
            'kitchen_response' => 'Tanggapan telah diteruskan ke tim pengolah dapur.',
        ]);
    }
}
