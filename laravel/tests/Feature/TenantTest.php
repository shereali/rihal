<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

class TenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_tenant(): void
    {
        $user = User::create([
            'name_bn' => 'Super Admin',
            'name_en' => 'Super Admin',
            'email' => 'super@example.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
            'is_platform_admin' => true,
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'super@example.com',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/tenants', [
                'name_bn' => 'Test Madrasa',
                'name_en' => 'Test Madrasa',
                'type' => 'madrasa',
                'registration_number' => 'MDR-001',
                'established_year' => 2020,
                'address_bn' => 'Dhaka, Bangladesh',
                'city' => 'Dhaka',
                'district' => 'Dhaka',
                'contact_email' => 'info@demo.bd',
                'contact_phone' => '+880****0000',
                'principal_name' => 'Principal Name',
                'principal_email' => 'principal@demo.bd',
                'subscription_tier' => 'free',
                'modules_enabled' => ['student', 'academic', 'attendance', 'notice'],
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'টেন্যান্ট তৈরি সফল',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'name_bn', 'slug', 'type', 'subscription_tier', 'created_at'],
            ]);

        $this->assertDatabaseHas('tenants', [
            'name_bn' => 'Test Madrasa',
            'type' => 'madrasa',
            'subscription_tier' => 'free',
        ]);
    }

    public function test_non_super_admin_cannot_create_tenant(): void
    {
        $tenant = Tenant::create([
            'name_bn' => 'Existing Madrasa',
            'name_en' => 'Existing Madrasa',
            'slug' => 'existing-madrasa',
            'type' => 'madrasa',
            'contact_email' => 'existing@example.com',
            'subscription_tier' => 'free',
            'subscription_status' => 'active',
            'modules_enabled' => ['student'],
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name_bn' => 'Tenant Admin',
            'name_en' => 'Tenant Admin',
            'email' => 'admin@tenant.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
            'is_platform_admin' => false,
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@tenant.com',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/tenants', [
                'name_bn' => 'New Madrasa',
                'name_en' => 'New Madrasa',
                'type' => 'madrasa',
                'subscription_tier' => 'free',
                'modules_enabled' => ['student'],
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_super_admin_can_list_tenants(): void
    {
        Tenant::create([
            'name_bn' => 'Madrasa One',
            'name_en' => 'Madrasa One',
            'slug' => 'madrasa-one',
            'type' => 'madrasa',
            'contact_email' => 'one@example.com',
            'subscription_tier' => 'free',
            'subscription_status' => 'active',
            'modules_enabled' => ['student'],
        ]);

        Tenant::create([
            'name_bn' => 'Madrasa Two',
            'name_en' => 'Madrasa Two',
            'slug' => 'madrasa-two',
            'type' => 'school',
            'contact_email' => 'two@example.com',
            'subscription_tier' => 'premium',
            'subscription_status' => 'active',
            'modules_enabled' => ['student', 'academic'],
        ]);

        $user = User::create([
            'name_bn' => 'Super Admin',
            'name_en' => 'Super Admin',
            'email' => 'super@example.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
            'is_platform_admin' => true,
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'super@example.com',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/tenants');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'name_bn', 'slug', 'type', 'subscription_tier'],
                    ],
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ])
            ->assertJsonCount(2, 'data.data');
    }

    public function test_show_tenant_by_slug(): void
    {
        $tenant = Tenant::create([
            'name_bn' => 'Test Madrasa',
            'name_en' => 'Test Madrasa',
            'slug' => 'test-madrasa',
            'type' => 'madrasa',
            'contact_email' => 'test@example.com',
            'subscription_tier' => 'free',
            'subscription_status' => 'active',
            'modules_enabled' => ['student'],
        ]);

        $user = User::create([
            'name_bn' => 'Super Admin',
            'name_en' => 'Super Admin',
            'email' => 'super@example.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
            'is_platform_admin' => true,
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'super@example.com',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/tenants/test-madrasa');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $tenant->id,
                    'name_bn' => 'Test Madrasa',
                    'slug' => 'test-madrasa',
                ],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_tenant(): void
    {
        $user = User::create([
            'name_bn' => 'Super Admin',
            'name_en' => 'Super Admin',
            'email' => 'super@example.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
            'is_platform_admin' => true,
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'super@example.com',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/tenants/nonexistent-slug');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'টেন্যান্ট পাওয়া যায় নি',
            ]);
    }

    public function test_update_tenant(): void
    {
        $tenant = Tenant::create([
            'name_bn' => 'Old Name',
            'name_en' => 'Old Name',
            'slug' => 'old-name',
            'type' => 'madrasa',
            'contact_email' => 'old@example.com',
            'subscription_tier' => 'free',
            'subscription_status' => 'active',
            'modules_enabled' => ['student'],
        ]);

        $user = User::create([
            'name_bn' => 'Super Admin',
            'name_en' => 'Super Admin',
            'email' => 'super@example.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
            'is_platform_admin' => true,
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'super@example.com',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/tenants/old-name', [
                'name_bn' => 'New Name',
                'name_en' => 'New Name',
                'city' => 'Dhaka',
                'district' => 'Dhaka',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'টেন্যান্ট আপডেট সফল',
            ]);

        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'name_bn' => 'New Name',
            'city' => 'Dhaka',
        ]);
    }

    public function test_delete_tenant(): void
    {
        $tenant = Tenant::create([
            'name_bn' => 'To Delete',
            'name_en' => 'To Delete',
            'slug' => 'to-delete',
            'type' => 'madrasa',
            'contact_email' => 'delete@example.com',
            'subscription_tier' => 'free',
            'subscription_status' => 'active',
            'modules_enabled' => ['student'],
        ]);

        $user = User::create([
            'name_bn' => 'Super Admin',
            'name_en' => 'Super Admin',
            'email' => 'super@example.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
            'is_platform_admin' => true,
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'super@example.com',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/v1/tenants/to-delete');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'টেন্যান্ট মুছে ফেলা সফল',
            ]);

        $this->assertSoftDeleted('tenants', [
            'slug' => 'to-delete',
        ]);
    }

    public function test_tenant_scoping_config_exists(): void
    {
        $config = config('tenancy');

        $this->assertArrayHasKey('enabled', $config);
        $this->assertTrue($config['enabled']);
        $this->assertArrayHasKey('scoping', $config);
        $this->assertTrue($config['scoping']['enabled']);
    }
}
