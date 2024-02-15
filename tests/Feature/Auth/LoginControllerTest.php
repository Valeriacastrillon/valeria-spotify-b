<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_login_successfully()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson(route('api.auth.do_login'), [
            'email' => 'test@example.com',
            'pwd' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'result',
                    'status',
                    'token',
                ],
            ]);

        $this->assertAuthenticated();
    }

    /** @test */
    public function user_login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson(route('api.auth.do_login'), [
            'email' => 'test@example.com',
            'pwd' => 'incorrect_password',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'data' => [
                    'result',
                    'error_id',
                    'error_msg',
                ],
            ]);

        $this->assertGuest();
    }
}
