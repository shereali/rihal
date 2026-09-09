<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\PropertyMaintenance;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;

class PropertyMaintenanceController extends Controller
{
    public function index(Request $request, $propertyId)
    {
        $tenant = $request->get('tenant');
        Property::where('tenant_id', $tenant?->id)->findOrFail($propertyId);

        $maintenance = PropertyMaintenance::where('property_id', $propertyId)
            ->orderByDesc('id')
            ->paginate((int) $request->query('per_page', 15));

        return ApiResource::collection($maintenance, function ($m) {
            return [
                'id' => $m->id,
                'property_id' => $m->property_id,
                'maintenance_type' => $m->maintenance_type,
                'description' => $m->description,
                'estimated_cost' => (float) ($m->estimated_cost ?? 0),
                'actual_cost' => (float) ($m->actual_cost ?? 0),
                'status' => $m->status,
                'vendor_name' => $m->vendor_name,
                'scheduled_date' => $m->scheduled_date?->format('Y-m-d'),
                'completed_date' => $m->completed_date?->format('Y-m-d'),
                'created_at' => $m->created_at?->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function store(Request $request, $propertyId)
    {
        $tenant = $request->get('tenant');
        Property::where('tenant_id', $tenant?->id)->findOrFail($propertyId);

        $validated = $request->validate([
            'maintenance_type' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:pending,in_progress,completed,cancelled',
            'vendor_name' => 'nullable|string|max:100',
            'scheduled_date' => 'nullable|date',
            'completed_date' => 'nullable|date',
        ]);

        $maintenance = PropertyMaintenance::create([
            'property_id' => $propertyId,
            'tenant_id' => $tenant?->id,
            ...$validated,
            'created_by' => $request->user()?->id,
        ]);

        return ApiResource::success([
            'message' => 'মেইনটেন্যান্স রেকর্ড তৈরি হয়েছে।',
            'data' => [
                'id' => $maintenance->id,
                'maintenance_type' => $maintenance->maintenance_type,
                'description' => $maintenance->description,
                'estimated_cost' => (float) ($maintenance->estimated_cost ?? 0),
                'status' => $maintenance->status,
                'vendor_name' => $maintenance->vendor_name,
                'scheduled_date' => $maintenance->scheduled_date?->format('Y-m-d'),
                'created_at' => $maintenance->created_at?->format('Y-m-d H:i:s'),
            ],
        ], 201);
    }

    public function show(Request $request, $propertyId, $id)
    {
        $tenant = $request->get('tenant');
        Property::where('tenant_id', $tenant?->id)->findOrFail($propertyId);

        $maintenance = PropertyMaintenance::where('property_id', $propertyId)
            ->where('tenant_id', $tenant?->id)
            ->findOrFail($id);

        return ApiResource::success([
            'id' => $maintenance->id,
            'property_id' => $maintenance->property_id,
            'maintenance_type' => $maintenance->maintenance_type,
            'description' => $maintenance->description,
            'estimated_cost' => (float) ($maintenance->estimated_cost ?? 0),
            'actual_cost' => (float) ($maintenance->actual_cost ?? 0),
            'status' => $maintenance->status,
            'vendor_name' => $maintenance->vendor_name,
            'scheduled_date' => $maintenance->scheduled_date?->format('Y-m-d'),
            'completed_date' => $maintenance->completed_date?->format('Y-m-d'),
            'created_by' => $maintenance->created_by,
            'created_at' => $maintenance->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $maintenance->updated_at?->format('Y-m-d H:i:s'),
        ]);
    }

    public function update(Request $request, $propertyId, $id)
    {
        $tenant = $request->get('tenant');
        Property::where('tenant_id', $tenant?->id)->findOrFail($propertyId);

        $maintenance = PropertyMaintenance::where('property_id', $propertyId)
            ->where('tenant_id', $tenant?->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'maintenance_type' => 'sometimes|string|max:50',
            'description' => 'nullable|string|max:500',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:pending,in_progress,completed,cancelled',
            'vendor_name' => 'nullable|string|max:100',
            'scheduled_date' => 'nullable|date',
            'completed_date' => 'nullable|date',
        ]);

        $maintenance->update($validated);

        return ApiResource::success([
            'message' => 'মেইনটেন্যান্স আপডেট হয়েছে।',
            'data' => [
                'id' => $maintenance->id,
                'maintenance_type' => $maintenance->maintenance_type,
                'description' => $maintenance->description,
                'estimated_cost' => (float) ($maintenance->estimated_cost ?? 0),
                'actual_cost' => (float) ($maintenance->actual_cost ?? 0),
                'status' => $maintenance->status,
                'vendor_name' => $maintenance->vendor_name,
                'scheduled_date' => $maintenance->scheduled_date?->format('Y-m-d'),
                'completed_date' => $maintenance->completed_date?->format('Y-m-d'),
                'updated_at' => $maintenance->updated_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function destroy(Request $request, $propertyId, $id)
    {
        $tenant = $request->get('tenant');
        Property::where('tenant_id', $tenant?->id)->findOrFail($propertyId);

        $maintenance = PropertyMaintenance::where('property_id', $propertyId)
            ->where('tenant_id', $tenant?->id)
            ->findOrFail($id);

        $maintenance->delete();

        return ApiResource::success(['message' => 'মেইনটেন্যান্স ডিলিট হয়েছে।']);
    }
}