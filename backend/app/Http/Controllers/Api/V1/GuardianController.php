<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\AttendanceRecord;
use App\Models\FeePayment;
use App\Models\Result;
use App\Models\StudentGuardian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuardianController extends ApiController
{
    /**
     * Guardian portal: returns the guardian's linked students with
     * attendance summary, latest exam results, and fee status.
     */
    public function portal(Request $request): JsonResponse
    {
        $user = $request->user();

        // Guardian links for this user
        $links = StudentGuardian::where('user_id', $user->id)
            ->with(['student' => function ($q) {
                $q->with('user:id,name_bn,name_en,email');
            }])
            ->get();

        if ($links->isEmpty()) {
            return $this->successResponse([
                'guardian' => ['name' => $user->name_bn ?? $user->name_en],
                'students' => [],
            ], 'অভিভাবক পোর্টাল');
        }

        $studentIds = $links->pluck('student_id')->filter()->unique()->values()->all();

        $students = [];
        foreach ($links as $link) {
            $student = $link->student;
            if (!$student) {
                continue;
            }

            // Attendance summary (last 30 days)
            $att = AttendanceRecord::where('student_id', $student->user_id)
                ->where('date', '>=', now()->subDays(30))
                ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as present', ['present'])
                ->first();

            // Latest results
            $latestResults = Result::where('student_id', $student->user_id)
                ->with('exam:id,name_bn,name_en')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['id', 'exam_id', 'marks_obtained', 'total_marks', 'grade', 'is_published']);

            // Fee status (latest unpaid first)
            $feePayments = FeePayment::where('student_id', $student->user_id)
                ->orderBy('due_date', 'desc')
                ->limit(5)
                ->get(['id', 'total_amount', 'paid_amount', 'balance', 'due_date', 'is_fully_paid', 'status']);

            $students[] = [
                'id' => $student->id,
                'name_bn' => $student->user?->name_bn ?? $student->name_bn ?? null,
                'name_en' => $student->user?->name_en ?? $student->name_en ?? null,
                'admission_number' => $student->admission_number,
                'class_id' => $student->class_id,
                'section_id' => $student->section_id,
                'relationship' => $link->relationship,
                'attendance' => [
                    'total' => $att->total ?? 0,
                    'present' => $att->present ?? 0,
                    'rate' => $att->total > 0 ? round(($att->present / $att->total) * 100) : 0,
                ],
                'results' => $latestResults->map(fn($r) => [
                    'exam' => $r->exam?->name_bn ?? $r->exam?->name_en ?? '-',
                    'marks' => $r->marks_obtained,
                    'grade' => $r->grade,
                    'published' => (bool) $r->is_published,
                ]),
                'fees' => $feePayments->map(fn($f) => [
                    'total' => $f->total_amount,
                    'paid' => $f->paid_amount,
                    'balance' => $f->balance,
                    'due_date' => $f->due_date,
                    'is_fully_paid' => (bool) $f->is_fully_paid,
                ]),
            ];
        }

        return $this->successResponse([
            'guardian' => ['name' => $user->name_bn ?? $user->name_en, 'email' => $user->email],
            'students' => $students,
        ], 'অভিভাবক পোর্টাল');
    }
}
