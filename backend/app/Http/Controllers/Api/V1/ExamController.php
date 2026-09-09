<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ExamController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Exam::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where('title_bn', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%");
            })
            ->when($request->has('session_id'), fn($q) => $q->where('session_id', $request->input('session_id')))
            ->when($request->has('class_id'), fn($q) => $q->where('class_id', $request->input('class_id')))
            ->when($request->has('type'), fn($q) => $q->where('type', $request->input('type')))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->with('session')
            ->with('class')
            ->orderBy('start_date', 'desc')
            ->orderBy('created_at', 'desc');

        $exams = $query->paginate($perPage);

        return $this->successResponse($exams);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $exam = Exam::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->with('session')
            ->with('class')
            ->with('questions')
            ->first();

        if (!$exam) {
            return $this->errorResponse('পরীক্ষা পাওয়া যায়নি', 404);
        }

        return $this->successResponse($exam);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name_bn' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'exam_type' => 'nullable|in:নিয়মিত,মাসিক,ত্রৈমাসিক,বার্ষিক,মডেল,অন্যান্য',
            'session_id' => 'nullable|integer',
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'start_time' => 'nullable|datetime',
            'end_time' => 'nullable|datetime',
            'total_marks' => 'nullable|integer',
            'passing_marks' => 'nullable|integer',
            'duration_minutes' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'attendance_required' => 'nullable|boolean',
            'grade_distribution' => 'nullable|array',
            'seat_plan' => 'nullable|array',
            'questions_count' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['is_active'] = $data['is_active'] ?? true;

        $exam = Exam::create($data);

        $exam->load('session');
        $exam->load('class');

        return $this->successResponse($exam, 'পরীক্ষা তৈরি সফল', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $exam = Exam::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$exam) {
            return $this->errorResponse('পরীক্ষা পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'name_bn' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'exam_type' => 'nullable|in:নিয়মিত,মাসিক,ত্রৈমাসিক,বার্ষিক,মডেল,অন্যান্য',
            'session_id' => 'nullable|integer',
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'start_time' => 'nullable|datetime',
            'end_time' => 'nullable|datetime',
            'total_marks' => 'nullable|integer',
            'passing_marks' => 'nullable|integer',
            'duration_minutes' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'attendance_required' => 'nullable|boolean',
            'grade_distribution' => 'nullable|array',
            'seat_plan' => 'nullable|array',
            'questions_count' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $exam->update($validator->validated());

        $exam->load('session');
        $exam->load('class');

        return $this->successResponse($exam->fresh(), 'পরীক্ষা আপডেট সফল');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $exam = Exam::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$exam) {
            return $this->errorResponse('পরীক্ষা পাওয়া যায়নি', 404);
        }

        $exam->delete();

        return $this->successResponse(null, 'পরীক্ষা মুছে ফেলা সফল');
    }
}
