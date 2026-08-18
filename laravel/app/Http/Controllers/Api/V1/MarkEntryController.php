<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\MarkEntry;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class MarkEntryController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = MarkEntry::where('tenant_id', $user->tenant_id)
            ->when($request->has('exam_id'), fn($q) => $q->where('exam_id', $request->input('exam_id')))
            ->when($request->has('student_id'), fn($q) => $q->where('student_id', $request->input('student_id')))
            ->when($request->has('subject_id'), fn($q) => $q->where('subject_id', $request->input('subject_id')))
            ->when($request->has('graded_by_teacher_id'), fn($q) => $q->where('graded_by_teacher_id', $request->input('graded_by_teacher_id')))
            ->when($request->has('is_graded'), fn($q) => $q->where('is_graded', filter_var($request->input('is_graded'), FILTER_VALIDATE_BOOLEAN)))
            ->with('exam:id,name_bn,class_id')
            ->with('student:id,name_bn,name_en')
            ->with('subject:id,name_bn,name_en')
            ->with('gradedByTeacher.user:id,name_bn,name_en')
            ->orderBy('created_at', 'desc');

        $entries = $query->paginate($perPage);

        return $this->successResponse($entries);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $entry = MarkEntry::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->with('exam')
            ->with('student.user:id,name_bn,name_en')
            ->with('subject:id,name_bn,name_en')
            ->with('gradedByTeacher.user:id,name_bn,name_en')
            ->first();

        if (!$entry) {
            return $this->errorResponse('মার্ক এন্ট্রি পাওয়া যায়নি', 404);
        }

        return $this->successResponse($entry);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|integer|exists:exams,id',
            'student_id' => 'required|integer|exists:users,id',
            'subject_id' => 'nullable|integer',
            'marks_obtained' => 'nullable|numeric',
            'max_marks' => 'nullable|numeric',
            'remarks_bn' => 'nullable|string|max:500',
            'remarks_en' => 'nullable|string|max:500',
            'graded_by_teacher_id' => 'nullable|integer|exists:teachers,id',
            'is_graded' => 'nullable|boolean',
            'graded_at' => 'nullable|datetime',
            'is_published_in_result' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['is_graded'] = $data['is_graded'] ?? false;
        $data['is_active'] = $data['is_active'] ?? true;

        $entry = MarkEntry::create($data);

        $entry->load('exam:id,name_bn,class_id');
        $entry->load('student:id,name_bn,name_en');

        return $this->successResponse($entry, 'মার্ক এন্ট্রি তৈরি সফল', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $entry = MarkEntry::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$entry) {
            return $this->errorResponse('মার্ক এন্ট্রি পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'marks_obtained' => 'nullable|numeric',
            'max_marks' => 'nullable|numeric',
            'remarks_bn' => 'nullable|string|max:500',
            'remarks_en' => 'nullable|string|max:500',
            'graded_by_teacher_id' => 'nullable|integer|exists:teachers,id',
            'is_graded' => 'nullable|boolean',
            'graded_at' => 'nullable|datetime',
            'is_published_in_result' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $entry->update($validator->validated());

        $entry->load('exam:id,name_bn,class_id');

        return $this->successResponse($entry->fresh(), 'মার্ক এন্ট্রি আপডেট সফল');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $entry = MarkEntry::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$entry) {
            return $this->errorResponse('মার্ক এন্ট্রি পাওয়া যায়নি', 404);
        }

        $entry->delete();

        return $this->successResponse(null, 'মার্ক এন্ট্রি মুছে ফেলা সফল');
    }

    public function bulkGrade(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|integer|exists:exams,id',
            'subject_id' => 'nullable|integer',
            'entries' => 'required|array',
            'entries.*.student_id' => 'required|integer|exists:users,id',
            'entries.*.marks_obtained' => 'nullable|numeric',
            'entries.*.max_marks' => 'nullable|numeric',
            'entries.*.remarks_bn' => 'nullable|string',
            'entries.*.remarks_en' => 'nullable|string',
            'graded_by_teacher_id' => 'nullable|integer|exists:teachers,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $tenantId = $request->user()->tenant_id;
        $teacherId = $data['graded_by_teacher_id'] ?? null;

        $created = [];
        foreach ($data['entries'] as $entryData) {
            $existing = MarkEntry::where('tenant_id', $tenantId)
                ->where('exam_id', $data['exam_id'])
                ->where('student_id', $entryData['student_id'])
                ->where('subject_id', $data['subject_id'] ?? null)
                ->first();

            if ($existing) {
                $existing->update([
                    'marks_obtained' => $entryData['marks_obtained'],
                    'max_marks' => $entryData['max_marks'],
                    'remarks_bn' => $entryData['remarks_bn'] ?? $existing->remarks_bn,
                    'remarks_en' => $entryData['remarks_en'] ?? $existing->remarks_en,
                    'graded_by_teacher_id' => $teacherId,
                    'is_graded' => true,
                    'graded_at' => now(),
                ]);
                $created[] = $existing->fresh();
            } else {
                $entry = MarkEntry::create([
                    'tenant_id' => $tenantId,
                    'exam_id' => $data['exam_id'],
                    'student_id' => $entryData['student_id'],
                    'subject_id' => $data['subject_id'],
                    'marks_obtained' => $entryData['marks_obtained'],
                    'max_marks' => $entryData['max_marks'],
                    'remarks_bn' => $entryData['remarks_bn'],
                    'remarks_en' => $entryData['remarks_en'],
                    'graded_by_teacher_id' => $teacherId,
                    'is_graded' => true,
                    'graded_at' => now(),
                    'is_active' => true,
                ]);
                $created[] = $entry;
            }
        }

        return $this->successResponse($created, 'বাল্ক মার্ক গ্রেডিং সফল', 201);
    }
}
