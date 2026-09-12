<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EnrollmentController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $tenantId = $user?->tenant_id;
            $perPage = min((int) $request->input('per_page', 15), 100);

            $query = Enrollment::query();
            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            }

            $query->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sq) use ($search) {
                    $sq->where('enrollment_number', 'like', "%{$search}%")
                        ->orWhereHas('student', function ($u) use ($search) {
                            $u->where('name_bn', 'like', "%{$search}%")
                                ->orWhere('name_en', 'like', "%{$search}%")
                                ->orWhere('father_phone', 'like', "%{$search}%")
                                ->orWhere('guardian_phone', 'like', "%{$search}%")
                                ->orWhereHas('user', fn($uq) => $uq->where('phone', 'like', "%{$search}%"));
                        });
                });
            })
            ->when($request->filled('student_id'), function ($q) use ($request) {
                $studentId = $request->input('student_id');
                $student = Student::find($studentId);
                $userId = $student?->user_id;
                $q->where(function ($sq) use ($studentId, $userId) {
                    $sq->where('student_id', $studentId);
                    if ($userId) {
                        $sq->orWhere('student_id', $userId);
                    }
                });
            })
            ->when($request->filled('class_id'), fn($q) => $q->where('class_id', $request->input('class_id')))
            ->when($request->filled('session_id'), fn($q) => $q->where('session_id', $request->input('session_id')))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('is_active'), fn($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->with(['student.user', 'class', 'section', 'session'])
            ->orderBy('enrollment_date', 'desc');

            $enrollments = $query->paginate($perPage);

            return $this->successResponse($enrollments);
        } catch (\Throwable $e) {
            Log::error('Enrollment index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->errorResponse('ভর্তি তথ্য লোড করতে সমস্যা হয়েছে: ' . $e->getMessage(), 500);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $enrollment = Enrollment::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->with(['student.user', 'class', 'section', 'session'])
            ->first();

        if (!$enrollment) {
            return $this->errorResponse('নাম নিবন্ধন পাওয়া যায়নি', 404);
        }

        return $this->successResponse($enrollment);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer',
            'class_id' => 'required|integer',
            'session_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'enrollment_date' => 'nullable|date',
            'enrollment_number' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,pending,completed,transferred,dropped,rejected,approved,enrolled',
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
        $tenantId = $request->user()->tenant_id;
        $data['tenant_id'] = $tenantId;
        $data['enrollment_date'] = $data['enrollment_date'] ?? today();
        $data['status'] = $data['status'] ?? 'active';
        $data['is_active'] = $data['is_active'] ?? true;

        // If a Student model ID was passed, map it to student's user_id for the enrollments foreign key
        $student = Student::find($data['student_id']);
        if ($student) {
            $data['student_id'] = $student->user_id;
        }

        // Validate foreign keys or fallback safely
        if (!empty($data['session_id'])) {
            $sessionExists = DB::table('academic_sessions')->where('id', $data['session_id'])->exists();
            if (!$sessionExists) {
                $data['session_id'] = DB::table('academic_sessions')->where('tenant_id', $tenantId)->value('id');
            }
        } else {
            $data['session_id'] = DB::table('academic_sessions')->where('tenant_id', $tenantId)->value('id');
        }

        if (!empty($data['class_id'])) {
            $classExists = DB::table('academic_classes')->where('id', $data['class_id'])->exists();
            if (!$classExists) {
                $data['class_id'] = DB::table('academic_classes')->where('tenant_id', $tenantId)->value('id');
            }
        }

        if (!empty($data['section_id'])) {
            $sectionExists = DB::table('academic_sections')->where('id', $data['section_id'])->exists();
            if (!$sectionExists) {
                $data['section_id'] = null;
            }
        }

        // Auto-generate enrollment number if not provided
        if (empty($data['enrollment_number'])) {
            $year = date('Y');
            $count = Enrollment::where('tenant_id', $data['tenant_id'])
                ->whereYear('enrollment_date', $year)
                ->count() + 1;
            $data['enrollment_number'] = "EN-$year-" . str_pad($count, 4, '0', STR_PAD_LEFT);
        }

        $enrollment = Enrollment::create($data);

        $enrollment->load(['student.user', 'class', 'section', 'session']);

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
            'status' => 'nullable|in:active,pending,completed,transferred,dropped,rejected,approved,enrolled',
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

        $enrollment->load(['student.user', 'class', 'section', 'session']);

        return $this->successResponse($enrollment->fresh(['student.user', 'class', 'section', 'session']), 'নাম নিবন্ধন আপডেট সফল');
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
