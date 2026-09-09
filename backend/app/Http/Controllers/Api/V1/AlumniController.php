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
        $alumni = AlumniGraduate::when($request->search, function ($q, $search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('workplace', 'like', "%{$search}%");
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

        $alumni = AlumniGraduate::create(array_merge($validated, [
            'tenant_id' => $request->user()?->tenant_id,
            'sanad_no'  => 'SND-' . date('Y') . '-' . rand(100, 999),
        ]));

        return response()->json([
            'status' => 201,
            'data' => $alumni,
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $alumni = AlumniGraduate::findOrFail($id);
        $alumni->update($request->all());

        return response()->json([
            'status' => 200,
            'data' => $alumni,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        AlumniGraduate::findOrFail($id)->delete();
        return response()->json([
            'status' => 200,
            'message' => 'ফারেগীন রেকর্ড মুছে ফেলা হয়েছে',
        ]);
    }
}
