<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LessonEvaluation;
use App\Models\LessonEvaluationBook;
use App\Models\Student;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonEvaluationController extends Controller
{
    public function grid(Request $request): JsonResponse
    {
        $classId = $request->query('class_id');
        $subjectId = $request->query('subject_id');
        $bookId = $request->query('book_id', 0);
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $evaluations = LessonEvaluation::where('month', $month)
            ->where('year', $year)
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->when($subjectId, fn($q) => $q->where('subject_id', $subjectId))
            ->when($bookId, fn($q) => $q->where('book_id', $bookId))
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $evaluations,
        ]);
    }

    public function mark(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|integer',
            'class_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'book_id' => 'nullable|integer',
            'date' => 'required|date',
            'day' => 'required|integer',
            'month' => 'required|integer',
            'year' => 'required|integer',
            'grade' => 'nullable|string|max:10',
        ]);

        $evaluation = LessonEvaluation::updateOrCreate(
            [
                'tenant_id' => $request->user()?->tenant_id,
                'student_id' => $validated['student_id'],
                'subject_id' => $validated['subject_id'],
                'book_id' => $validated['book_id'] ?? 0,
                'date' => $validated['date'],
            ],
            [
                'class_id' => $validated['class_id'],
                'day' => $validated['day'],
                'month' => $validated['month'],
                'year' => $validated['year'],
                'grade' => $validated['grade'] ?? '',
            ]
        );

        return response()->json([
            'status' => 'success',
            'data' => $evaluation,
        ]);
    }

    public function markBulk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|integer',
            'marks.*.class_id' => 'required|integer',
            'marks.*.subject_id' => 'required|integer',
            'marks.*.book_id' => 'nullable|integer',
            'marks.*.date' => 'required|date',
            'marks.*.day' => 'required|integer',
            'marks.*.month' => 'required|integer',
            'marks.*.year' => 'required|integer',
            'marks.*.grade' => 'nullable|string|max:10',
        ]);

        $tenantId = $request->user()?->tenant_id;

        foreach ($validated['marks'] as $m) {
            LessonEvaluation::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'student_id' => $m['student_id'],
                    'subject_id' => $m['subject_id'],
                    'book_id' => $m['book_id'] ?? 0,
                    'date' => $m['date'],
                ],
                [
                    'class_id' => $m['class_id'],
                    'day' => $m['day'],
                    'month' => $m['month'],
                    'year' => $m['year'],
                    'grade' => $m['grade'] ?? '',
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'পাঠ মূল্যায়ন সফলভাবে সংরক্ষিত হয়েছে',
        ]);
    }

    public function books(Request $request): JsonResponse
    {
        $classId = $request->query('class_id');
        $subjectId = $request->query('subject_id');

        $books = LessonEvaluationBook::when($classId, fn($q) => $q->where('class_id', $classId))
            ->when($subjectId, fn($q) => $q->where('subject_id', $subjectId))
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $books,
        ]);
    }

    public function storeBook(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'class_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
        ]);

        $book = LessonEvaluationBook::create([
            'tenant_id' => $request->user()?->tenant_id,
            'name' => $validated['name'],
            'class_id' => $validated['class_id'] ?? null,
            'subject_id' => $validated['subject_id'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $book,
        ], 201);
    }

    public function destroyBook($id): JsonResponse
    {
        LessonEvaluationBook::findOrFail($id)->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'বই মুছে ফেলা হয়েছে',
        ]);
    }
}
