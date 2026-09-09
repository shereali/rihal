<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\PropertyVisitor;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;

class PropertyVisitorController extends Controller
{
    public function index(Request $request, $propertyId)
    {
        $tenant = $request->get('tenant');
        Property::where('tenant_id', $tenant?->id)->findOrFail($propertyId);

        $visitors = PropertyVisitor::where('property_id', $propertyId)
            ->when($request->filled('name'), fn($q) => $q->where('name', 'like', "%{$request->name}%"))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('visit_date'), fn($q) => $q->whereDate('visit_date', $request->visit_date))
            ->orderByDesc('id')
            ->paginate((int) $request->query('per_page', 15));

        return ApiResource::collection($visitors, function ($v) {
            return [
                'id' => $v->id,
                'property_id' => $v->property_id,
                'name' => $v->name,
                'phone' => $v->phone,
                'nid' => $v->nid,
                'visit_date' => $v->visit_date?->format('Y-m-d'),
                'entry_time' => $v->entry_time?->format('H:i'),
                'exit_time' => $v->exit_time?->format('H:i'),
                'status' => $v->status,
                'purpose' => $v->purpose,
                'host_name' => $v->host_name,
                'visitor_type' => $v->visitor_type,
                'remarks' => $v->remarks,
                'created_by' => $v->created_by,
                'user' => $v->whenLoaded('user', fn() => [
                    'id' => $v->user?->id,
                    'name_bn' => $v->user?->name_bn ?? $v->user?->name,
                    'name' => $v->user?->name,
                    'phone' => $v->user?->phone,
                ]),
                'created_at' => $v->created_at?->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function store(Request $request, $propertyId)
    {
        $tenant = $request->get('tenant');
        Property::where('tenant_id', $tenant?->id)->findOrFail($propertyId);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'nid' => 'nullable|string|max:50',
            'visit_date' => 'required|date',
            'entry_time' => 'required|date_format:H:i',
            'exit_time' => 'nullable|date_format:H:i|after:entry_time',
            'purpose' => 'nullable|string|max:200',
            'host_name' => 'nullable|string|max:100',
            'visitor_type' => 'nullable|string|in:visitor,staff,vendor,inspector,official,relative,other',
            'status' => 'nullable|string|in:pending,arrived,visited,no_show',
            'remarks' => 'nullable|string|max:500',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $validated['property_id'] = $propertyId;
        $validated['tenant_id'] = $tenant?->id;
        $validated['created_by'] = $request->user()?->id;

        if (!empty($validated['user_id'])) {
            $user = \App\Models\User::find($validated['user_id']);
            $validated['name'] = $user->name_bn ?? $user->name;
            $validated['phone'] = $user->phone ?? $validated['phone'];
            unset($validated['user_id']);
        }

        $visitor = PropertyVisitor::create($validated);

        return ApiResource::success([
            'message' => 'দর্শনার্থী রেকর্ড তৈরি হয়েছে।',
            'data' => [
                'id' => $visitor->id,
                'name' => $visitor->name,
                'phone' => $visitor->phone,
                'nid' => $visitor->nid,
                'visit_date' => $visitor->visit_date?->format('Y-m-d'),
                'entry_time' => $visitor->entry_time?->format('H:i'),
                'exit_time' => $visitor->exit_time?->format('H:i'),
                'status' => $visitor->status,
                'purpose' => $visitor->purpose,
                'host_name' => $visitor->host_name,
                'visitor_type' => $visitor->visitor_type,
                'remarks' => $visitor->remarks,
                'created_at' => $visitor->created_at?->format('Y-m-d H:i:s'),
            ],
        ], 201);
    }

    public function show(Request $request, $propertyId, $id)
    {
        $tenant = $request->get('tenant');
        Property::where('tenant_id', $tenant?->id)->findOrFail($propertyId);

        $visitor = PropertyVisitor::where('property_id', $propertyId)
            ->where('tenant_id', $tenant?->id)
            ->findOrFail($id);

        return ApiResource::success([
            'id' => $visitor->id,
            'property_id' => $visitor->property_id,
            'name' => $visitor->name,
            'phone' => $visitor->phone,
            'nid' => $visitor->nid,
            'visit_date' => $visitor->visit_date?->format('Y-m-d'),
            'entry_time' => $visitor->entry_time?->format('H:i'),
            'exit_time' => $visitor->exit_time?->format('H:i'),
            'status' => $visitor->status,
            'purpose' => $visitor->purpose,
            'host_name' => $visitor->host_name,
            'visitor_type' => $visitor->visitor_type,
            'remarks' => $visitor->remarks,
            'created_by' => $visitor->created_by,
            'user' => $visitor->whenLoaded('user', fn() => [
                'id' => $visitor->user?->id,
                'name_bn' => $visitor->user?->name_bn ?? $visitor->user?->name,
                'name' => $visitor->user?->name,
            ]),
            'created_at' => $visitor->created_at?->format('Y-m-d H:i:s'),
        ]);
    }

    public function update(Request $request, $propertyId, $id)
    {
        $tenant = $request->get('tenant');
        Property::where('tenant_id', $tenant?->id)->findOrFail($propertyId);

        $visitor = PropertyVisitor::where('property_id', $propertyId)
            ->where('tenant_id', $tenant?->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'phone' => 'nullable|string|max:20',
            'nid' => 'nullable|string|max:50',
            'visit_date' => 'sometimes|date',
            'entry_time' => 'sometimes|date_format:H:i',
            'exit_time' => 'nullable|date_format:H:i|after:entry_time',
            'purpose' => 'nullable|string|max:200',
            'host_name' => 'nullable|string|max:100',
            'visitor_type' => 'nullable|string|in:visitor,staff,vendor,inspector,official,relative,other',
            'status' => 'nullable|string|in:pending,arrived,visited,no_show',
            'remarks' => 'nullable|string|max:500',
        ]);

        $visitor->update($validated);

        return ApiResource::success([
            'message' => 'দর্শনার্থী আপডেট হয়েছে।',
            'data' => [
                'id' => $visitor->id,
                'name' => $visitor->name,
                'phone' => $visitor->phone,
                'nid' => $visitor->nid,
                'visit_date' => $visitor->visit_date?->format('Y-m-d'),
                'entry_time' => $visitor->entry_time?->format('H:i'),
                'exit_time' => $visitor->exit_time?->format('H:i'),
                'status' => $visitor->status,
                'purpose' => $visitor->purpose,
                'host_name' => $visitor->host_name,
                'visitor_type' => $visitor->visitor_type,
                'remarks' => $visitor->remarks,
                'updated_at' => $visitor->updated_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function destroy(Request $request, $propertyId, $id)
    {
        $tenant = $request->get('tenant');
        Property::where('tenant_id', $tenant?->id)->findOrFail($propertyId);

        $visitor = PropertyVisitor::where('property_id', $propertyId)
            ->where('tenant_id', $tenant?->id)
            ->findOrFail($id);

        $visitor->delete();

        return ApiResource::success(['message' => 'দর্শনার্থী ডিলিট হয়েছে।']);
    }
}