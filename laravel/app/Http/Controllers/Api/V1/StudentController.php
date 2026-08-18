<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
                    $sq->whereHas('user', fn($u) => $u->where('name_bn', 'like', "%{$search}%"))
                        ->orWhere('roll_number', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%");
                });
            })
            ->when($request->has('class_id'), fn($q) => $q->where('class_id', $request->input('class_id')))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->with('user')
            ->orderBy('created_at', 'desc');

        $students = $query->paginate($perPage);

        return $this->successResponse($students);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $student = Student::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->with('user')
            ->first();

        if (!$student) {
            return $this->errorResponse('ছাত্র পাওয়া যায়নি', 404);
        }

        return $this->successResponse($student);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'roll_number' => 'nullable|string|max:50',
            'admission_number' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:ছেলে,মেয়ে,অন্যান্য',
            'blood_group' => 'nullable|string|max:5',
            'guardian_id' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;

        $student = Student::create($data);

        $student->load('user');

        return $this->successResponse($student, 'ছাত্র তৈরি সফল', 201);
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
            'user_id' => 'nullable|integer|exists:users,id',
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'roll_number' => 'nullable|string|max:50',
            'admission_number' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:ছেলে,মেয়ে,অন্যান্য',
            'blood_group' => 'nullable|string|max:5',
            'guardian_id' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $student->update($validator->validated());

        $student->load('user');

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
