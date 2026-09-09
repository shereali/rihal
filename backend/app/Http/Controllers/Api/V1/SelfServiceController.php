<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\AttendanceRecord;
use App\Models\FeePayment;
use App\Models\Result;
use App\Models\Student;
use App\Models\TeacherAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SelfServiceController extends ApiController
{
    /**
     * Student self-service: the logged-in student (role=student) sees their
     * own attendance summary, latest results, and fee status.
     */
    public function studentMe(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if($user->role !== 'student', 403, 'শুধুমাত্র ছাত্র এই তথ্য দেখতে পারবে');

        $student = Student::where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $att = AttendanceRecord::where('student_id', $student->user_id)
            ->where('date', '>=', now()->subDays(30))
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as present', ['present'])
            ->first();

        $results = Result::where('student_id', $student->user_id)
            ->with('exam:id,name_bn,name_en')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'exam_id', 'marks_obtained', 'total_marks', 'grade', 'is_published']);

        $fees = FeePayment::where('student_id', $student->user_id)
            ->orderBy('due_date', 'desc')
            ->limit(5)
            ->get(['id', 'total_amount', 'paid_amount', 'balance', 'due_date', 'is_fully_paid', 'status']);

        return $this->successResponse([
            'student' => [
                'name_bn' => $student->user?->name_bn ?? $student->name_bn,
                'name_en' => $student->user?->name_en ?? $student->name_en,
                'admission_number' => $student->admission_number,
                'class_id' => $student->class_id,
                'section_id' => $student->section_id,
            ],
            'attendance' => [
                'total' => $att->total ?? 0,
                'present' => $att->present ?? 0,
                'rate' => $att->total > 0 ? round(($att->present / $att->total) * 100) : 0,
            ],
            'results' => $results->map(fn($r) => [
                'exam' => $r->exam?->name_bn ?? $r->exam?->name_en ?? '-',
                'marks' => $r->marks_obtained,
                'grade' => $r->grade,
                'published' => (bool) $r->is_published,
            ]),
            'fees' => $fees->map(fn($f) => [
                'total' => $f->total_amount,
                'paid' => $f->paid_amount,
                'balance' => $f->balance,
                'due_date' => $f->due_date,
                'is_fully_paid' => (bool) $f->is_fully_paid,
            ]),
        ], 'ছাত্রের তথ্য');
    }

    /**
     * Teacher self-service: the logged-in teacher (role=teacher) sees their
     * assignments.
     */
    public function teacherAssignments(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if($user->role !== 'teacher', 403, 'শুধুমাত্র শিক্ষক এই তথ্য দেখতে পারবে');

        $assignments = TeacherAssignment::where('tenant_id', $user->tenant_id)
            ->where('teacher_id', $user->id)
            ->with(['class:id,name_bn,name_en', 'section:id,name_bn,name_en', 'subject:id,name_bn,name_en'])
            ->get();

        return $this->successResponse($assignments, 'শিক্ষকের বরাদ্দ');
    }
}
