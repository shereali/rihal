<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Tenant;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'demo-madrasa')->first();
        if (!$tenant) {
            return;
        }

        // Create demo admin for the tenant
        if (!User::where('tenant_id', $tenant->id)->where('email', 'admin@demo.bd')->exists()) {
            User::create([
                'tenant_id' => $tenant->id,
                'name_bn' => 'মাদ্রাসা প্রশাসক',
                'name_en' => 'Madrasa Admin',
                'email' => 'admin@demo.bd',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_platform_admin' => false,
                'is_active' => true,
            ]);
        }

        // Create a few demo users
        $demoUsers = [
            ['name_bn' => 'আলী আহমেদ', 'name_en' => 'Ali Ahmed', 'role' => 'teacher'],
            ['name_bn' => 'সুমিতা আক্তার', 'name_en' => 'Sumita Akter', 'role' => 'teacher'],
            ['name_bn' => 'রহিমুল ইসলাম', 'name_en' => 'Rahimul Islam', 'role' => 'staff'],
            ['name_bn' => 'সায়েদা নুরজাহান', 'name_en' => 'Syeda Nurjahan', 'role' => 'student'],
        ];

        foreach ($demoUsers as $user) {
            User::firstOrCreate(
                ['tenant_id' => $tenant->id, 'email' => strtolower(str_replace(' ', '.', $user['name_en'])) . '@demo.bd'],
                [
                    'tenant_id' => $tenant->id,
                    'name_bn' => $user['name_bn'],
                    'name_en' => $user['name_en'],
                    'email' => strtolower(str_replace(' ', '.', $user['name_en'])) . '@demo.bd',
                    'password' => Hash::make('demo123'),
                    'role' => $user['role'],
                    'is_platform_admin' => false,
                    'is_active' => true,
                ]
            );
        }
    }
}
