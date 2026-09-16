<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StudentController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Student::where('tenant_id', $user->tenant_id)
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sq) use ($search) {
                    $sq->where('name_bn', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('mother_name', 'like', "%{$search}%")
                        ->orWhereHas('user', fn($u) => $u->where('name_bn', 'like', "%{$search}%")
                            ->orWhere('name_en', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('class_id'), fn($q) => $q->whereHas('enrollments', fn($e) => $e->where('class_id', $request->input('class_id'))))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('is_active'), function ($q) use ($request) {
                $isActive = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);
                $q->where('status', $isActive ? 'active' : 'inactive');
            })
            ->with(['user', 'enrollments.class'])
            ->orderBy('created_at', 'desc');

        $students = $query->paginate($perPage);

        return $this->successResponse($students);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->get('tenant')?->id;

        $student = Student::withTrashed()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('id', $id)
            ->with(['user', 'guardian', 'enrollments.class', 'enrollments.section', 'enrollments.session'])
            ->first();

        if (!$student) {
            return $this->errorResponse('ছাত্র পাওয়া যায়নি', 404);
        }

        return $this->successResponse($student);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name_bn' => 'required_without:user_id|nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'user_id' => 'nullable|integer|exists:users,id',
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'roll_number' => 'nullable|string|max:50',
            'admission_number' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:50',
            'blood_group' => 'nullable|string|max:10',
            'father_name' => 'nullable|string|max:255',
            'father_phone' => 'nullable|string|max:50',
            'mother_name' => 'nullable|string|max:255',
            'mother_phone' => 'nullable|string|max:50',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:50',
            'guardian_relation' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'address_bn' => 'nullable|string',
            'nationality' => 'nullable|string|max:100',
            'health_summary' => 'nullable|string|max:255',
            'guardian_id' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $tenantId = $request->user()->tenant_id;

        return DB::transaction(function () use ($data, $tenantId) {
            $userId = $data['user_id'] ?? null;
            $nameBn = $data['name_bn'] ?? '';
            $nameEn = $data['name_en'] ?? '';

            if (!$userId) {
                $email = !empty($data['email']) ? $data['email'] : 'student_' . time() . '_' . rand(100, 999) . '@rihal.local';
                $existingUser = User::where('tenant_id', $tenantId)->where('email', $email)->first();
                if ($existingUser) {
                    $userId = $existingUser->id;
                } else {
                    $user = User::create([
                        'tenant_id' => $tenantId,
                        'name_bn' => $nameBn ?: 'শিক্ষার্থী',
                        'name_en' => $nameEn,
                        'email' => $email,
                        'phone' => $data['phone'] ?? null,
                        'password' => Hash::make('student123'),
                        'role' => 'student',
                    ]);
                    $userId = $user->id;
                }
            } else {
                $user = User::find($userId);
                if ($user && empty($nameBn)) {
                    $nameBn = $user->name_bn;
                    $nameEn = $user->name_en;
                }
            }

            $admissionNumber = $data['admission_number'] ?? null;
            if (!$admissionNumber) {
                $year = date('Y');
                $seq = Student::withTrashed()->where('tenant_id', $tenantId)->whereYear('created_at', $year)->count() + 1;
                do {
                    $candidate = "ADM-{$year}-" . str_pad($seq, 4, '0', STR_PAD_LEFT);
                    $seq++;
                } while (Student::withTrashed()->where('tenant_id', $tenantId)->where('admission_number', $candidate)->exists());
                $admissionNumber = $candidate;
            }

            $studentData = [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'name_bn' => $nameBn ?: 'শিক্ষার্থী',
                'name_en' => $nameEn,
                'email' => $data['email'] ?? null,
                'admission_number' => $admissionNumber,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'father_name' => $data['father_name'] ?? null,
                'father_phone' => $data['father_phone'] ?? null,
                'mother_name' => $data['mother_name'] ?? null,
                'mother_phone' => $data['mother_phone'] ?? null,
                'guardian_name' => $data['guardian_name'] ?? null,
                'guardian_phone' => $data['guardian_phone'] ?? null,
                'guardian_relation' => $data['guardian_relation'] ?? null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'nationality' => $data['nationality'] ?? 'বাংলাদেশী',
                'health_summary' => $data['health_summary'] ?? null,
                'address_bn' => $data['address_bn'] ?? null,
                'status' => isset($data['is_active']) ? ($data['is_active'] ? 'active' : 'inactive') : 'active',
                'admission_date' => now(),
            ];

            $student = Student::create($studentData);

            // Create initial enrollment if class_id is present
            if (!empty($data['class_id'])) {
                $session = AcademicSession::where('tenant_id', $tenantId)->first();
                Enrollment::create([
                    'tenant_id' => $tenantId,
                    'student_id' => $userId,
                    'class_id' => $data['class_id'],
                    'section_id' => $data['section_id'] ?? null,
                    'session_id' => $session?->id,
                    'enrollment_number' => 'ENR-' . date('Y') . '-' . $student->id,
                    'enrollment_date' => now(),
                    'status' => 'enrolled',
                ]);
            }

            $student->load(['user', 'enrollments.class']);

            return $this->successResponse($student, 'ছাত্র তৈরি সফল', 201);
        });
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->get('tenant')?->id;

        $student = Student::withTrashed()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('id', $id)
            ->first();

        if (!$student) {
            return $this->errorResponse('ছাত্র পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'name_bn' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'user_id' => 'nullable|integer|exists:users,id',
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'roll_number' => 'nullable|string|max:50',
            'admission_number' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:50',
            'blood_group' => 'nullable|string|max:10',
            'father_name' => 'nullable|string|max:255',
            'father_phone' => 'nullable|string|max:50',
            'mother_name' => 'nullable|string|max:255',
            'mother_phone' => 'nullable|string|max:50',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:50',
            'guardian_relation' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'address_bn' => 'nullable|string',
            'nationality' => 'nullable|string|max:100',
            'health_summary' => 'nullable|string|max:255',
            'admission_date' => 'nullable|date',
            'guardian_id' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();

        $studentPayload = $data;
        if (isset($studentPayload['is_active'])) {
            $studentPayload['status'] = $studentPayload['is_active'] ? 'active' : 'inactive';
            unset($studentPayload['is_active']);
        }

        if (array_key_exists('health_summary', $data)) {
            if (is_string($data['health_summary']) && trim($data['health_summary']) !== '') {
                $studentPayload['health_summary'] = ['notes' => trim($data['health_summary'])];
            } else {
                $studentPayload['health_summary'] = null;
            }
        }

        if (array_key_exists('date_of_birth', $data)) {
            $studentPayload['date_of_birth'] = !empty($data['date_of_birth']) ? $data['date_of_birth'] : null;
        }
        if (array_key_exists('admission_date', $data)) {
            $studentPayload['admission_date'] = !empty($data['admission_date']) ? $data['admission_date'] : null;
        }

        $allowedStudentCols = [
            'name_bn', 'name_en', 'admission_number', 'date_of_birth', 'gender',
            'blood_group', 'photo_url', 'father_name', 'father_phone', 'mother_name',
            'mother_phone', 'guardian_name', 'guardian_phone', 'guardian_relation',
            'address_bn', 'email', 'emergency_contact_name', 'emergency_contact_phone',
            'nationality', 'health_summary', 'status', 'admission_date', 'graduation_date',
            'graduation_type', 'current_class', 'current_section',
        ];
        $filteredUpdates = array_intersect_key($studentPayload, array_flip($allowedStudentCols));

        $student->update($filteredUpdates);

        // Sync name/email/phone with associated user if provided
        if ($student->user) {
            $userUpdates = [];
            if (!empty($data['name_bn'])) $userUpdates['name_bn'] = $data['name_bn'];
            if (!empty($data['name_en'])) $userUpdates['name_en'] = $data['name_en'];
            if (!empty($data['phone'])) $userUpdates['phone'] = $data['phone'];
            if (!empty($data['email'])) $userUpdates['email'] = $data['email'];
            if (!empty($userUpdates)) {
                $student->user->update($userUpdates);
            }
        }

        // Sync or create guardian record
        if (!empty($data['guardian_name']) || !empty($data['guardian_phone'])) {
            \App\Models\StudentGuardian::updateOrCreate(
                ['student_id' => $student->id],
                [
                    'tenant_id' => $tenantId ?? $student->tenant_id,
                    'guardian_name' => $data['guardian_name'] ?? $student->guardian_name ?? 'অভিভাবক',
                    'phone' => $data['guardian_phone'] ?? $student->guardian_phone,
                    'relationship' => $data['guardian_relation'] ?? $student->guardian_relation ?? 'অভিভাবক',
                    'is_primary' => true,
                ]
            );
        }

        // Sync or create enrollment record if class_id is provided
        if (!empty($data['class_id'])) {
            $enrollment = Enrollment::where('tenant_id', $tenantId ?? $student->tenant_id)
                ->where('student_id', $student->user_id ?: $student->id)
                ->latest()
                ->first();

            if ($enrollment) {
                $enrollUpdates = ['class_id' => $data['class_id']];
                if (array_key_exists('section_id', $data)) $enrollUpdates['section_id'] = $data['section_id'];
                if (array_key_exists('roll_number', $data)) $enrollUpdates['roll_number'] = $data['roll_number'];
                $enrollment->update($enrollUpdates);
            } else {
                $session = AcademicSession::where('tenant_id', $tenantId ?? $student->tenant_id)->first();
                Enrollment::create([
                    'tenant_id' => $tenantId ?? $student->tenant_id,
                    'student_id' => $student->user_id ?: $student->id,
                    'class_id' => $data['class_id'],
                    'section_id' => $data['section_id'] ?? null,
                    'roll_number' => $data['roll_number'] ?? null,
                    'session_id' => $session?->id,
                    'enrollment_number' => 'ENR-' . date('Y') . '-' . $student->id,
                    'enrollment_date' => now(),
                    'status' => 'enrolled',
                ]);
            }
        }

        $student->load(['user', 'enrollments.class', 'enrollments.section']);

        return $this->successResponse($student->fresh(['user', 'enrollments.class', 'enrollments.section', 'guardian']), 'ছাত্র আপডেট সফল');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $student = Student::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$student) {
            return $this->errorResponse('ছাত্র পাওয়া যায়নি', 404);
        }

        $student->delete();

        return $this->successResponse(null, 'ছাত্র মুছে ফেলা সফল');
    }

    public function restore(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $student = Student::withTrashed()
            ->where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$student) {
            return $this->errorResponse('ছাত্র পাওয়া যায়নি', 404);
        }

        $student->restore();
        if ($student->user_id) {
            User::withTrashed()->where('id', $student->user_id)->restore();
        }

        return $this->successResponse($student->fresh()->load(['user', 'guardian', 'enrollments.class', 'enrollments.section', 'enrollments.session']), 'ছাত্র সফলভাবে পুনরুদ্ধার করা হয়েছে');
    }
}
