<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AlumniGraduate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlumniController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id;

        $alumni = AlumniGraduate::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('workplace', 'like', "%{$search}%")
                      ->orWhere('sanad_no', 'like', "%{$search}%");
                });
            })
            ->when($request->batch, fn($q, $b) => $q->where('batch', $b))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $alumni,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'batch'              => 'required|string|max:50',
            'phone'              => 'nullable|string|max:50',
            'degree'             => 'nullable|string|max:100',
            'workplace'          => 'nullable|string|max:255',
            'designation'        => 'nullable|string|max:100',
            'status'             => 'required|string|in:employed,jobless,higher_study',
            'preferred_job'      => 'nullable|string|max:255',
            'preferred_location' => 'nullable|string|max:255',
            'institution'        => 'nullable|string|max:255',
            'country'            => 'nullable|string|max:100',
        ]);

        $tenantId = $request->user()?->tenant_id;
        $year = date('Y');
        $count = AlumniGraduate::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->whereYear('created_at', $year)->count() + 1;
        do {
            $sanadNo = "SND-{$year}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
            $count++;
        } while (AlumniGraduate::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('sanad_no', $sanadNo)->exists());

        $alumni = AlumniGraduate::create(array_merge($validated, [
            'tenant_id' => $tenantId,
            'sanad_no'  => $sanadNo,
        ]));

        return response()->json([
            'status' => 201,
            'data' => $alumni,
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id;
        $alumni = AlumniGraduate::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->findOrFail($id);

        $validated = $request->validate([
            'name'               => 'sometimes|required|string|max:255',
            'batch'              => 'sometimes|required|string|max:50',
            'phone'              => 'nullable|string|max:50',
            'degree'             => 'nullable|string|max:100',
            'workplace'          => 'nullable|string|max:255',
            'designation'        => 'nullable|string|max:100',
            'status'             => 'sometimes|required|string|in:employed,jobless,higher_study',
            'preferred_job'      => 'nullable|string|max:255',
            'preferred_location' => 'nullable|string|max:255',
            'institution'        => 'nullable|string|max:255',
            'country'            => 'nullable|string|max:100',
        ]);

        $alumni->update($validated);

        return response()->json([
            'status' => 200,
            'data' => $alumni,
        ]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id;
        $alumni = AlumniGraduate::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->findOrFail($id);
        $alumni->delete();

        return response()->json([
            'status' => 200,
            'message' => 'ফারেগীন রেকর্ড মুছে ফেলা হয়েছে',
        ]);
    }
}
