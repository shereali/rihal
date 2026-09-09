<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\TransportAssignment;
use App\Models\TransportBus;
use App\Models\TransportRoute;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use Illuminate\Support\Facades\DB;

class TransportAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $tenant = $request->get('tenant');
        $query = TransportAssignment::where('tenant_id', $tenant?->id)
            ->with(['student', 'bus', 'route'])
            ->when($request->filled('student_id'), fn($q) => $q->where('student_id', $request->student_id))
            ->when($request->filled('bus_id'), fn($q) => $q->where('bus_id', $request->bus_id))
            ->when($request->filled('route_id'), fn($q) => $q->where('route_id', $request->route_id))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->orderByDesc('id');

        $perPage = min((int) $request->query('per_page', 15), 100);
        $items = $query->paginate($perPage);

        return ApiResource::collection($items, function ($item) {
            return [
                'id' => $item->id,
                'student_id' => $item->student_id,
                'student' => $item->whenLoaded('student', fn() => [
                    'id' => $item->student?->id,
                    'name_bn' => $item->student?->name_bn ?? $item->student?->name,
                    'name' => $item->student?->name,
                    'class' => $item->student?->enrollment?->academicClass?->class_name ?? $item->student?->enrollment?->class_name,
                    'section' => $item->student?->enrollment?->section?->section_name,
                ]),
                'bus_id' => $item->bus_id,
                'bus' => $item->whenLoaded('bus', fn() => [
                    'id' => $item->bus?->id,
                    'bus_number' => $item->bus?->bus_number,
                    'bus_name' => $item->bus?->bus_name,
                    'plate_number' => $item->bus?->plate_number,
                ]),
                'route_id' => $item->route_id,
                'route' => $item->whenLoaded('route', fn() => [
                    'id' => $item->route?->id,
                    'route_name' => $item->route?->route_name,
                    'start_location' => $item->route?->start_location,
                    'end_location' => $item->route?->end_location,
                ]),
                'pickup_time' => $item->pickup_time?->format('h:i A'),
                'dropoff_time' => $item->dropoff_time?->format('h:i A'),
                'monthly_fee' => (float) $item->monthly_fee,
                'seat_number' => $item->seat_number,
                'status' => $item->status,
                'is_active' => (bool) $item->is_active,
                'created_at' => $item->created_at?->format('d M, Y'),
            ];
        });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'bus_id' => 'required|integer|exists:transport_buses,id',
            'route_id' => 'required|integer|exists:transport_routes,id',
            'pickup_time' => 'required|date_format:H:i',
            'dropoff_time' => 'required|date_format:H:i',
            'monthly_fee' => 'nullable|numeric|min:0',
            'seat_number' => 'nullable|string|max:10',
            'status' => 'nullable|string|in:active,inactive,completed,cancelled',
        ]);

        $validated['tenant_id'] = $request->get('tenant')?->id;
        $validated['pickup_time'] = $this->parseTime($validated['pickup_time']);
        $validated['dropoff_time'] = $this->parseTime($validated['dropoff_time']);
        $validated['status'] = $request->status ?? 'active';
        $validated['is_active'] = true;

        // Check for existing active assignment for this student
        $existing = TransportAssignment::where('tenant_id', $validated['tenant_id'])
            ->where('student_id', $validated['student_id'])
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return ApiResource::error('এই শিক্ষার্থীর জন্য ইতিমধ্যে সক্রিয় বরাদ্দ আছে। পূর্ববর্তী বরাদ্দটি নিষ্ক্রিয় বা শেষ করুন।', 422);
        }

        $assignment = TransportAssignment::create($validated);
        $assignment->load(['student', 'bus', 'route']);

        return ApiResource::success([
            'message' => 'বরাদ্দ সফলভাবে তৈরি হয়েছে।',
            'data' => $this->formatItem($assignment),
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $assignment = TransportAssignment::where('tenant_id', $tenant?->id)
            ->with(['student', 'bus', 'route'])
            ->findOrFail($id);

        return ApiResource::success($this->formatItem($assignment));
    }

    public function update(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $assignment = TransportAssignment::where('tenant_id', $tenant?->id)->findOrFail($id);

        $validated = $request->validate([
            'student_id' => 'sometimes|integer|exists:students,id',
            'bus_id' => 'sometimes|integer|exists:transport_buses,id',
            'route_id' => 'sometimes|integer|exists:transport_routes,id',
            'pickup_time' => 'sometimes|date_format:H:i',
            'dropoff_time' => 'sometimes|date_format:H:i',
            'monthly_fee' => 'nullable|numeric|min:0',
            'seat_number' => 'nullable|string|max:10',
            'status' => 'nullable|string|in:active,inactive,completed,cancelled',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['pickup_time'])) {
            $validated['pickup_time'] = $this->parseTime($validated['pickup_time']);
        }
        if (isset($validated['dropoff_time'])) {
            $validated['dropoff_time'] = $this->parseTime($validated['dropoff_time']);
        }

        $assignment->update($validated);
        $assignment->load(['student', 'bus', 'route']);

        return ApiResource::success([
            'message' => 'বরাদ্দ আপডেট হয়েছে।',
            'data' => $this->formatItem($assignment),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $assignment = TransportAssignment::where('tenant_id', $tenant?->id)->findOrFail($id);
        $assignment->delete();

        return ApiResource::success(['message' => 'বরাদ্দ ডিলিট হয়েছে।']);
    }

    private function parseTime(string $time): string
    {
        $now = now();
        return $now->format('Y-m-d') . ' ' . $time;
    }

    private function formatItem($item): array
    {
        return [
            'id' => $item->id,
            'student_id' => $item->student_id,
            'student' => $item->whenLoaded('student', fn() => [
                'id' => $item->student?->id,
                'name_bn' => $item->student?->name_bn ?? $item->student?->name,
                'name' => $item->student?->name,
                'class' => $item->student?->enrollment?->academicClass?->class_name ?? $item->student?->enrollment?->class_name,
                'section' => $item->student?->enrollment?->section?->section_name,
            ]),
            'bus_id' => $item->bus_id,
            'bus' => $item->whenLoaded('bus', fn() => [
                'id' => $item->bus?->id,
                'bus_number' => $item->bus?->bus_number,
                'bus_name' => $item->bus?->bus_name,
                'plate_number' => $item->bus?->plate_number,
            ]),
            'route_id' => $item->route_id,
            'route' => $item->whenLoaded('route', fn() => [
                'id' => $item->route?->id,
                'route_name' => $item->route?->route_name,
                'start_location' => $item->route?->start_location,
                'end_location' => $item->route?->end_location,
            ]),
            'pickup_time' => $item->pickup_time?->format('h:i A'),
            'dropoff_time' => $item->dropoff_time?->format('h:i A'),
            'monthly_fee' => (float) $item->monthly_fee,
            'seat_number' => $item->seat_number,
            'status' => $item->status,
            'is_active' => (bool) $item->is_active,
            'created_at' => $item->created_at?->format('d M, Y'),
        ];
    }
}