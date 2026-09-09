<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::create([
            'name_bn' => 'Test Madrasa',
            'name_en' => 'Test Madrasa',
            'slug' => 'test-madrasa',
            'type' => 'madrasa',
            'contact_email' => 'test@test.com',
            'subscription_tier' => 'free',
            'subscription_status' => 'active',
            'modules_enabled' => ['student'],
        ]);
    }

    public function test_create_user_in_tenant(): void
    {
        $admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'Admin User',
            'name_en' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
            'is_platform_admin' => false,
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/users', [
                'tenant_id' => $this->tenant->id,
                'name_bn' => 'New Student',
                'name_en' => 'New Student',
                'email' => 'student@test.com',
                'phone' => '+880****0000',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'user',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'ব্যবহারকারী তৈরি সফল',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'name_bn', 'email', 'role', 'tenant_id'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'student@test.com',
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'New Student',
        ]);
    }

    public function test_non_admin_cannot_create_user(): void
    {
        $regularUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'Regular User',
            'name_en' => 'Regular User',
            'email' => 'regular@test.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_active' => true,
            'is_platform_admin' => false,
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'regular@test.com',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/users', [
                'tenant_id' => $this->tenant->id,
                'name_bn' => 'New User',
                'name_en' => 'New User',
                'email' => 'newuser@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_list_users_in_tenant(): void
    {
        User::create([
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'User One',
            'name_en' => 'User One',
            'email' => 'one@test.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_active' => true,
        ]);

        User::create([
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'User Two',
            'name_en' => 'User Two',
            'email' => 'two@test.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_active' => true,
        ]);

        $admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'Admin User',
            'name_en' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
            'is_platform_admin' => false,
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/users');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'name_bn', 'email', 'role', 'tenant_id'],
                    ],
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ])
            ->assertJsonCount(3, 'data.data');
    }

    public function test_show_user_by_id(): void
    {
        $user = User::create([
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'Test User',
            'name_en' => 'Test User',
            'email' => 'testuser@test.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_active' => true,
        ]);

        $admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'Admin User',
            'name_en' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
            'is_platform_admin' => false,
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/users/' . $user->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name_bn' => 'Test User',
                    'email' => 'testuser@test.com',
                ],
            ]);
    }

    public function test_update_user(): void
    {
        $user = User::create([
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'Original Name',
            'name_en' => 'Original Name',
            'email' => 'original@test.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_active' => true,
        ]);

        $admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'Admin User',
            'name_en' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
            'is_platform_admin' => false,
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/users/' . $user->id, [
                'name_bn' => 'Updated Name',
                'name_en' => 'Updated Name',
                'phone' => '+880****9999',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'ব্যবহারকারী আপডেট সফল',
            ])
            ->assertJson([
                'data' => [
                    'name_bn' => 'Updated Name',
                    'phone' => '+880****9999',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name_bn' => 'Updated Name',
        ]);
    }

    public function test_delete_user(): void
    {
        $userToDelete = User::create([
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'To Delete',
            'name_en' => 'To Delete',
            'email' => 'todelete@test.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_active' => true,
        ]);

        $admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'Super Admin',
            'name_en' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
            'is_platform_admin' => false,
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'super@test.com',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/v1/users/' . $userToDelete->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'ব্যবহারকারী মুছে ফেলা সফল',
            ]);

        $this->assertSoftDeleted('users', [
            'email' => 'todelete@test.com',
        ]);
    }
}
