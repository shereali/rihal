<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TeacherController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Teacher::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sq) use ($search) {
                    $sq->whereHas('user', fn($u) => $u->where('name_bn', 'like', "%{$search}%"))
                        ->orWhere('designation', 'like', "%{$search}%");
                });
            })
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->with('user')
            ->orderBy('created_at', 'desc');

        $teachers = $query->paginate($perPage);

        return $this->successResponse($teachers);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $teacher = Teacher::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->with('user')
            ->first();

        if (!$teacher) {
            return $this->errorResponse('শিক্ষক পাওয়া যায়নি', 404);
        }

        return $this->successResponse($teacher);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'designation' => 'nullable|string|max:255',
            'join_date' => 'nullable|date',
            'leave_date' => 'nullable|date',
            'salary' => 'nullable|numeric',
            'qualifications' => 'nullable|array',
            'experience' => 'nullable|array',
            'subjects' => 'nullable|array',
            'classes' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;

        $teacher = Teacher::create($data);

        $teacher->load('user');

        return $this->successResponse($teacher, 'শিক্ষক তৈরি সফল', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $teacher = Teacher::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$teacher) {
            return $this->errorResponse('শিক্ষক পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|integer|exists:users,id',
            'designation' => 'nullable|string|max:255',
            'join_date' => 'nullable|date',
            'leave_date' => 'nullable|date',
            'salary' => 'nullable|numeric',
            'qualifications' => 'nullable|array',
            'experience' => 'nullable|array',
            'subjects' => 'nullable|array',
            'classes' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $teacher->update($validator->validated());

        $teacher->load('user');

        return $this->successResponse($teacher->fresh(), 'শিক্ষক আপডেট সফল');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $teacher = Teacher::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$teacher) {
            return $this->errorResponse('শিক্ষক পাওয়া যায়নি', 404);
        }

        $teacher->delete();

        return $this->successResponse(null, 'শিক্ষক মুছে ফেলা সফল');
    }
}
