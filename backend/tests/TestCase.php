<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function createTenant(array $attributes = []): Tenant
    {
        return Tenant::create(array_merge([
            'name_bn' => 'Test Madrasa',
            'name_en' => 'Test Madrasa',
            'slug' => 'test-madrasa-' . time(),
            'type' => 'madrasa',
            'contact_email' => 'test@test.com',
            'subscription_tier' => 'free',
            'subscription_status' => 'active',
            'modules_enabled' => ['student'],
        ], $attributes));
    }

    protected function createUser(array $attributes = []): User
    {
        $tenant = Tenant::create([
            'name_bn' => 'Test Madrasa',
            'name_en' => 'Test Madrasa',
            'slug' => 'test-madrasa-' . time(),
            'type' => 'madrasa',
            'contact_email' => 'test@test.com',
            'subscription_tier' => 'free',
            'subscription_status' => 'active',
            'modules_enabled' => ['student'],
        ]);

        return User::create(array_merge([
            'tenant_id' => $tenant->id,
            'name_bn' => 'Test User',
            'name_en' => 'Test User',
            'email' => 'test@test.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_platform_admin' => false,
            'is_active' => true,
        ], $attributes));
    }

    protected function createSuperAdminUser(array $attributes = []): User
    {
        return User::create(array_merge([
            'name_bn' => 'Super Admin',
            'name_en' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_platform_admin' => true,
            'is_active' => true,
        ], $attributes));
    }

    protected function authAsUser(User $user)
    {
        return $this->actingAs($user, 'sanctum');
    }
}
