<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\AcademicClass;
use App\Models\AttendanceRecord;
use App\Models\Exam;
use App\Models\Result;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends ApiController
{
    /**
     * Attendance report: per-student present/absent/late matrix across a date range.
     * Query: class_id (required), from (Y-m-d), to (Y-m-d, default today)
     */
    public function attendance(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|integer',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $user = $request->user();
        $class = AcademicClass::findOrFail($request->input('class_id'));

        $from = $request->input('from', Carbon::now()->startOfMonth()->toDateString());
        $to = $request->input('to', Carbon::now()->toDateString());

        // Students enrolled in this class
        $studentIds = Student::whereHas('enrollments', fn($e) => $e->where('class_id', $class->id))
            ->with('user:id,name_bn,name_en')
            ->get();

        // Build date range
        $dates = [];
        $current = Carbon::parse($from);
        $end = Carbon::parse($to);
        while ($current->lte($end)) {
            $dates[] = $current->toDateString();
            $current->addDay();
        }

        // Attendance records in range
        $records = AttendanceRecord::whereIn('student_id', $studentIds->pluck('user_id'))
            ->whereBetween('date', [$from, $to])
            ->get()
            ->groupBy('student_id');

        $rows = [];
        foreach ($studentIds as $student) {
            $byDate = [];
            $present = $absent = $late = 0;
            if (isset($records[$student->user_id])) {
                foreach ($records[$student->user_id] as $rec) {
                    $byDate[(string) $rec->date] = $rec->status;
                    if ($rec->status === 'present') $present++;
                    elseif ($rec->status === 'absent') $absent++;
                    elseif ($rec->status === 'late') $late++;
                }
            }
            $rows[] = [
                'student_id' => $student->id,
                'name_bn' => $student->user?->name_bn ?? $student->name_bn,
                'name_en' => $student->user?->name_en ?? $student->name_en,
                'admission_number' => $student->admission_number,
                'by_date' => $byDate,
                'summary' => ['present' => $present, 'absent' => $absent, 'late' => $late, 'total' => count($dates)],
            ];
        }

        return $this->successResponse([
            'class' => ['id' => $class->id, 'name_bn' => $class->name_bn, 'name_en' => $class->name_en],
            'from' => $from,
            'to' => $to,
            'dates' => $dates,
            'rows' => $rows,
        ], 'হাজিরা রিপোর্ট');
    }

    /**
     * Exam result report: per-student marks per subject + grade for an exam.
     * Query: exam_id (required)
     */
    public function results(Request $request): JsonResponse
    {
        $request->validate([
            'exam_id' => 'required|integer|exists:exams,id',
        ]);

        $user = $request->user();
        $exam = Exam::findOrFail($request->input('exam_id'));

        $results = Result::where('exam_id', $exam->id)
            ->with(['student:id,name_bn,name_en', 'studentProfile'])
            ->get();

        $rows = [];
        foreach ($results as $r) {
            $subjects = [];
            if (is_array($r->subject_results)) {
                foreach ($r->subject_results as $sr) {
                    $subjects[] = [
                        'subject' => $sr['subject_name'] ?? ($sr['subject'] ?? '-'),
                        'marks' => $sr['marks_obtained'] ?? ($sr['marks'] ?? null),
                        'total' => $sr['total_marks'] ?? ($sr['total'] ?? null),
                        'grade' => $sr['grade'] ?? null,
                    ];
                }
            }
            $rows[] = [
                'student_id' => $r->student?->id,
                'name_bn' => $r->student?->name_bn ?? null,
                'name_en' => $r->student?->name_en ?? null,
                'admission_number' => $r->studentProfile?->admission_number,
                'total_marks' => $r->marks_obtained,
                'total_max' => $r->total_marks,
                'percentage' => $r->percentage,
                'grade' => $r->grade,
                'subjects' => $subjects,
            ];
        }

        return $this->successResponse([
            'exam' => ['id' => $exam->id, 'name_bn' => $exam->name_bn, 'name_en' => $exam->name_en],
            'rows' => $rows,
        ], 'ফলাফল রিপোর্ট');
    }

    /**
     * CSV export of the attendance report.
     */
    public function exportAttendanceCsv(Request $request): StreamedResponse
    {
        $request->validate([
            'class_id' => 'required|integer',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $user = $request->user();
        $class = AcademicClass::where('tenant_id', $user->tenant_id)
            ->where('id', $request->input('class_id'))
            ->firstOrFail();

        $from = $request->input('from', Carbon::now()->startOfMonth()->toDateString());
        $to = $request->input('to', Carbon::now()->toDateString());

        $studentIds = Student::where('tenant_id', $user->tenant_id)
            ->whereHas('enrollments', fn($e) => $e->where('class_id', $class->id))
            ->with('user:id,name_bn,name_en')
            ->get();

        $dates = [];
        $current = Carbon::parse($from);
        $end = Carbon::parse($to);
        while ($current->lte($end)) {
            $dates[] = $current->toDateString();
            $current->addDay();
        }

        $records = AttendanceRecord::whereIn('student_id', $studentIds->pluck('user_id'))
            ->whereBetween('date', [$from, $to])
            ->get()
            ->groupBy('student_id');

        $fileName = 'attendance_class_' . $class->id . '_' . $from . '_' . $to . '.csv';

        return response()->streamDownload(function () use ($studentIds, $dates, $records, $from, $to, $class) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Bengali in Excel
            fputcsv($out, ['হাজিরা রিপোর্ট — ' . ($class->name_bn ?? $class->name_en), '', '', $from . ' থেকে ' . $to]);
            fputcsv($out, array_merge(['নাম', 'ভর্তি নং'], $dates, ['উপস্থিত', 'অনুপস্থিত', 'দেরি']));
            foreach ($studentIds as $student) {
                $byDate = [];
                $present = $absent = $late = 0;
                if (isset($records[$student->user_id])) {
                    foreach ($records[$student->user_id] as $rec) {
                        $byDate[(string) $rec->date] = $rec->status;
                        if ($rec->status === 'present') $present++;
                        elseif ($rec->status === 'absent') $absent++;
                        elseif ($rec->status === 'late') $late++;
                    }
                }
                $row = [$student->user?->name_bn ?? $student->name_bn, $student->admission_number ?? ''];
                foreach ($dates as $d) {
                    $row[] = $byDate[$d] ?? '-';
                }
                $row[] = $present;
                $row[] = $absent;
                $row[] = $late;
                fputcsv($out, $row);
            }
            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=utf-8']);
    }

    /**
     * CSV export of the exam result report.
     */
    public function exportResultsCsv(Request $request): StreamedResponse
    {
        $request->validate([
            'exam_id' => 'required|integer|exists:exams,id',
        ]);

        $user = $request->user();
        $exam = Exam::where('tenant_id', $user->tenant_id)
            ->where('id', $request->input('exam_id'))
            ->firstOrFail();

        $results = Result::where('tenant_id', $user->tenant_id)
            ->where('exam_id', $exam->id)
            ->with('student:id,name_bn,name_en')
            ->get();

        $fileName = 'results_exam_' . $exam->id . '.csv';

        return response()->streamDownload(function () use ($results, $exam) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ফলাফল রিপোর্ট — ' . ($exam->name_bn ?? $exam->name_en)]);
            fputcsv($out, ['নাম', 'ভর্তি নং', 'মোট প্রাপ্ত' , 'মোট নম্বর', 'শতকরা', 'গ্রেড']);
            foreach ($results as $r) {
                fputcsv($out, [
                    $r->student?->name_bn ?? '',
                    optional(\App\Models\Student::where('user_id', $r->student_id)->first())->admission_number ?? '',
                    $r->marks_obtained,
                    $r->total_marks,
                    $r->percentage,
                    $r->grade,
                ]);
            }
            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=utf-8']);
    }
}
