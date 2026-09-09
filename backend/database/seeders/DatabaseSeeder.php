<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Running RolePermissionSeeder...');
        $this->call(RolePermissionSeeder::class);

        // Only seed platform admin if not already present
        if (!\App\Models\User::where('email', 'admin@rihal.app')->exists()) {
            $this->command->info('Running PlatformAdminSeeder...');
            $this->call(PlatformAdminSeeder::class);
        } else {
            $this->command->info('Platform admin already exists, skipping.');
        }

        $this->command->info('Running DemoTenantSeeder...');
        $this->call(DemoTenantSeeder::class);

        $this->command->info('Database seeding completed.');
    }
}
