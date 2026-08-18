<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class HomeworkController extends ApiController
{
    // ─── Assignments ───────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = \App\Models\HomeworkAssignment::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->where('title_bn', 'like', "%{$request->input('search')}%"))
            ->when($request->has('class_id'), fn($q) => $q->where('class_id', $request->input('class_id')))
            ->when($request->has('subject_id'), fn($q) => $q->where('subject_id', $request->input('subject_id')))
            ->when($request->has('teacher_id'), fn($q) => $q->where('teacher_id', $request->input('teacher_id')))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->when($request->has('due_date'), fn($q) => $q->where('due_date', $request->input('due_date')))
            ->with('teacher.user:id,name_bn,name_en')
            ->with('class:id,name_bn,name_en')
            ->with('subject:id,name_bn,name_en')
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc');

        $assignments = $query->paginate($perPage);

        return $this->successResponse($assignments);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $assignment = \App\Models\HomeworkAssignment::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->with('teacher.user:id,name_bn,name_en')
            ->with('class:id,name_bn,name_en')
            ->with('subject:id,name_bn,name_en')
            ->with('submissions.student.user:id,name_bn,name_en')
            ->first();

        if (!$assignment) {
            return $this->errorResponse('গৃহকাজ পাওয়া যায়নি', 404);
        }

        return $this->successResponse($assignment);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title_bn' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'description_bn' => 'nullable|string',
            'description_en' => 'nullable|string',
            'teacher_id' => 'nullable|integer|exists:teachers,id',
            'class_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|datetime',
            'is_active' => 'nullable|boolean',
            'attachments' => 'nullable|array',
            'max_score' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['is_active'] = $data['is_active'] ?? true;

        $assignment = \App\Models\HomeworkAssignment::create($data);

        $assignment->load('teacher.user:id,name_bn,name_en');
        $assignment->load('class:id,name_bn,name_en');
        $assignment->load('subject:id,name_bn,name_en');

        return $this->successResponse($assignment, 'গৃহকাজ তৈরি সফল', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $assignment = \App\Models\HomeworkAssignment::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$assignment) {
            return $this->errorResponse('গৃহকাজ পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'title_bn' => 'nullable|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'description_bn' => 'nullable|string',
            'description_en' => 'nullable|string',
            'teacher_id' => 'nullable|integer|exists:teachers,id',
            'class_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|datetime',
            'is_active' => 'nullable|boolean',
            'attachments' => 'nullable|array',
            'max_score' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $assignment->update($validator->validated());

        $assignment->load('teacher.user:id,name_bn,name_en');

        return $this->successResponse($assignment->fresh(), 'গৃহকাজ আপডেট সফল');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $assignment = \App\Models\HomeworkAssignment::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$assignment) {
            return $this->errorResponse('গৃহকাজ পাওয়া যায়নি', 404);
        }

        $assignment->delete();

        return $this->successResponse(null, 'গৃহকাজ মুছে ফেলা সফল');
    }

    // ─── Submissions ──────────────────────────────────────────────────────────

    public function submissions(Request $request, int $assignmentId): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $assignment = \App\Models\HomeworkAssignment::where('tenant_id', $user->tenant_id)
            ->where('id', $assignmentId)
            ->first();

        if (!$assignment) {
            return $this->errorResponse('গৃহকাজ পাওয়া যায়নি', 404);
        }

        $query = \App\Models\HomeworkSubmission::where('tenant_id', $user->tenant_id)
            ->where('assignment_id', $assignmentId)
            ->with('student.user:id,name_bn,name_en')
            ->orderBy('submitted_at', 'desc');

        $submissions = $query->paginate($perPage);

        return $this->successResponse($submissions, 'গৃহকাজ জমা');
    }

    public function storeSubmission(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'assignment_id' => 'required|integer|exists:homework_assignments,id',
            'student_id' => 'required|integer|exists:students,id',
            'content_bn' => 'nullable|string',
            'content_en' => 'nullable|string',
            'attachment_url' => 'nullable|string|max:500',
            'score' => 'nullable|integer',
            'feedback_bn' => 'nullable|string',
            'feedback_en' => 'nullable|string',
            'is_submitted' => 'nullable|boolean',
            'is_graded' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['is_submitted'] = $data['is_submitted'] ?? true;
        $data['submitted_at'] = $data['submitted_at'] ?? now();

        $submission = \App\Models\HomeworkSubmission::create($data);

        $submission->load('student.user:id,name_bn,name_en');

        return $this->successResponse($submission, 'গৃহকাজ জমা সফল', 201);
    }

    public function updateSubmission(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $submission = \App\Models\HomeworkSubmission::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$submission) {
            return $this->errorResponse('গৃহকাজ জমা পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'content_bn' => 'nullable|string',
            'content_en' => 'nullable|string',
            'attachment_url' => 'nullable|string|max:500',
            'score' => 'nullable|integer',
            'feedback_bn' => 'nullable|string',
            'feedback_en' => 'nullable|string',
            'is_graded' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $submission->update($validator->validated());

        return $this->successResponse($submission->fresh(), 'গৃহকাজ জমা আপডেট সফল');
    }

    // ─── Lesson Plans ─────────────────────────────────────────────────────────

    public function lessonPlans(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = \App\Models\LessonPlan::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->where('topic_bn', 'like', "%{$request->input('search')}%"))
            ->when($request->has('class_id'), fn($q) => $q->where('class_id', $request->input('class_id')))
            ->when($request->has('subject_id'), fn($q) => $q->where('subject_id', $request->input('subject_id')))
            ->when($request->has('teacher_id'), fn($q) => $q->where('teacher_id', $request->input('teacher_id')))
            ->with('teacher.user:id,name_bn,name_en')
            ->with('class:id,name_bn,name_en')
            ->with('subject:id,name_bn,name_en')
            ->orderBy('class_date', 'desc');

        $plans = $query->paginate($perPage);

        return $this->successResponse($plans);
    }

    public function storeLessonPlan(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'topic_bn' => 'required|string|max:255',
            'topic_en' => 'nullable|string|max:255',
            'teacher_id' => 'nullable|integer|exists:teachers,id',
            'class_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
            'class_date' => 'nullable|date',
            'start_time' => 'nullable|datetime',
            'end_time' => 'nullable|datetime',
            'content_bn' => 'nullable|string',
            'content_en' => 'nullable|string',
            'objectives' => 'nullable|array',
            'materials' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['is_active'] = $data['is_active'] ?? true;

        $plan = \App\Models\LessonPlan::create($data);

        $plan->load('teacher.user:id,name_bn,name_en');

        return $this->successResponse($plan, 'পাঠ পরিকল্পনা তৈরি সফল', 201);
    }

    public function updateLessonPlan(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $plan = \App\Models\LessonPlan::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$plan) {
            return $this->errorResponse('পাঠ পরিকল্পনা পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'topic_bn' => 'nullable|string|max:255',
            'topic_en' => 'nullable|string|max:255',
            'teacher_id' => 'nullable|integer|exists:teachers,id',
            'class_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
            'class_date' => 'nullable|date',
            'start_time' => 'nullable|datetime',
            'end_time' => 'nullable|datetime',
            'content_bn' => 'nullable|string',
            'content_en' => 'nullable|string',
            'objectives' => 'nullable|array',
            'materials' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $plan->update($validator->validated());

        return $this->successResponse($plan->fresh(), 'পাঠ পরিকল্পনা আপডেট সফল');
    }

    public function destroyLessonPlan(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $plan = \App\Models\LessonPlan::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$plan) {
            return $this->errorResponse('পাঠ পরিকল্পনা পাওয়া যায়নি', 404);
        }

        $plan->delete();

        return $this->successResponse(null, 'পাঠ পরিকল্পনা মুছে ফেলা সফল');
    }
}
