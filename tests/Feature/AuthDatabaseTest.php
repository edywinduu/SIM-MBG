<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthDatabaseTest extends TestCase
{
    use DatabaseTransactions;

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
            'password' => '123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'role' => 'student',
            ]);
    }
}
