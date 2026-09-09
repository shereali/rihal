<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use App\Models\TenantBranch;
use Illuminate\Support\Facades\Hash;

class PlatformAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create or find super admin user
        $user = User::firstOrCreate(
            ['email' => 'admin@rihal.app'],
            [
                'name_bn' => 'প্ল্যাটফর্ম প্রশাসক',
                'name_en' => 'Platform Administrator',
                'phone' => '+880****0000',
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
                'is_active' => true,
                'is_platform_admin' => true,
            ]
        );

        // Create default tenant for the super admin (only if not exists)
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'rihal-platform'],
            [
                'name_bn' => 'রিহাল প্ল্যাটফর্ম',
                'name_en' => 'Rihal Platform',
                'type' => 'madrasa',
                'contact_email' => 'admin@rihal.app',
                'contact_phone' => '+880****0000',
                'principal_name' => 'প্ল্যাটফর্ম প্রশাসক',
                'principal_email' => 'admin@rihal.app',
                'subscription_tier' => 'enterprise',
                'subscription_status' => 'active',
                'modules_enabled' => ['student', 'academic', 'finance', 'attendance', 'notice', 'hostel', 'transport', 'inventory', 'hr', 'property', 'report', 'ai'],
                'settings' => [],
                'activated_at' => now(),
            ]
        );

        // Link user to tenant if not already linked
        if (!$user->tenant_id || $user->tenant_id != $tenant->id) {
            $user->tenant_id = $tenant->id;
            $user->save();
        }

        // Create a primary branch for the platform tenant (with guard check)
        if (class_exists('App\Models\TenantBranch')) {
            $branch = TenantBranch::where('tenant_id', $tenant->id)->where('is_primary', true)->first();
            if (!$branch) {
                TenantBranch::create([
                    'tenant_id' => $tenant->id,
                    'name_bn' => 'মূলশাখা',
                    'name_en' => 'Head Office',
                    'is_primary' => true,
                    'is_active' => true,
                ]);
            }
        }
    }
}
