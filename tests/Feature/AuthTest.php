<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_user()
    {
        $payload = [
            'user_name' => 'Mohamed Salama',
            'email' => 'mohamed@example.com',
            'password' => 'secret123',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'status',
            'user' => [
                'id',
                'user_name',
                'email',
            ],
            'token',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'mohamed@example.com',
            'user_name' => 'Mohamed Salama',
        ]);
    }

    public function test_can_login_user()
    {
        $user = User::create([
            'user_name' => 'Mohamed Salama',
            'email' => 'mohamed@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $payload = [
            'email' => 'mohamed@example.com',
            'password' => 'secret123',
        ];

        $response = $this->postJson('/api/login', $payload);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'user' => [
                'id',
                'user_name',
                'email',
            ],
            'token',
        ]);
    }

    public function test_cannot_login_with_invalid_credentials()
    {
        $user = User::create([
            'user_name' => 'Mohamed Salama',
            'email' => 'mohamed@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $payload = [
            'email' => 'mohamed@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/login', $payload);

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Invalid credentials');
    }

    public function test_admin_can_access_dashboard()
    {
        $admin = User::create([
            'user_name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        \Laravel\Sanctum\Sanctum::actingAs($admin);

        $response = $this->getJson('/api/dashboard/stats');
        $response->assertStatus(200);
    }

    public function test_user_cannot_access_dashboard()
    {
        $user = User::create([
            'user_name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        \Laravel\Sanctum\Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard/stats');
        $response->assertStatus(403);
        $response->assertJsonPath('message', 'غير مصرح لك بالوصول. هذا المسار مخصص للمسؤولين فقط.');
    }
}
