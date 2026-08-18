<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class EnrollmentController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Enrollment::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->where('enrollment_number', 'like', "%{$request->input('search')}%"))
            ->when($request->has('student_id'), fn($q) => $q->where('student_id', $request->input('student_id')))
            ->when($request->has('class_id'), fn($q) => $q->where('class_id', $request->input('class_id')))
            ->when($request->has('session_id'), fn($q) => $q->where('session_id', $request->input('session_id')))
            ->when($request->has('status'), fn($q) => $q->where('status', $request->input('status')))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->with('student:id,name_bn,name_en')
            ->with('class:id,name_bn,name_en')
            ->with('section:id,name_bn,name_en')
            ->with('session:id,name_bn,name_en')
            ->orderBy('enrollment_date', 'desc');

        $enrollments = $query->paginate($perPage);

        return $this->successResponse($enrollments);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $enrollment = Enrollment::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->with('student:id,name_bn,name_en')
            ->with('class:id,name_bn,name_en')
            ->with('section:id,name_bn,name_en')
            ->with('session:id,name_bn,name_en')
            ->first();

        if (!$enrollment) {
            return $this->errorResponse('নাম নিবন্ধন পাওয়া যায়নি', 404);
        }

        return $this->successResponse($enrollment);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'class_id' => 'required|integer',
            'session_id' => 'required|integer',
            'section_id' => 'nullable|integer',
            'enrollment_date' => 'nullable|date',
            'enrollment_number' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,pending,completed,transferred,dropped',
            'admission_type' => 'nullable|in:regular,transfer,religious,special',
            'previous_school' => 'nullable|string|max:255',
            'previous_board' => 'nullable|string|max:100',
            'passing_year' => 'nullable|integer',
            'remarks_bn' => 'nullable|string|max:500',
            'remarks_en' => 'nullable|string|max:500',
            'documents' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['enrollment_date'] = $data['enrollment_date'] ?? today();
        $data['status'] = $data['status'] ?? 'active';
        $data['is_active'] = $data['is_active'] ?? true;

        // Auto-generate enrollment number if not provided
        if (empty($data['enrollment_number'])) {
            $year = date('Y');
            $count = Enrollment::where('tenant_id', $data['tenant_id'])
                ->whereYear('enrollment_date', $year)
                ->count() + 1;
            $data['enrollment_number'] = "EN-$year-" . str_pad($count, 4, '0', STR_PAD_LEFT);
        }

        $enrollment = Enrollment::create($data);

        $enrollment->load('student:id,name_bn,name_en');
        $enrollment->load('class:id,name_bn,name_en');
        $enrollment->load('session:id,name_bn,name_en');

        return $this->successResponse($enrollment, 'নাম নিবন্ধন সফল', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $enrollment = Enrollment::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$enrollment) {
            return $this->errorResponse('নাম নিবন্ধন পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'class_id' => 'nullable|integer',
            'session_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'enrollment_date' => 'nullable|date',
            'status' => 'nullable|in:active,pending,completed,transferred,dropped',
            'admission_type' => 'nullable|in:regular,transfer,religious,special',
            'previous_school' => 'nullable|string|max:255',
            'previous_board' => 'nullable|string|max:100',
            'passing_year' => 'nullable|integer',
            'remarks_bn' => 'nullable|string|max:500',
            'remarks_en' => 'nullable|string|max:500',
            'documents' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $enrollment->update($validator->validated());

        $enrollment->load('student:id,name_bn,name_en');
        $enrollment->load('class:id,name_bn,name_en');

        return $this->successResponse($enrollment->fresh(), 'নাম নিবন্ধন আপডেট সফল');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $enrollment = Enrollment::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$enrollment) {
            return $this->errorResponse('নাম নিবন্ধন পাওয়া যায়নি', 404);
        }

        $enrollment->delete();

        return $this->successResponse(null, 'নাম নিবন্ধন মুছে ফেলা সফল');
    }

    public function transfer(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $enrollment = Enrollment::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$enrollment) {
            return $this->errorResponse('নাম নিবন্ধন পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'new_class_id' => 'nullable|integer',
            'new_section_id' => 'nullable|integer',
            'transfer_date' => 'nullable|date',
            'transfer_reason_bn' => 'nullable|string|max:500',
            'transfer_reason_en' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();

        $enrollment->update(array_filter($data, fn($v) => $v !== null));

        return $this->successResponse($enrollment->fresh(), 'ট্রান্সফার আপডেট সফল');
    }
}
