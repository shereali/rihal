<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\HostelRoom;
use App\Models\HostelVisitor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class HostelController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = HostelRoom::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where('room_number', 'like', "%{$search}%")
                    ->orWhere('block', 'like', "%{$search}%");
            })
            ->when($request->has('block'), fn($q) => $q->where('block', $request->input('block')))
            ->when($request->has('floor'), fn($q) => $q->where('floor', $request->input('floor')))
            ->when($request->has('is_available'), fn($q) => $q->where('is_available', filter_var($request->input('is_available'), FILTER_VALIDATE_BOOLEAN)))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->with('warden:id,name_bn,name_en,email,phone')
            ->orderBy('floor')
            ->orderBy('room_number');

        $rooms = $query->paginate($perPage);

        return $this->successResponse($rooms);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $room = HostelRoom::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->with('warden:id,name_bn,name_en,email,phone')
            ->with('visitors')
            ->first();

        if (!$room) {
            return $this->errorResponse('হোস্টেল কক্ষ পাওয়া যায়নি', 404);
        }

        return $this->successResponse($room);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'room_number' => 'required|string|max:50',
            'block' => 'nullable|string|max:50',
            'floor' => 'nullable|integer',
            'capacity' => 'nullable|integer|min:1',
            'current_occupancy' => 'nullable|integer',
            'monthly_rent' => 'nullable|numeric',
            'is_available' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'amenities' => 'nullable|array',
            'students' => 'nullable|json',
            'images' => 'nullable|json',
            'warden_id' => 'nullable|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['is_available'] = $data['is_available'] ?? true;
        $data['is_active'] = $data['is_active'] ?? true;
        $data['current_occupancy'] = $data['current_occupancy'] ?? 0;

        $room = HostelRoom::create($data);

        $room->load('warden:id,name_bn,name_en,email,phone');

        return $this->successResponse($room, 'হোস্টেল কক্ষ তৈরি সফল', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $room = HostelRoom::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$room) {
            return $this->errorResponse('হোস্টেল কক্ষ পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'room_number' => 'nullable|string|max:50',
            'block' => 'nullable|string|max:50',
            'floor' => 'nullable|integer',
            'capacity' => 'nullable|integer|min:1',
            'current_occupancy' => 'nullable|integer',
            'monthly_rent' => 'nullable|numeric',
            'is_available' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'amenities' => 'nullable|array',
            'students' => 'nullable|json',
            'images' => 'nullable|json',
            'warden_id' => 'nullable|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $room->update($validator->validated());

        $room->load('warden:id,name_bn,name_en,email,phone');

        return $this->successResponse($room->fresh(), 'হোস্টেল কক্ষ আপডেট সফল');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $room = HostelRoom::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$room) {
            return $this->errorResponse('হোস্টেল কক্ষ পাওয়া যায়নি', 404);
        }

        $room->delete();

        return $this->successResponse(null, 'হোস্টেল কক্ষ মুছে ফেলা সফল');
    }

    // ─── Visitors ─────────────────────────────────────────────────────────────

    public function visitors(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = HostelVisitor::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->where('visitor_name_bn', 'like', "%{$request->input('search')}%"))
            ->when($request->has('room_id'), fn($q) => $q->where('room_id', $request->input('room_id')))
            ->when($request->has('hostel_id'), fn($q) => $q->where('hostel_id', $request->input('hostel_id')))
            ->when($request->has('from_date'), fn($q) => $q->where('visit_date', '>=', $request->input('from_date')))
            ->when($request->has('to_date'), fn($q) => $q->where('visit_date', '<=', $request->input('to_date')))
            ->with('room:id,room_number,block')
            ->orderBy('visit_date', 'desc')
            ->orderBy('created_at', 'desc');

        $visitors = $query->paginate($perPage);

        return $this->successResponse($visitors);
    }

    public function storeVisitor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'nullable|integer|exists:hostel_rooms,id',
            'hostel_id' => 'nullable|integer',
            'visitor_name_bn' => 'required|string|max:255',
            'visitor_name_en' => 'nullable|string|max:255',
            'visitor_relation' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:50',
            'visit_date' => 'nullable|date',
            'visit_time' => 'nullable|datetime',
            'departure_time' => 'nullable|datetime',
            'purpose' => 'nullable|string|max:500',
            'remarks' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['is_active'] = $data['is_active'] ?? true;

        $visitor = HostelVisitor::create($data);

        $visitor->load('room:id,room_number,block');

        return $this->successResponse($visitor, 'হোস্টেল দর্শনার্থী রেকর্ড সফল', 201);
    }

    public function updateVisitor(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $visitor = HostelVisitor::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$visitor) {
            return $this->errorResponse('হোস্টেল দর্শনার্থী পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'departure_time' => 'nullable|datetime',
            'remarks' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $visitor->update($validator->validated());

        return $this->successResponse($visitor->fresh(), 'হোস্টেল দর্শনার্থী আপডেট সফল');
    }

    public function destroyVisitor(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $visitor = HostelVisitor::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$visitor) {
            return $this->errorResponse('হোস্টেল দর্শনার্থী পাওয়া যায়নি', 404);
        }

        $visitor->delete();

        return $this->successResponse(null, 'হোস্টেল দর্শনার্থী রেকর্ড মুছে ফেলা সফল');
    }
}
