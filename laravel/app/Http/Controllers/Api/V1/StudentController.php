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
            ->when($request->has('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sq) use ($search) {
                    $sq->where('name_bn', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%")
                        ->orWhereHas('user', fn($u) => $u->where('name_bn', 'like', "%{$search}%")
                            ->orWhere('name_en', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhere('roll_number', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%");
                });
            })
            ->when($request->has('class_id'), fn($q) => $q->whereHas('enrollments', fn($e) => $e->where('class_id', $request->input('class_id'))))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->with(['user', 'enrollments.class'])
            ->orderBy('created_at', 'desc');

        $students = $query->paginate($perPage);

        return $this->successResponse($students);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $student = Student::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->with(['user', 'guardian', 'enrollments.class', 'enrollments.section'])
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
            'mother_name' => 'nullable|string|max:255',
            'guardian_name' => 'nullable|string|max:255',
            'address_bn' => 'nullable|string',
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

            $studentData = [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'name_bn' => $nameBn ?: 'শিক্ষার্থী',
                'name_en' => $nameEn,
                'email' => $data['email'] ?? null,
                'admission_number' => $data['admission_number'] ?? ('ADM-' . date('Y') . '-' . rand(1000, 9999)),
                'roll_number' => $data['roll_number'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'father_name' => $data['father_name'] ?? null,
                'mother_name' => $data['mother_name'] ?? null,
                'guardian_name' => $data['guardian_name'] ?? null,
                'address_bn' => $data['address_bn'] ?? null,
                'guardian_id' => $data['guardian_id'] ?? null,
                'status' => 'active',
                'admission_date' => now(),
                'is_active' => $data['is_active'] ?? true,
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
                    'session_id' => $session?->id ?? 1,
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
        $user = $request->user();

        $student = Student::where('tenant_id', $user->tenant_id)
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
            'mother_name' => 'nullable|string|max:255',
            'guardian_name' => 'nullable|string|max:255',
            'address_bn' => 'nullable|string',
            'guardian_id' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();

        $student->update($data);

        // Sync name/email with associated user if provided
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

        $student->load(['user', 'enrollments.class']);

        return $this->successResponse($student->fresh(), 'ছাত্র আপডেট সফল');
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
}
