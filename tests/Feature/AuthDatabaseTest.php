<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthDatabaseTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_user_can_login_with_username_and_password(): void
    {
        $response = $this->postJson('/login', [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'role' => 'admin',
            ]);
    }

    public function test_student_can_login_with_nisn(): void
    {
        $response = $this->postJson('/login', [
            'username' => '0081234567',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'role' => 'student',
            ]);
    }
}
