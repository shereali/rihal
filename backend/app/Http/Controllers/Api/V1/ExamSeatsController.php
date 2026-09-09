<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExamSeatsController extends ApiController
{
    public function show(Request $request, int $examId): JsonResponse
    {
        $exam = Exam::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $examId)
            ->with('class:id,name_bn,class_type')
            ->with('session:id,name_bn')
            ->first();

        if (!$exam) {
            return $this->errorResponse('পরীক্ষা পাওয়া যায়নি', 404);
        }

        $enrolled = $this->enrolledStudents($request, $exam)->get();
        $enrolledStudents = $enrolled->map(fn($e) => [
            'id' => $e->student_id,
            'name_bn' => $e->name_bn ?? '',
            'name_en' => $e->name_en ?? '',
            'roll_or_reg' => $e->enrollment_number ?? '',
            'photo_url' => null,
        ]);

        return $this->successResponse([
            'exam' => $exam,
            'enrolled_count' => $enrolled->count(),
            'seats_assigned' => ($exam->seat_plan ? count($exam->seat_plan) : 0),
            'hall_rows' => $exam->seat_hall_rows ?? 6,
            'hall_cols' => $exam->seat_hall_cols ?? 9,
            'enrolled_students' => $enrolledStudents,
            'seat_plan' => $exam->seat_plan ?? [],
        ]);
    }

    public function allocateSeat(Request $request, int $examId): JsonResponse
    {
        $exam = Exam::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $examId)
            ->first();

        if (!$exam) {
            return $this->errorResponse('পরীক্ষা পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:users,id',
            'row' => 'required|integer|min:1',
            'col' => 'required|integer|min:1',
            'seat_label' => 'nullable|string|max:20',
            'hall_floor' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $seatPlan = $exam->seat_plan ?? [];
        $seatPlan[$request->student_id] = [
            'row' => (int) $request->row,
            'col' => (int) $request->col,
            'label' => $request->seat_label ?? ($request->row . '-' . $request->col),
            'floor' => $request->hall_floor ?? 'মূল ভবন',
            'student_id' => (int) $request->student_id,
            'assigned_by' => $request->user()->id,
            'assigned_at' => now(),
        ];

        $exam->update([
            'seat_plan' => $seatPlan,
            'seat_generated' => true,
            'seat_generated_at' => now(),
        ]);

        $exam->refresh();

        return $this->successResponse([
            'seat_plan' => $exam->seat_plan,
            'message' => 'সিট বরাদ্দ সফল',
        ]);
    }

    public function seatPlan(Request $request, int $examId): JsonResponse
    {
        $exam = Exam::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $examId)
            ->first();

        if (!$exam) {
            return $this->errorResponse('পরীক্ষা পাওয়া যায়নি', 404);
        }

        return $this->successResponse($exam->seat_plan ?? []);
    }

    public function storeSeatPlan(Request $request, int $examId): JsonResponse
    {
        $exam = Exam::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $examId)
            ->first();

        if (!$exam) {
            return $this->errorResponse('পরীক্ষা পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'seat_plan' => 'nullable|array',
            'hall_rows' => 'nullable|integer|min:1|max:20',
            'hall_cols' => 'nullable|integer|min:1|max:30',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $exam->update([
            'seat_plan' => $request->seat_plan ?? [],
            'seat_hall_rows' => $request->hall_rows ?? $exam->seat_hall_rows,
            'seat_hall_cols' => $request->hall_cols ?? $exam->seat_hall_cols,
            'seat_generated' => true,
            'seat_generated_at' => now(),
        ]);

        $exam->refresh();

        return $this->successResponse($exam);
    }

    public function generateAdmitCard(Request $request, int $examId, int $studentId): JsonResponse
    {
        $exam = Exam::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $examId)
            ->first();

        if (!$exam) {
            return $this->errorResponse('পরীক্ষা পাওয়া যায়নি', 404);
        }

        $student = User::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $studentId)
            ->first();

        if (!$student) {
            return $this->errorResponse('এই শিক্ষার্থী পাওয়া যায়নি', 404);
        }

        $seat = $exam->seat_plan[$studentId] ?? null;
        $enroll = DB::table('enrollments')
            ->where('enrollments.tenant_id', $request->user()->tenant_id)
            ->where('enrollments.student_id', $studentId)
            ->where('enrollments.class_id', $exam->class_id)
            ->first();

        return $this->successResponse([
            'exam_id' => $exam->id,
            'exam_name_bn' => $exam->name_bn ?? '',
            'exam_type' => $exam->exam_type ?? 'নিয়মিত',
            'exam_date' => $exam->start_date ? $exam->start_date->format('d F Y') : '',
            'exam_time' => $exam->start_time ? $exam->start_time->format('h:i A') : '',
            'exam_end_time' => $exam->end_time ? $exam->end_time->format('h:i A') : '',
            'exam_duration_minutes' => $exam->duration_minutes ?? 120,
            'exam_venue' => $exam->seat_venue ?? 'মূল পরীক্ষা কক্ষ',
            'student' => [
                'id' => $student->id,
                'name_bn' => $student->name_bn ?? $student->name ?? '',
                'name_en' => $student->name_en ?? '',
                'roll_or_reg' => $enroll?->enrollment_number ?? '—',
                'photo_url' => null,
                'blood_group' => $student->blood_group ?? null,
                'parent_contact' => $student->parent_phone ?? null,
            ],
            'seat' => $seat ?? null,
            'seat_label' => $seat?->label ?? 'অনির্ধারিত',
            'row' => $seat?->row ?? null,
            'col' => $seat?->col ?? null,
        ]);
    }

    private function enrolledStudents(Request $request, Exam $exam)
    {
        return DB::table('enrollments')
            ->where('enrollments.tenant_id', $request->user()->tenant_id)
            ->where('enrollments.class_id', $exam->class_id)
            ->where('enrollments.status', 'enrolled')
            ->join('users', 'enrollments.student_id', '=', 'users.id')
            ->select('users.id as student_id', 'users.name_bn', 'users.name_en', 'enrollments.enrollment_number');
    }
}
