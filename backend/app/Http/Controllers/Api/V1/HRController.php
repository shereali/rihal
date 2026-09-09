<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Staff;
use App\Models\HostelVisitor;
use App\Models\HostelRoom;
use App\Models\Holiday;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class HRController extends ApiController
{
    // ─── Staff ────────────────────────────────────────────────────────────────

    public function staff(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Staff::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->where('name_bn', 'like', "%{$request->input('search')}%"))
            ->when($request->has('designation'), fn($q) => $q->where('designation', $request->input('designation')))
            ->when($request->has('department'), fn($q) => $q->where('department', $request->input('department')))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->with('user:id,name_bn,name_en,email,phone')
            ->orderBy('name_bn');

        $staff = $query->paginate($perPage);

        return $this->successResponse($staff);
    }

    public function showStaff(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $staff = Staff::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->with('user:id,name_bn,name_en,email,phone')
            ->first();

        if (!$staff) {
            return $this->errorResponse('কর্মকর্তা পাওয়া যায়নি', 404);
        }

        return $this->successResponse($staff);
    }

    public function storeStaff(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name_bn' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:staff,email',
            'phone' => 'nullable|string|max:50',
            'designation' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'nid_number' => 'nullable|string|max:100',
            'join_date' => 'nullable|date',
            'salary' => 'nullable|numeric',
            'address_bn' => 'nullable|string',
            'photo_url' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;

        $staff = Staff::create($data);

        return $this->successResponse($staff, 'কর্মকর্তা তৈরি সফল', 201);
    }

    public function updateStaff(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $staff = Staff::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$staff) {
            return $this->errorResponse('কর্মকর্তা পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'name_bn' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:staff,email,' . $id,
            'phone' => 'nullable|string|max:50',
            'designation' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'nid_number' => 'nullable|string|max:100',
            'join_date' => 'nullable|date',
            'salary' => 'nullable|numeric',
            'address_bn' => 'nullable|string',
            'photo_url' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $staff->update($validator->validated());

        return $this->successResponse($staff->fresh(), 'কর্মকর্তা আপডেট সফল');
    }

    public function destroyStaff(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $staff = Staff::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$staff) {
            return $this->errorResponse('কর্মকর্তা পাওয়া যায়নি', 404);
        }

        $staff->delete();

        return $this->successResponse(null, 'কর্মকর্তা মুছে ফেলা সফল');
    }

    // ─── Hostel Visitors ──────────────────────────────────────────────────────

    public function hostelVisitors(Request $request): JsonResponse
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
            ->orderBy('visit_date', 'desc');

        $visitors = $query->paginate($perPage);

        return $this->successResponse($visitors);
    }

    public function storeHostelVisitor(Request $request): JsonResponse
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

    public function updateHostelVisitor(Request $request, int $id): JsonResponse
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

    public function destroyHostelVisitor(Request $request, int $id): JsonResponse
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

    // ─── Hostel Rooms ─────────────────────────────────────────────────────────

    public function hostelRooms(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = HostelRoom::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->where('room_number', 'like', "%{$request->input('search')}%"))
            ->when($request->has('block'), fn($q) => $q->where('block', $request->input('block')))
            ->when($request->has('floor'), fn($q) => $q->where('floor', $request->input('floor')))
            ->when($request->has('is_available'), fn($q) => $q->where('is_available', filter_var($request->input('is_available'), FILTER_VALIDATE_BOOLEAN)))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->with('warden:id,name_bn,name_en,email')
            ->orderBy('floor')
            ->orderBy('room_number');

        $rooms = $query->paginate($perPage);

        return $this->successResponse($rooms);
    }

    public function storeHostelRoom(Request $request): JsonResponse
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
            'students' => 'nullable|array',
            'images' => 'nullable|array',
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

        $room->load('warden:id,name_bn,name_en,email');

        return $this->successResponse($room, 'হোস্টেল কক্ষ তৈরি সফল', 201);
    }

    // ─── Holidays ─────────────────────────────────────────────────────────────

    public function holidays(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Holiday::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->where('name_bn', 'like', "%{$request->input('search')}%"))
            ->when($request->has('type'), fn($q) => $q->where('type', $request->input('type')))
            ->when($request->has('session_id'), fn($q) => $q->where('session_id', $request->input('session_id')))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->orderBy('date');

        $holidays = $query->paginate($perPage);

        return $this->successResponse($holidays);
    }

    public function storeHoliday(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name_bn' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'date' => 'required|date',
            'type' => 'nullable|in:রাষ্ট্রীয়,ধর্মীয়,অনুষ্ঠানিক,বিশেষ,অন্যান্য',
            'session_id' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'description_bn' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['is_active'] = $data['is_active'] ?? true;

        $holiday = Holiday::create($data);

        return $this->successResponse($holiday, 'ছুটির দিন তৈরি সফল', 201);
    }

    public function updateHoliday(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $holiday = Holiday::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$holiday) {
            return $this->errorResponse('ছুটির দিন পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'name_bn' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'type' => 'nullable|in:রাষ্ট্রীয়,ধর্মীয়,অনুষ্ঠানিক,বিশেষ,অন্যান্য',
            'session_id' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'description_bn' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $holiday->update($validator->validated());

        return $this->successResponse($holiday->fresh(), 'ছুটির দিন আপডেট সফল');
    }

    public function destroyHoliday(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $holiday = Holiday::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$holiday) {
            return $this->errorResponse('ছুটির দিন পাওয়া যায়নি', 404);
        }

        $holiday->delete();

        return $this->successResponse(null, 'ছুটির দিন মুছে ফেলা সফল');
    }

    // ─── Events ───────────────────────────────────────────────────────────────

    public function events(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Event::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->where('title_bn', 'like', "%{$request->input('search')}%"))
            ->when($request->has('type'), fn($q) => $q->where('type', $request->input('type')))
            ->when($request->has('from_date'), fn($q) => $q->where('event_date', '>=', $request->input('from_date')))
            ->when($request->has('to_date'), fn($q) => $q->where('event_date', '<=', $request->input('to_date')))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->with('creator:id,name_bn,name_en')
            ->withCount('registrations')
            ->orderBy('event_date', 'desc');

        $events = $query->paginate($perPage);

        return $this->successResponse($events);
    }

    public function showEvent(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $event = Event::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->with('creator:id,name_bn,name_en')
            ->with('registrations.user:id,name_bn,name_en')
            ->first();

        if (!$event) {
            return $this->errorResponse('আয়োজন পাওয়া যায়নি', 404);
        }

        return $this->successResponse($event);
    }

    public function storeEvent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title_bn' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'description_bn' => 'nullable|string',
            'description_en' => 'nullable|string',
            'event_date' => 'nullable|date',
            'start_time' => 'nullable|datetime',
            'end_time' => 'nullable|datetime',
            'location_bn' => 'nullable|string|max:255',
            'location_en' => 'nullable|string|max:255',
            'type' => 'nullable|in:উৎসব,সেমিনার,সামাজিক,ধর্মীয়,অনুষ্ঠান,মিছিল,অন্যান্য',
            'capacity' => 'nullable|integer',
            'is_public' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'attachments' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_public'] = $data['is_public'] ?? false;
        $data['created_by_user_id'] = $request->user()->id;

        $event = Event::create($data);

        $event->load('creator:id,name_bn,name_en');

        return $this->successResponse($event, 'আয়োজন তৈরি সফল', 201);
    }

    public function updateEvent(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $event = Event::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$event) {
            return $this->errorResponse('আয়োজন পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'title_bn' => 'nullable|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'description_bn' => 'nullable|string',
            'description_en' => 'nullable|string',
            'event_date' => 'nullable|date',
            'start_time' => 'nullable|datetime',
            'end_time' => 'nullable|datetime',
            'location_bn' => 'nullable|string|max:255',
            'location_en' => 'nullable|string|max:255',
            'type' => 'nullable|in:উৎসব,সেমিনার,সামাজিক,ধর্মীয়,অনুষ্ঠান,মিছিল,অন্যান্য',
            'capacity' => 'nullable|integer',
            'is_public' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'attachments' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $event->update($validator->validated());

        return $this->successResponse($event->fresh(), 'আয়োজন আপডেট সফল');
    }

    public function destroyEvent(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $event = Event::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$event) {
            return $this->errorResponse('আয়োজন পাওয়া যায়নি', 404);
        }

        $event->delete();

        return $this->successResponse(null, 'আয়োজন মুছে ফেলা সফল');
    }

    // ─── Event Registrations ──────────────────────────────────────────────────

    public function registrations(Request $request, int $eventId): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $event = Event::where('tenant_id', $user->tenant_id)
            ->where('id', $eventId)
            ->first();

        if (!$event) {
            return $this->errorResponse('আয়োজন পাওয়া যায়নি', 404);
        }

        $query = EventRegistration::where('event_id', $eventId)
            ->with('user:id,name_bn,name_en')
            ->orderBy('registered_at', 'desc');

        $registrations = $query->paginate($perPage);

        return $this->successResponse($registrations, 'নাম নিবন্ধন');
    }

    public function storeRegistration(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|integer|exists:events,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'guest_name_bn' => 'nullable|string|max:255',
            'guest_name_en' => 'nullable|string|max:255',
            'guest_phone' => 'nullable|string|max:50',
            'guest_email' => 'nullable|email',
            'number_of_guests' => 'nullable|integer|min:1',
            'special_requests' => 'nullable|string',
            'payment_status' => 'nullable|in:unpaid,paid,partial',
            'status' => 'nullable|in:registered,confirmed,cancelled,attended',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['status'] = $data['status'] ?? 'registered';
        $data['registered_at'] = $data['registered_at'] ?? now();

        $registration = EventRegistration::create($data);

        $registration->load('user:id,name_bn,name_en');

        return $this->successResponse($registration, 'নাম নিবন্ধন সফল', 201);
    }

    public function updateRegistration(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $registration = EventRegistration::find($id);
        if (!$registration || $registration->event->tenant_id !== $user->tenant_id) {
            return $this->errorResponse('নাম নিবন্ধন পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'guest_name_bn' => 'nullable|string|max:255',
            'guest_name_en' => 'nullable|string|max:255',
            'guest_phone' => 'nullable|string|max:50',
            'guest_email' => 'nullable|email',
            'number_of_guests' => 'nullable|integer|min:1',
            'special_requests' => 'nullable|string',
            'payment_status' => 'nullable|in:unpaid,paid,partial',
            'status' => 'nullable|in:registered,confirmed,cancelled,attended',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $registration->update($validator->validated());

        return $this->successResponse($registration->fresh(), 'নাম নিবন্ধন আপডেট সফল');
    }

    public function destroyRegistration(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $registration = EventRegistration::find($id);
        if (!$registration || $registration->event->tenant_id !== $user->tenant_id) {
            return $this->errorResponse('নাম নিবন্ধন পাওয়া যায়নি', 404);
        }

        $registration->delete();

        return $this->successResponse(null, 'নাম নিবন্ধন মুছে ফেলা সফল');
    }
}
