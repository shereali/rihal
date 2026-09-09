<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends ApiController
{
    /**
     * List users within the current tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 100), 100);

        $query = User::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sq) use ($search) {
                    $sq->where('name_bn', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->has('role'), function ($q) use ($request) {
                $q->where('role', $request->input('role'));
            })
            ->when($request->has('is_active'), function ($q) use ($request) {
                $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy('created_at', 'desc');

        $users = $query->paginate($perPage);

        return $this->successResponse($users);
    }

    /**
     * Show a user by ID.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $targetUser = User::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$targetUser) {
            return $this->errorResponse('ব্যবহারকারী পাওয়া যায় নি', 404);
        }

        return $this->successResponse($targetUser);
    }

    /**
     * Create a new user within the tenant.
     */
    public function store(Request $request): JsonResponse
    {
        $currentUser = $request->user();

        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required|integer|exists:tenants,id',
            'name_bn' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|in:super_admin,admin,teacher,staff,student,user',
            'title' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
        ], [
            'tenant_id.required' => 'টেন্যান্ট প্রয়োজন।',
            'tenant_id.exists' => 'এই টেন্যান্টটি বিদ্যমান নয়।',
            'name_bn.required' => 'নাম (বাংলা) প্রয়োজন।',
            'email.required' => 'ইমেইল প্রয়োজন।',
            'email.unique' => 'এই ইমেইলটি আগে থেকেই নিবন্ধিত।',
            'password.required' => 'পাসওয়ার্ড প্রয়োজন।',
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষর হতে হবে।',
            'password.confirmed' => 'পাসওয়ার্ড এবং পাসওয়ার্ড নিশ্চিতকরণ মিলছে না।',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();

        $data['password'] = Hash::make($data['password']);
        $data['is_platform_admin'] = false;

        $user = User::create($data);

        $user->load('tenant');

        return $this->successResponse($user, 'ব্যবহারকারী তৈরি সফল', 201);
    }

    /**
     * Update a user.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $currentUser = $request->user();

        $targetUser = User::where('tenant_id', $currentUser->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$targetUser) {
            return $this->errorResponse('ব্যবহারকারী পাওয়া যায় নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'name_bn' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'title' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'role' => 'nullable|in:super_admin,admin,teacher,staff,student,user',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $targetUser->update($validator->validated());

        return $this->successResponse($targetUser->fresh(), 'ব্যবহারকারী আপডেট সফল');
    }

    /**
     * Delete a user (soft delete).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $currentUser = $request->user();

        $targetUser = User::where('tenant_id', $currentUser->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$targetUser) {
            return $this->errorResponse('ব্যবহারকারী পাওয়া যায় নি', 404);
        }

        if ($targetUser->id === $currentUser->id) {
            return $this->errorResponse('আপনি নিজের অ্যাকাউন্টটি মুছতে পারবেন না', 400);
        }

        $targetUser->delete();

        return $this->successResponse(null, 'ব্যবহারকারী মুছে ফেলা সফল');
    }
}
