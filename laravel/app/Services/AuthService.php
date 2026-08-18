<?php

namespace App\Services;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name_bn' => $data['name_bn'],
                'name_en' => $data['name_en'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => 'user',
                'is_active' => true,
                'is_platform_admin' => false,
                'tenant_id' => $data['tenant_id'] ?? null,
            ]);

            $user->load('tenant');
            return $user;
        });
    }

    public function login(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'ভুল ইমেইল বা পাসওয়ার্ড',
                'status' => 401,
            ];
        }

        if (!Hash::check($data['password'], $user->password)) {
            return [
                'success' => false,
                'message' => 'ভুল ইমেইল বা পাসওয়ার্ড',
                'status' => 401,
            ];
        }

        if (!$user->is_active) {
            return [
                'success' => false,
                'message' => 'অ্যাকাউন্টটি নিষ্ক্রিয় করা হয়েছে',
                'status' => 403,
            ];
        }

        $token = $user->createToken('rihal-token')->plainTextToken;

        $user->load('tenant');

        return [
            'success' => true,
            'message' => 'লগইন সফল',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'status' => 200,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
