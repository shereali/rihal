<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\PropertyMaintenance;
use App\Models\PropertyVisitor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PropertyController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Property::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->where('property_name_bn', 'like', "%{$request->input('search')}%"))
            ->when($request->has('property_type'), fn($q) => $q->where('property_type', $request->input('property_type')))
            ->when($request->has('status'), fn($q) => $q->where('status', $request->input('status')))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->orderBy('property_name_bn');

        $properties = $query->paginate($perPage);

        return $this->successResponse($properties);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $property = Property::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->with('documents')
            ->with('maintenanceRecords')
            ->first();

        if (!$property) {
            return $this->errorResponse('সম্পত্তি পাওয়া যায়নি', 404);
        }

        return $this->successResponse($property);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_name_bn' => 'required|string|max:255',
            'property_name_en' => 'nullable|string|max:255',
            'property_type' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'sub_category' => 'nullable|string|max:100',
            'location_address_bn' => 'nullable|string|max:500',
            'location_address_en' => 'nullable|string|max:500',
            'land_area_sqft' => 'nullable|numeric',
            'built_up_area_sqft' => 'nullable|numeric',
            'status' => 'nullable|in:owned,rented,leased,under_construction,completed,sold,damaged',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric',
            'current_market_value' => 'nullable|numeric',
            'registration_number' => 'nullable|string|max:100',
            'registration_date' => 'nullable|date',
            'land_record_number' => 'nullable|string|max:100',
            'block_number' => 'nullable|string|max:100',
            'mouza' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'zone' => 'nullable|string|max:100',
            'rental_amount' => 'nullable|numeric',
            'rental_start_date' => 'nullable|date',
            'rental_end_date' => 'nullable|date',
            'tenant_name' => 'nullable|string|max:255',
            'tenant_contact' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:100',
            'insurance_expiry' => 'nullable|date',
            'availability_status' => 'nullable|in:available,occupied,under_maintenance,locked',
            'floor_count' => 'nullable|integer',
            'room_count' => 'nullable|integer',
            'construction_year' => 'nullable|integer',
            'owner_name' => 'nullable|string|max:255',
            'owner_contact' => 'nullable|string|max:255',
            'share_percentage' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
            'documents' => 'nullable|json',
            'images' => 'nullable|json',
            'amenities' => 'nullable|json',
            'description_bn' => 'nullable|string',
            'description_en' => 'nullable|string',
            'property_number' => 'nullable|string|max:50',
            'color_code' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['is_active'] = $data['is_active'] ?? true;

        $property = Property::create($data);

        return $this->successResponse($property, 'সম্পত্তি তৈরি সফল', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $property = Property::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$property) {
            return $this->errorResponse('সম্পত্তি পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'property_name_bn' => 'nullable|string|max:255',
            'property_name_en' => 'nullable|string|max:255',
            'property_type' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'sub_category' => 'nullable|string|max:100',
            'location_address_bn' => 'nullable|string|max:500',
            'location_address_en' => 'nullable|string|max:500',
            'land_area_sqft' => 'nullable|numeric',
            'built_up_area_sqft' => 'nullable|numeric',
            'status' => 'nullable|in:owned,rented,leased,under_construction,completed,sold,damaged',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric',
            'current_market_value' => 'nullable|numeric',
            'registration_number' => 'nullable|string|max:100',
            'registration_date' => 'nullable|date',
            'land_record_number' => 'nullable|string|max:100',
            'block_number' => 'nullable|string|max:100',
            'mouza' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'zone' => 'nullable|string|max:100',
            'rental_amount' => 'nullable|numeric',
            'rental_start_date' => 'nullable|date',
            'rental_end_date' => 'nullable|date',
            'tenant_name' => 'nullable|string|max:255',
            'tenant_contact' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:100',
            'insurance_expiry' => 'nullable|date',
            'availability_status' => 'nullable|in:available,occupied,under_maintenance,locked',
            'floor_count' => 'nullable|integer',
            'room_count' => 'nullable|integer',
            'construction_year' => 'nullable|integer',
            'owner_name' => 'nullable|string|max:255',
            'owner_contact' => 'nullable|string|max:255',
            'share_percentage' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
            'description_bn' => 'nullable|string',
            'description_en' => 'nullable|string',
            'property_number' => 'nullable|string|max:50',
            'color_code' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $property->update($validator->validated());

        return $this->successResponse($property->fresh(), 'সম্পত্তি আপডেট সফল');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $property = Property::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$property) {
            return $this->errorResponse('সম্পত্তি পাওয়া যায়নি', 404);
        }

        $property->delete();

        return $this->successResponse(null, 'সম্পত্তি মুছে ফেলা সফল');
    }

    // ─── Documents ────────────────────────────────────────────────────────────

    public function documents(Request $request, int $propertyId): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $property = Property::where('tenant_id', $user->tenant_id)
            ->where('id', $propertyId)
            ->first();

        if (!$property) {
            return $this->errorResponse('সম্পত্তি পাওয়া যায়নি', 404);
        }

        $query = PropertyDocument::where('property_id', $propertyId)
            ->when($request->has('type'), fn($q) => $q->where('document_type', $request->input('type')))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->orderBy('created_at', 'desc');

        $documents = $query->paginate($perPage);

        return $this->successResponse($documents, 'সম্পত্তি ডকুমেন্ট');
    }

    public function storeDocument(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|integer|exists:properties,id',
            'document_type' => 'nullable|string|max:100',
            'document_title_bn' => 'required|string|max:255',
            'document_title_en' => 'nullable|string|max:255',
            'document_url' => 'nullable|string|max:500',
            'file_path' => 'nullable|string|max:500',
            'file_size' => 'nullable|integer',
            'mime_type' => 'nullable|string|max:100',
            'description_bn' => 'nullable|string',
            'description_en' => 'nullable|string',
            'uploaded_by_user_id' => 'nullable|integer|exists:users,id',
            'expiry_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['uploaded_by_user_id'] = $data['uploaded_by_user_id'] ?? $request->user()->id;
        $data['is_active'] = $data['is_active'] ?? true;

        $document = PropertyDocument::create($data);

        return $this->successResponse($document, 'সম্পত্তি ডকুমেন্ট তৈরি সফল', 201);
    }

    public function updateDocument(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $document = PropertyDocument::where('property_id', function ($q) use ($user) {
            $q->select('tenant_id')->from('properties')->where('id', $document->property_id)->where('tenant_id', $user->tenant_id);
        })->first();

        $document = PropertyDocument::find($id);
        if (!$document || $document->property->tenant_id !== $user->tenant_id) {
            return $this->errorResponse('ডকুমেন্ট পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'document_type' => 'nullable|string|max:100',
            'document_title_bn' => 'nullable|string|max:255',
            'document_title_en' => 'nullable|string|max:255',
            'document_url' => 'nullable|string|max:500',
            'file_path' => 'nullable|string|max:500',
            'description_bn' => 'nullable|string',
            'description_en' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $document->update($validator->validated());

        return $this->successResponse($document->fresh(), 'সম্পত্তি ডকুমেন্ট আপডেট সফল');
    }

    public function destroyDocument(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $document = PropertyDocument::find($id);
        if (!$document || $document->property->tenant_id !== $user->tenant_id) {
            return $this->errorResponse('ডকুমেন্ট পাওয়া যায়নি', 404);
        }

        $document->delete();

        return $this->successResponse(null, 'সম্পত্তি ডকুমেন্ট মুছে ফেলা সফল');
    }

    // ─── Maintenance ──────────────────────────────────────────────────────────

    public function maintenance(Request $request, int $propertyId): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $property = Property::where('tenant_id', $user->tenant_id)
            ->where('id', $propertyId)
            ->first();

        if (!$property) {
            return $this->errorResponse('সম্পত্তি পাওয়া যায়নি', 404);
        }

        $query = PropertyMaintenance::where('property_id', $propertyId)
            ->when($request->has('maintenance_type'), fn($q) => $q->where('maintenance_type', $request->input('maintenance_type')))
            ->when($request->has('status'), fn($q) => $q->where('status', $request->input('status')))
            ->when($request->has('from_date'), fn($q) => $q->where('maintenance_date', '>=', $request->input('from_date')))
            ->when($request->has('to_date'), fn($q) => $q->where('maintenance_date', '<=', $request->input('to_date')))
            ->with('assignedBy:id,name_bn,name_en')
            ->with('completedBy:id,name_bn,name_en')
            ->orderBy('maintenance_date', 'desc');

        $maintenance = $query->paginate($perPage);

        return $this->successResponse($maintenance, 'সম্পত্তি মেইনটেইনেন্স');
    }

    public function storeMaintenance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|integer|exists:properties,id',
            'maintenance_type' => 'nullable|string|max:100',
            'description_bn' => 'required|string|max:500',
            'description_en' => 'nullable|string|max:500',
            'maintenance_date' => 'nullable|date',
            'estimated_cost' => 'nullable|numeric',
            'actual_cost' => 'nullable|numeric',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'assigned_to_user_id' => 'nullable|integer|exists:users,id',
            'completed_by_user_id' => 'nullable|integer|exists:users,id',
            'completion_date' => 'nullable|date',
            'notes_bn' => 'nullable|string',
            'notes_en' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['assigned_by_user_id'] = $request->user()->id;
        $data['is_active'] = $data['is_active'] ?? true;
        $data['status'] = $data['status'] ?? 'pending';

        $maintenance = PropertyMaintenance::create($data);

        $maintenance->load('assignedBy:id,name_bn,name_en');

        return $this->successResponse($maintenance, 'মেইনটেইনেন্স রেকর্ড তৈরি সফল', 201);
    }

    public function updateMaintenance(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $maintenance = PropertyMaintenance::find($id);
        if (!$maintenance || $maintenance->property->tenant_id !== $user->tenant_id) {
            return $this->errorResponse('মেইনটেইনেন্স রেকর্ড পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'maintenance_type' => 'nullable|string|max:100',
            'description_bn' => 'nullable|string|max:500',
            'description_en' => 'nullable|string|max:500',
            'maintenance_date' => 'nullable|date',
            'estimated_cost' => 'nullable|numeric',
            'actual_cost' => 'nullable|numeric',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'assigned_to_user_id' => 'nullable|integer|exists:users,id',
            'completed_by_user_id' => 'nullable|integer|exists:users,id',
            'completion_date' => 'nullable|date',
            'notes_bn' => 'nullable|string',
            'notes_en' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $maintenance->update($validator->validated());

        return $this->successResponse($maintenance->fresh(), 'মেইনটেইনেন্স রেকর্ড আপডেট সফল');
    }

    public function destroyMaintenance(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $maintenance = PropertyMaintenance::find($id);
        if (!$maintenance || $maintenance->property->tenant_id !== $user->tenant_id) {
            return $this->errorResponse('মেইনটেইনেন্স রেকর্ড পাওয়া যায়নি', 404);
        }

        $maintenance->delete();

        return $this->successResponse(null, 'মেইনটেইনেন্স রেকর্ড মুছে ফেলা সফল');
    }

    // ─── Visitors ──────────────────────────────────────────────────────────────

    public function visitors(Request $request, int $propertyId): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $property = Property::where('tenant_id', $user->tenant_id)
            ->where('id', $propertyId)
            ->first();

        if (!$property) {
            return $this->errorResponse('সম্পত্তি পাওয়া যায়নি', 404);
        }

        $query = PropertyVisitor::where('property_id', $propertyId)
            ->when($request->has('visitor_name'), fn($q) => $q->where('visitor_name_bn', 'like', "%{$request->input('visitor_name')}%"))
            ->when($request->has('from_date'), fn($q) => $q->where('visit_date', '>=', $request->input('from_date')))
            ->when($request->has('to_date'), fn($q) => $q->where('visit_date', '<=', $request->input('to_date')))
            ->orderBy('visit_date', 'desc');

        $visitors = $query->paginate($perPage);

        return $this->successResponse($visitors, 'সম্পত্তি দর্শনার্থী');
    }

    public function storeVisitor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|integer|exists:properties,id',
            'visitor_name_bn' => 'required|string|max:255',
            'visitor_name_en' => 'nullable|string|max:255',
            'visitor_type' => 'nullable|string|max:100',
            'visitor_relation' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email',
            'visit_date' => 'nullable|date',
            'visit_time' => 'nullable|datetime',
            'departure_time' => 'nullable|datetime',
            'purpose' => 'nullable|string|max:500',
            'remarks' => 'nullable|string',
            'issuing_officer_id' => 'nullable|integer|exists:users,id',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['is_active'] = $data['is_active'] ?? true;

        $visitor = PropertyVisitor::create($data);

        return $this->successResponse($visitor, 'সম্পত্তি দর্শনার্থী রেকর্ড সফল', 201);
    }

    public function updateVisitor(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $visitor = PropertyVisitor::find($id);
        if (!$visitor || $visitor->property->tenant_id !== $user->tenant_id) {
            return $this->errorResponse('দর্শনার্থী রেকর্ড পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'departure_time' => 'nullable|datetime',
            'purpose' => 'nullable|string|max:500',
            'remarks' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $visitor->update($validator->validated());

        return $this->successResponse($visitor->fresh(), 'দর্শনার্থী রেকর্ড আপডেট সফল');
    }

    public function destroyVisitor(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $visitor = PropertyVisitor::find($id);
        if (!$visitor || $visitor->property->tenant_id !== $user->tenant_id) {
            return $this->errorResponse('দর্শনার্থী রেকর্ড পাওয়া যায়নি', 404);
        }

        $visitor->delete();

        return $this->successResponse(null, 'দর্শনার্থী রেকর্ড মুছে ফেলা সফল');
    }
}
