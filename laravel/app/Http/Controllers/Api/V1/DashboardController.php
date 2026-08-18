<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\AttendanceRecord;
use App\Models\Donation;
use App\Models\Exam;
use App\Models\Expense;
use App\Models\FeePayment;
use App\Models\Result;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    public function stats(Request $request): JsonResponse
    {
        $tenantId = $tenant = $request->user()->tenant_id;
        $today = today()->toDateString();

        $totalStudents = Student::where('tenant_id', $tenantId)->count();
        $totalTeachers = Teacher::where('tenant_id', $tenantId)->count();
        $totalExams = Exam::where('tenant_id', $tenantId)->count();
        $unpublishedResults = Result::where('tenant_id', $tenantId)->where('is_published', false)->count();
        $publishedResults = Result::where('tenant_id', $tenantId)->where('is_published', true)->count();

        $todayAttendance = AttendanceRecord::where('tenant_id', $tenantId)
            ->whereDate('date', $today)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as present, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as absent, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as late', ['present', 'absent', 'late'])
            ->first();

        $totalDonations = Donation::where('tenant_id', $tenantId)->sum('amount') ?? 0;
        $totalExpenses = Expense::where('tenant_id', $tenantId)->sum('amount') ?? 0;
        $totalFeeCollected = FeePayment::where('tenant_id', $tenantId)->where('is_fully_paid', true)->sum('paid_amount') ?? 0;

        $attendanceRate = $todayAttendance && $todayAttendance->total > 0
            ? round(($todayAttendance->present / $todayAttendance->total) * 100)
            : 0;

        return $this->successResponse([
            'total_students' => $totalStudents,
            'total_teachers' => $totalTeachers,
            'total_exams' => $totalExams,
            'unpublished_results' => $unpublishedResults,
            'published_results' => $publishedResults,
            'attendance' => [
                'total' => $todayAttendance->total ?? 0,
                'present' => $todayAttendance->present ?? 0,
                'absent' => $todayAttendance->absent ?? 0,
                'late' => $todayAttendance->late ?? 0,
                'attendance_rate' => $attendanceRate,
            ],
            'finance' => [
                'total_donations' => $totalDonations,
                'total_expenses' => $totalExpenses,
                'total_fee_collected' => $totalFeeCollected,
                'net_balance' => $totalDonations + $totalFeeCollected - $totalExpenses,
            ],
        ], 'ড্যাশবোর্ড পরিসংখ্যান');
    }
}
