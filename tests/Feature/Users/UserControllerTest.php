<?php

namespace Tests\Feature\Users;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test listing users.
     */
    public function test_list_users(): void
    {
        // Crear usuarios de prueba
        User::factory()->count(5)->create();

        $response = $this->getJson(route('api.users.list'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
                'message',
                'statusCode',
            ]);
    }

    /**
     * Test creating a user.
     */
    public function test_create_user(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson(route('api.users.store'), $userData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                ],
                'message',
                'statusCode',
            ]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }


    /**
     * Test retrieving a user.
     */
    public function test_show_user(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson(route('api.users.show', ['id' => $user->id]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                ],
                'message',
                'statusCode',
            ]);
    }

    /**
     * Test updating a user.
     */
    public function test_update_user(): void
    {
        $user = User::factory()->create();
        $updatedUserData = [
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com',
        ];

        $response = $this->putJson(route('api.users.update', ['id' => $user->id]), $updatedUserData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    // ... otras propiedades del usuario
                ],
                'message',
                'statusCode',
            ]);

        // Asegurarse de que los datos del usuario se actualizaron en la base de datos
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com',
        ]);
    }

    /**
     * Test deleting a user.
     */
    public function test_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson(route('api.users.destroy', ['id' => $user->id]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'statusCode',
            ]);

        // Asegurarse de que el usuario realmente se eliminó de la base de datos
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
