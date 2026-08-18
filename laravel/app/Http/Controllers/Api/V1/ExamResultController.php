<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Result;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ExamResultController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Result::where('tenant_id', $user->tenant_id)
            ->when($request->has('exam_id'), fn($q) => $q->where('exam_id', $request->input('exam_id')))
            ->when($request->has('session_id'), fn($q) => $q->where('session_id', $request->input('session_id')))
            ->when($request->has('class_id'), fn($q) => $q->whereHas('exam', fn($e) => $e->where('class_id', $request->input('class_id'))))
            ->when($request->has('is_published'), fn($q) => $q->where('is_published', filter_var($request->input('is_published'), FILTER_VALIDATE_BOOLEAN)))
            ->when($request->has('from_date'), fn($q) => $q->where('published_at', '>=', $request->input('from_date')))
            ->when($request->has('to_date'), fn($q) => $q->where('published_at', '<=', $request->input('to_date')))
            ->with('exam')
            ->with('student:id,name_bn,name_en')
            ->with('session:id,name_bn,name_en')
            ->orderBy('published_at', 'desc')
            ->orderBy('percentage', 'desc');

        $results = $query->paginate($perPage);

        return $this->successResponse($results, 'ফলাফল তালিকা');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $result = Result::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->with('exam')
            ->with('student:id,name_bn,name_en')
            ->with('session:id,name_bn,name_en')
            ->first();

        if (!$result) {
            return $this->errorResponse('ফলাফল পাওয়া যায়নি', 404);
        }

        return $this->successResponse($result);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|integer|exists:exams,id',
            'student_id' => 'required|integer|exists:users,id',
            'session_id' => 'nullable|integer',
            'marks_obtained' => 'nullable|numeric',
            'total_marks' => 'nullable|numeric',
            'gpa' => 'nullable|numeric',
            'percentage' => 'nullable|numeric',
            'grade' => 'nullable|string|max:10',
            'class_position' => 'nullable|integer',
            'subject_results' => 'nullable|array',
            'is_published' => 'nullable|boolean',
            'published_at' => 'nullable|datetime',
            'parent_notified' => 'nullable|boolean',
            'comments' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['is_published'] = $data['is_published'] ?? false;

        $result = Result::create($data);

        $result->load('exam');
        $result->load('student:id,name_bn,name_en');

        return $this->successResponse($result, 'ফলাফল তৈরি সফল', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $result = Result::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$result) {
            return $this->errorResponse('ফলাফল পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'marks_obtained' => 'nullable|numeric',
            'total_marks' => 'nullable|numeric',
            'gpa' => 'nullable|numeric',
            'percentage' => 'nullable|numeric',
            'grade' => 'nullable|string|max:10',
            'class_position' => 'nullable|integer',
            'subject_results' => 'nullable|array',
            'is_published' => 'nullable|boolean',
            'published_at' => 'nullable|datetime',
            'parent_notified' => 'nullable|boolean',
            'comments' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $result->update($validator->validated());

        $result->load('exam');

        return $this->successResponse($result->fresh(), 'ফলাফল আপডেট সফল');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $result = Result::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$result) {
            return $this->errorResponse('ফলাফল পাওয়া যায়নি', 404);
        }

        $result->delete();

        return $this->successResponse(null, 'ফলাফল মুছে ফেলা সফল');
    }

    public function publish(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $result = Result::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$result) {
            return $this->errorResponse('ফলাফল পাওয়া যায়নি', 404);
        }

        $result->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        return $this->successResponse($result->fresh(), 'ফলাফল প্রকাশিত হয়েছে');
    }

    public function unpublish(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $result = Result::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$result) {
            return $this->errorResponse('ফলাফল পাওয়া যায়নি', 404);
        }

        $result->update([
            'is_published' => false,
            'published_at' => null,
        ]);

        return $this->successResponse($result->fresh(), 'ফলাফল সরানো হয়েছে');
    }
}
