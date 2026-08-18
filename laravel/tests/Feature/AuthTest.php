<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_returns_201_with_token(): void
    {
        $tenant = Tenant::create([
            'name_bn' => 'Test Madrasa',
            'name_en' => 'Test Madrasa',
            'slug' => 'test-madrasa',
            'type' => 'madrasa',
            'contact_email' => 'test@test.com',
            'subscription_tier' => 'free',
            'subscription_status' => 'active',
            'modules_enabled' => ['student'],
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name_bn' => 'Test User',
            'name_en' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+880****0000',
            'tenant_id' => $tenant->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'নিবন্ধন সফল',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user', 'token'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name_bn' => 'Test User',
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_login_with_wrong_password_returns_401(): void
    {
        User::create([
            'name_bn' => 'Test User',
            'name_en' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('correctpassword'),
            'role' => 'user',
            'is_active' => true,
            'is_platform_admin' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'ভুল ইমেইল বা পাসওয়ার্ড',
            ]);
    }

    public function test_login_with_correct_password_returns_token(): void
    {
        User::create([
            'name_bn' => 'Test User',
            'name_en' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('correctpassword'),
            'role' => 'user',
            'is_active' => true,
            'is_platform_admin' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'correctpassword',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'লগইন সফল',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user', 'token'],
            ]);
    }

    public function test_logout_invalidates_token(): void
    {
        $user = User::create([
            'name_bn' => 'Test User',
            'name_en' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_active' => true,
            'is_platform_admin' => false,
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('data.token');

        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'লগআউট সফল',
            ]);
    }

    public function test_user_endpoint_returns_authenticated_user(): void
    {
        $user = User::create([
            'name_bn' => 'Test User',
            'name_en' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_active' => true,
            'is_platform_admin' => false,
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/user');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'name_bn', 'email', 'role', 'tenant_id'],
            ]);
    }

    public function test_register_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name_bn' => '',
            'email' => '',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonStructure([
                'success',
                'errors' => [
                    'name_bn',
                    'email',
                    'password',
                ],
            ]);
    }

    public function test_register_validates_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name_bn' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJson([
                'errors' => [
                    'password' => [
                        'পাসওয়ার্ড এবং পাসওয়ার্ড নিশ্চিতকরণ মিলছে না।',
                    ],
                ],
            ]);
    }

    public function test_login_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonStructure([
                'success',
                'errors' => [
                    'email',
                    'password',
                ],
            ]);
    }

    public function test_user_cannot_login_if_inactive(): void
    {
        $user = User::create([
            'name_bn' => 'Inactive User',
            'name_en' => 'Inactive User',
            'email' => 'inactive@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_active' => false,
            'is_platform_admin' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'অ্যাকাউন্টটি নিষ্ক্রিয় করা হয়েছে',
            ]);
    }
}
