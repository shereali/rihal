<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TenantController extends ApiController
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * List tenants (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tenant::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_bn', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->has('subscription_tier')) {
            $query->where('subscription_tier', $request->input('subscription_tier'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);

        $tenants = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->successResponse($tenants);
    }

    /**
     * Create a new tenant.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name_bn' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'type' => 'required|in:madrasa,school,college,university,organization',
            'registration_number' => 'nullable|string|max:100',
            'established_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'address_bn' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'principal_name' => 'nullable|string|max:255',
            'principal_email' => 'nullable|email|max:255',
            'subscription_tier' => 'required|in:free,premium,enterprise',
            'modules_enabled' => 'nullable|array',
        ], [
            'name_bn.required' => 'নাম (বাংলা) প্রয়োজন।',
            'type.required' => 'ধরন প্রয়োজন।',
            'subscription_tier.required' => 'সাবস্ক্রিপশন লেভেল প্রয়োজন।',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $tenant = $this->tenantService->create($validator->validated());

        return $this->successResponse($tenant, 'টেন্যান্ট তৈরি সফল', 201);
    }

    /**
     * Show a tenant by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $tenant = Tenant::where('slug', $slug)->first();

        if (!$tenant) {
            return $this->errorResponse('টেন্যান্ট পাওয়া যায় নি', 404);
        }

        return $this->successResponse($tenant);
    }

    /**
     * Get current tenant (from authenticated user).
     */
    public function current(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        return $this->successResponse($tenant);
    }

    /**
     * Update a tenant.
     */
    public function update(Request $request, string $slug): JsonResponse
    {
        $tenant = Tenant::where('slug', $slug)->first();

        if (!$tenant) {
            return $this->errorResponse('টেন্যান্ট পাওয়া যায় নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'name_bn' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'type' => 'nullable|in:madrasa,school,college,university,organization',
            'registration_number' => 'nullable|string|max:100',
            'established_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'address_bn' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'principal_name' => 'nullable|string|max:255',
            'principal_email' => 'nullable|email|max:255',
            'subscription_tier' => 'nullable|in:free,premium,enterprise',
            'modules_enabled' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $tenant->update($validator->validated());

        return $this->successResponse($tenant->fresh(), 'টেন্যান্ট আপডেট সফল');
    }

    /**
     * Delete a tenant (soft delete).
     */
    public function destroy(Request $request, string $slug): JsonResponse
    {
        $tenant = Tenant::where('slug', $slug)->first();

        if (!$tenant) {
            return $this->errorResponse('টেন্যান্ট পাওয়া যায় নি', 404);
        }

        $tenant->delete();

        return $this->successResponse(null, 'টেন্যান্ট মুছে ফেলা সফল');
    }
}
