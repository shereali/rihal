<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\User;
use Carbon\Carbon;

class ExamSeatDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'demo-madrasa')->first();
        if (!$tenant) {
            echo "No demo tenant found.\n";
            return;
        }

        $session = AcademicSession::where('tenant_id', $tenant->id)
            ->where('status', 'active')->first();
        $sessionId = $session?->id ?? AcademicSession::where('tenant_id', $tenant->id)->first()?->id;
        if (!$sessionId) {
            echo "No academic session found.\n";
            return;
        }

        // Class 1 (first regular class)
        $targetClass = AcademicClass::where('tenant_id', $tenant->id)
            ->where('class_type', 'regular')->first();
        if (!$targetClass) {
            echo "No regular class found.\n";
            return;
        }

        $classId = $targetClass->id;

        // Find or create an exam for this class
        $exam = Exam::firstOrCreate(
            ['tenant_id' => $tenant->id, 'class_id' => $classId, 'name_en' => 'Annual Exam 2027'],
            [
                'tenant_id' => $tenant->id,
                'class_id' => $classId,
                'session_id' => $sessionId,
                'name_bn' => 'বার্ষিক পরীক্ষা ২০২৭',
                'name_en' => 'Annual Exam 2027',
                'exam_type' => 'বার্ষিক',
                'status' => 'scheduled',
                'start_date' => Carbon::tomorrow()->format('Y-m-d'),
                'end_date' => Carbon::tomorrow()->format('Y-m-d'),
                'start_time' => Carbon::tomorrow()->setTime(9, 30),
                'end_time' => Carbon::tomorrow()->setTime(11, 30),
                'duration_minutes' => 120,
                'room_name' => 'মূল পরীক্ষা কক্ষ',
                'total_marks' => 100,
                'passing_marks' => 33,
                'seat_hall_rows' => 6,
                'seat_hall_cols' => 6,
                'seat_venue' => 'মূল পরীক্ষা কক্ষ',
            ]
        );

        $examId = $exam->id;

        // Enrolled students for this class (limit 12)
        $enrolled = Enrollment::where('tenant_id', $tenant->id)
            ->where('class_id', $classId)
            ->orderBy('id')
            ->limit(12)
            ->get();

        $seatPlan = [];
        foreach ($enrolled as $index => $entry) {
            $row = intdiv($index, 3) + 1;
            $col = ($index % 3) + 1;
            $seatPlan[$entry->student_id] = [
                'row' => $row,
                'col' => $col,
                'label' => "{$row}-{$col}",
                'floor' => 'মূল ভবন',
                'assigned_by' => auth()->id() ?: 1,
                'assigned_at' => now(),
            ];
        }

        // Store seat plan as JSON on the exam row (see Exam model seat_plan cast)
        if (!empty($seatPlan)) {
            $exam->update([
                'seat_plan' => $seatPlan,
                'seat_generated' => true,
                'seat_generated_at' => now(),
                'seat_number' => count($seatPlan),
            ]);
            echo "Seeded seat plan for exam #{$examId}: " . count($seatPlan) . " seats.\n";
        }

        // Seed 2 exam results for the first two students
        $firstTwo = $enrolled->take(2);
        foreach ($firstTwo as $entry) {
            $studentId = $entry->student_id;

            $existing = \App\Models\Result::where('tenant_id', $tenant->id)
                ->where('exam_id', $examId)
                ->where('student_id', $studentId)
                ->exists();

            if (!$existing) {
                try {
                    \App\Models\Result::create([
                        'tenant_id' => $tenant->id,
                        'exam_id' => $examId,
                        'student_id' => $studentId,
                        'session_id' => $sessionId,
                        'gpa' => ($studentId % 2 === 0) ? 3.2 : 3.8,
                        'percentage' => ($studentId % 2 === 0) ? 72.5 : 85.0,
                        'grade' => ($studentId % 2 === 0) ? 'B' : 'A',
                        'pass_fail_status' => 'passed',
                        'subject_results' => json_encode(['Islamic Studies' => 80, 'Bengali' => 75, 'English' => 70, 'Mathematics' => 72]),
                        'published_at' => now(),
                        'published_by' => auth()->id() ?: 1,
                    ]);
                    echo "Seeded exam result for student #{$studentId}.\n";
                } catch (\Throwable $e) {
                    echo "Result seed skipped ({$studentId}): " . $e->getMessage() . "\n";
                }
            }
        }

        echo "ExamSeatDemoSeeder complete.\n";
    }
}
