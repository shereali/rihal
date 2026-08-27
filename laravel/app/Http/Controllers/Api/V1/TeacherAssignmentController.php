<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TeacherAssignmentController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = TeacherAssignment::where('tenant_id', $user->tenant_id)
            ->when($request->has('teacher_id'), fn($q) => $q->where('teacher_id', $request->input('teacher_id')))
            ->when($request->has('class_id'), fn($q) => $q->where('class_id', $request->input('class_id')))
            ->when($request->has('subject_id'), fn($q) => $q->where('subject_id', $request->input('subject_id')))
            ->when($request->has('section_id'), fn($q) => $q->where('section_id', $request->input('section_id')))
            ->when($request->has('status'), fn($q) => $q->where('status', $request->input('status')))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->with('teacher:id,name_bn,name_en')
            ->with('class:id,name_bn,name_en')
            ->with('section:id,name_bn,name_en')
            ->with('subject:id,name_bn,name_en')
            ->orderBy('assigned_at', 'desc');

        $assignments = $query->paginate($perPage);

        return $this->successResponse($assignments);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $assignment = TeacherAssignment::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->with('teacher:id,name_bn,name_en')
            ->with('class:id,name_bn,name_en')
            ->with('section:id,name_bn,name_en')
            ->with('subject:id,name_bn,name_en')
            ->first();

        if (!$assignment) {
            return $this->errorResponse('শিক্ষক বরাদ্দ পাওয়া যায়নি', 404);
        }

        return $this->successResponse($assignment);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'teacher_id' => 'required|integer',
            'class_id' => 'required|integer',
            'section_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
            'session_id' => 'nullable|integer',
            'topic_bn' => 'nullable|string|max:255',
            'topic_en' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,paused',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['status'] = $data['status'] ?? 'active';
        $data['is_active'] = $data['is_active'] ?? true;
        $data['assigned_at'] = $data['assigned_at'] ?? now();

        // Resolve teacher record ID to user ID if necessary
        $teacher = \App\Models\Teacher::find($data['teacher_id']);
        if ($teacher && $teacher->user_id) {
            $data['teacher_id'] = $teacher->user_id;
        }

        $assignment = TeacherAssignment::create($data);

        $assignment->load('teacher:id,name_bn,name_en');
        $assignment->load('class:id,name_bn,name_en');
        $assignment->load('subject:id,name_bn,name_en');

        return $this->successResponse($assignment, 'শিক্ষক বরাদ্দ সফল', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $assignment = TeacherAssignment::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$assignment) {
            return $this->errorResponse('শিক্ষক বরাদ্দ পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
            'session_id' => 'nullable|integer',
            'topic_bn' => 'nullable|string|max:255',
            'topic_en' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,paused',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $assignment->update($validator->validated());

        $assignment->load('teacher:id,name_bn,name_en');
        $assignment->load('class:id,name_bn,name_en');

        return $this->successResponse($assignment->fresh(), 'শিক্ষক বরাদ্দ আপডেট সফল');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $assignment = TeacherAssignment::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$assignment) {
            return $this->errorResponse('শিক্ষক বরাদ্দ পাওয়া যায়নি', 404);
        }

        $assignment->delete();

        return $this->successResponse(null, 'শিক্ষক বরাদ্দ মুছে ফেলা সফল');
    }

    public function teacherSchedule(Request $request, int $teacherId): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $teacher = \App\Models\Teacher::where('tenant_id', $user->tenant_id)
            ->where('id', $teacherId)
            ->first();

        if (!$teacher) {
            return $this->errorResponse('শিক্ষক পাওয়া যায়নি', 404);
        }

        $query = TeacherAssignment::where('tenant_id', $user->tenant_id)
            ->where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->with('class:id,name_bn,name_en')
            ->with('section:id,name_bn,name_en')
            ->with('subject:id,name_bn,name_en')
            ->orderBy('topic_bn');

        $assignments = $query->paginate($perPage);

        return $this->successResponse($assignments, 'শিক্ষকের কাজের তালিকা');
    }
}
