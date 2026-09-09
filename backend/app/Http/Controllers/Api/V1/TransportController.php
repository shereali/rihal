<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\TransportRoute;
use App\Models\TransportBus;
use App\Models\TransportAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TransportController extends ApiController
{
    // ─── Routes ────────────────────────────────────────────────────────────────

    public function routes(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = TransportRoute::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->where('route_name_bn', 'like', "%{$request->input('search')}%"))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->with('buses')
            ->orderBy('route_name_bn');

        $routes = $query->paginate($perPage);

        return $this->successResponse($routes);
    }

    public function showRoute(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $route = TransportRoute::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->with('buses')
            ->first();

        if (!$route) {
            return $this->errorResponse('পথ পাওয়া যায়নি', 404);
        }

        return $this->successResponse($route);
    }

    public function storeRoute(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'route_name_bn' => 'required|string|max:255',
            'route_name_en' => 'nullable|string|max:255',
            'start_point' => 'nullable|string|max:255',
            'end_point' => 'nullable|string|max:255',
            'stop_points' => 'nullable|json',
            'distance_km' => 'nullable|numeric',
            'estimated_duration_minutes' => 'nullable|integer',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'fare' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['is_active'] = $data['is_active'] ?? true;

        $route = TransportRoute::create($data);

        return $this->successResponse($route, 'পথ তৈরি সফল', 201);
    }

    public function updateRoute(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $route = TransportRoute::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$route) {
            return $this->errorResponse('পথ পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'route_name_bn' => 'nullable|string|max:255',
            'route_name_en' => 'nullable|string|max:255',
            'start_point' => 'nullable|string|max:255',
            'end_point' => 'nullable|string|max:255',
            'stop_points' => 'nullable|json',
            'distance_km' => 'nullable|numeric',
            'estimated_duration_minutes' => 'nullable|integer',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'fare' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $route->update($validator->validated());

        return $this->successResponse($route->fresh(), 'পথ আপডেট সফল');
    }

    public function destroyRoute(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $route = TransportRoute::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$route) {
            return $this->errorResponse('পথ পাওয়া যায়নি', 404);
        }

        $route->delete();

        return $this->successResponse(null, 'পথ মুছে ফেলা সফল');
    }

    // ─── Buses ────────────────────────────────────────────────────────────────

    public function buses(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = TransportBus::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->where('bus_number', 'like', "%{$request->input('search')}%"))
            ->when($request->has('route_id'), fn($q) => $q->where('route_id', $request->input('route_id')))
            ->when($request->has('driver_id'), fn($q) => $q->where('driver_id', $request->input('driver_id')))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->with('route:id,route_name_bn,route_name_en')
            ->with('driver:id,name_bn,name_en,email,phone')
            ->orderBy('bus_number');

        $buses = $query->paginate($perPage);

        return $this->successResponse($buses);
    }

    public function showBus(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $bus = TransportBus::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->with('route:id,route_name_bn,route_name_en')
            ->with('driver:id,name_bn,name_en,email,phone')
            ->with('assignments')
            ->first();

        if (!$bus) {
            return $this->errorResponse('যানবাহন পাওয়া যায়নি', 404);
        }

        return $this->successResponse($bus);
    }

    public function storeBus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bus_number' => 'required|string|max:50',
            'route_id' => 'nullable|integer|exists:transport_routes,id',
            'driver_id' => 'nullable|integer|exists:users,id',
            'capacity' => 'nullable|integer|min:1',
            'current_occupancy' => 'nullable|integer',
            'vehicle_type' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:100',
            'insurance_expiry' => 'nullable|date',
            'fitness_expiry' => 'nullable|date',
            'last_maintenance_date' => 'nullable|date',
            'has_ac' => 'nullable|boolean',
            'has_seat_belts' => 'nullable|boolean',
            'gps_enabled' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'images' => 'nullable|json',
            'documents' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['is_active'] = $data['is_active'] ?? true;
        $data['current_occupancy'] = $data['current_occupancy'] ?? 0;

        $bus = TransportBus::create($data);

        $bus->load('route:id,route_name_bn,route_name_en');
        $bus->load('driver:id,name_bn,name_en,email,phone');

        return $this->successResponse($bus, 'যানবাহন তৈরি সফল', 201);
    }

    public function updateBus(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $bus = TransportBus::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$bus) {
            return $this->errorResponse('যানবাহন পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'bus_number' => 'nullable|string|max:50',
            'route_id' => 'nullable|integer|exists:transport_routes,id',
            'driver_id' => 'nullable|integer|exists:users,id',
            'capacity' => 'nullable|integer|min:1',
            'current_occupancy' => 'nullable|integer',
            'vehicle_type' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:100',
            'insurance_expiry' => 'nullable|date',
            'fitness_expiry' => 'nullable|date',
            'last_maintenance_date' => 'nullable|date',
            'has_ac' => 'nullable|boolean',
            'has_seat_belts' => 'nullable|boolean',
            'gps_enabled' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'images' => 'nullable|json',
            'documents' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $bus->update($validator->validated());

        $bus->load('route:id,route_name_bn,route_name_en');
        $bus->load('driver:id,name_bn,name_en,email,phone');

        return $this->successResponse($bus->fresh(), 'যানবাহন আপডেট সফল');
    }

    public function destroyBus(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $bus = TransportBus::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$bus) {
            return $this->errorResponse('যানবাহন পাওয়া যায়নি', 404);
        }

        $bus->delete();

        return $this->successResponse(null, 'যানবাহন মুছে ফেলা সফল');
    }

    // ─── Assignments ──────────────────────────────────────────────────────────

    public function assignments(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = TransportAssignment::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->whereHas('student', fn($s) => $s->whereHas('user', fn($u) => $u->where('name_bn', 'like', "%{$request->input('search')}%"))))
            ->when($request->has('student_id'), fn($q) => $q->where('student_id', $request->input('student_id')))
            ->when($request->has('bus_id'), fn($q) => $q->where('bus_id', $request->input('bus_id')))
            ->when($request->has('route_id'), fn($q) => $q->where('route_id', $request->input('route_id')))
            ->when($request->has('pickup_point'), fn($q) => $q->where('pickup_point', 'like', "%{$request->input('pickup_point')}%"))
            ->when($request->has('status'), fn($q) => $q->where('status', $request->input('status')))
            ->with('student.user:id,name_bn,name_en')
            ->with('bus:id,bus_number')
            ->with('route:id,route_name_bn')
            ->orderBy('created_at', 'desc');

        $assignments = $query->paginate($perPage);

        return $this->successResponse($assignments);
    }

    public function storeAssignment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'bus_id' => 'nullable|integer|exists:transport_buses,id',
            'route_id' => 'nullable|integer|exists:transport_routes,id',
            'pickup_point' => 'nullable|string|max:255',
            'drop_point' => 'nullable|string|max:255',
            'pickup_time' => 'nullable|datetime',
            'drop_time' => 'nullable|datetime',
            'fare_amount' => 'nullable|numeric',
            'status' => 'nullable|in:active,inactive,paused',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['is_active'] = $data['is_active'] ?? true;

        $assignment = TransportAssignment::create($data);

        $assignment->load('student.user:id,name_bn,name_en');
        $assignment->load('bus:id,bus_number');

        return $this->successResponse($assignment, 'যানবাহন বরাদ্দ সফল', 201);
    }

    public function updateAssignment(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $assignment = TransportAssignment::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$assignment) {
            return $this->errorResponse('যানবাহন বরাদ্দ পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'bus_id' => 'nullable|integer|exists:transport_buses,id',
            'route_id' => 'nullable|integer|exists:transport_routes,id',
            'pickup_point' => 'nullable|string|max:255',
            'drop_point' => 'nullable|string|max:255',
            'pickup_time' => 'nullable|datetime',
            'drop_time' => 'nullable|datetime',
            'fare_amount' => 'nullable|numeric',
            'status' => 'nullable|in:active,inactive,paused',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $assignment->update($validator->validated());

        $assignment->load('student.user:id,name_bn,name_en');

        return $this->successResponse($assignment->fresh(), 'যানবাহন বরাদ্দ আপডেট সফল');
    }

    public function destroyAssignment(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $assignment = TransportAssignment::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$assignment) {
            return $this->errorResponse('যানবাহন বরাদ্দ পাওয়া যায়নি', 404);
        }

        $assignment->delete();

        return $this->successResponse(null, 'যানবাহন বরাদ্দ মুছে ফেলা সফল');
    }
}
