<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\AcademicClass;
use App\Models\AttendanceRecord;
use App\Models\Donation;
use App\Models\Enrollment;
use App\Models\Expense;
use App\Models\FeePayment;
use App\Models\Fund;
use App\Models\Result;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends ApiController
{
    public function stats(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $today = today()->toDateString();
        $period = $request->get('period', 'all');

        // ---- Core counts ----
        $totalStudents = Student::where('tenant_id', $tenantId)->count();
        $totalTeachers = Teacher::where('tenant_id', $tenantId)->count();
        $totalExams = \App\Models\Exam::where('tenant_id', $tenantId)->count();
        $unpublishedResults = Result::where('tenant_id', $tenantId)->where('is_published', false)->count();
        $publishedResults = Result::where('tenant_id', $tenantId)->where('is_published', true)->count();

        // ---- Attendance (today or period) ----
        $attendanceQuery = AttendanceRecord::where('tenant_id', $tenantId);
        if ($period === 'today') {
            $attendanceQuery->whereDate('date', $today);
        } elseif ($period === 'week') {
            $startOfWeek = today()->startOfWeek()->toDateString();
            $attendanceQuery->whereBetween('date', [$startOfWeek, $today]);
        } elseif ($period === 'month') {
            $startOfMonth = today()->startOfMonth()->toDateString();
            $attendanceQuery->whereBetween('date', [$startOfMonth, $today]);
        } elseif ($period === 'year') {
            $startOfYear = today()->startOfYear()->toDateString();
            $attendanceQuery->whereBetween('date', [$startOfYear, $today]);
        }

        $todayAttendance = $attendanceQuery
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as present, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as absent, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as late', ['present', 'absent', 'late'])
            ->first();

        $attendanceRate = $todayAttendance && $todayAttendance->total > 0
            ? round(($todayAttendance->present / $todayAttendance->total) * 100)
            : 0;

        // ---- Finance (period-aware) ----
        $donationQuery = Donation::where('tenant_id', $tenantId);
        $expenseQuery = Expense::where('tenant_id', $tenantId);
        $feeQuery = FeePayment::where('tenant_id', $tenantId)->where('is_fully_paid', true);

        if ($period === 'today') {
            $donationQuery->whereDate('donation_date', $today);
            $expenseQuery->whereDate('transaction_date', $today);
            $feeQuery->whereDate('paid_date', $today);
        } elseif ($period === 'week') {
            $startOfWeek = today()->startOfWeek()->toDateString();
            $donationQuery->whereBetween('donation_date', [$startOfWeek, $today]);
            $expenseQuery->whereBetween('transaction_date', [$startOfWeek, $today]);
            $feeQuery->whereBetween('paid_date', [$startOfWeek, $today]);
        } elseif ($period === 'month') {
            $startOfMonth = today()->startOfMonth()->toDateString();
            $donationQuery->whereBetween('donation_date', [$startOfMonth, $today]);
            $expenseQuery->whereBetween('transaction_date', [$startOfMonth, $today]);
            $feeQuery->whereBetween('paid_date', [$startOfMonth, $today]);
        } elseif ($period === 'year') {
            $startOfYear = today()->startOfYear()->toDateString();
            $donationQuery->whereBetween('donation_date', [$startOfYear, $today]);
            $expenseQuery->whereBetween('transaction_date', [$startOfYear, $today]);
            $feeQuery->whereBetween('paid_date', [$startOfYear, $today]);
        }

        $totalDonations = $donationQuery->sum('amount') ?? 0;
        $totalExpenses = $expenseQuery->sum('amount') ?? 0;
        $totalFeeCollected = $feeQuery->sum('paid_amount') ?? 0;
        $netBalance = $totalDonations + $totalFeeCollected - $totalExpenses;

        // ======================================================================
        // NEW: License status
        // ======================================================================
        $tenant = Tenant::where('id', $tenantId)->first();
        $licenseStatus = 'expired';
        $remainingDays = 0;
        $expiryDate = null;
        $totalDays = 0;
        $usedDays = 0;

        if ($tenant) {
            $totalDays = (int) ($tenant->total_days ?? 0);
            $usedDays = (int) ($tenant->used_days ?? 0);
            $subStatus = $tenant->subscription_status ?? 'inactive';
            $subEndsAt = $tenant->subscription_ends_at;
            $trialEndsAt = $tenant->trial_ends_at;

            if ($subStatus === 'active' && $subEndsAt) {
                $licenseStatus = 'active';
                $expiryDate = Carbon::parse($subEndsAt)->toDateString();
                $remainingDays = max(0, today()->diffInDays(Carbon::parse($subEndsAt), false));
            } elseif ($subStatus === 'trial' && $trialEndsAt) {
                $licenseStatus = 'trial';
                $expiryDate = Carbon::parse($trialEndsAt)->toDateString();
                $remainingDays = max(0, today()->diffInDays(Carbon::parse($trialEndsAt), false));
            } elseif ($subStatus === 'active' && !$subEndsAt) {
                $licenseStatus = 'active';
                $remainingDays = 9999; // indefinite
            }
        }

        // ======================================================================
        // NEW: Current-month class-wise dues
        // ======================================================================
        $currentMonthStart = today()->startOfMonth()->toDateString();
        $currentMonthEnd = today()->endOfMonth()->toDateString();

        $classWiseDues = \DB::table('fee_payments')
            ->join('fee_structures', 'fee_payments.fee_structure_id', '=', 'fee_structures.id')
            ->join('academic_classes', 'fee_structures.class_id', '=', 'academic_classes.id')
            ->where('fee_payments.tenant_id', $tenantId)
            ->where('fee_payments.due_date', '>=', $currentMonthStart)
            ->where('fee_payments.due_date', '<=', $currentMonthEnd)
            ->where(function ($q) {
                $q->where('fee_payments.is_fully_paid', false)
                    ->orWhereNull('fee_payments.is_fully_paid');
            })
            ->selectRaw('academic_classes.name_bn, COUNT(*) as due_count, COALESCE(SUM(fee_payments.balance), 0) as due_amount')
            ->groupBy('academic_classes.id', 'academic_classes.name_bn')
            ->orderBy('due_amount', 'desc')
            ->get();

        $classWiseDuesArray = $classWiseDues->map(function ($row) {
            return [
                'class_name' => $row->name_bn ?? 'অজ্ঞাত শ্রেণি',
                'due_count' => (int) $row->due_count,
                'due_amount' => round((float) $row->due_amount, 2),
            ];
        })->values();

        // ======================================================================
        // NEW: Monthly dues chart (last 6 months)
        // ======================================================================
        $monthlyDues = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = today()->subMonths($i);
            $monthStart = $monthDate->copy()->startOfMonth()->toDateString();
            $monthEnd = $monthDate->copy()->endOfMonth()->toDateString();
            $monthLabel = $monthDate->format('F');

            $dueAmount = \DB::table('fee_payments')
                ->where('tenant_id', $tenantId)
                ->whereBetween('due_date', [$monthStart, $monthEnd])
                ->where(function ($q) {
                    $q->where('is_fully_paid', false)
                        ->orWhereNull('is_fully_paid');
                })
                ->sum('balance') ?? 0;

            $monthlyDues[] = [
                'month' => $monthDate->locale('bn')->format('F'), // Bengali month name via Carbon
                'due_amount' => round((float) $dueAmount, 2),
            ];
        }

        // Fallback: if Carbon doesn't render Bengali, provide manual labels
        $bnMonthNames = ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];
        $monthlyDues = array_map(function ($item) use ($bnMonthNames) {
            $item['month'] = $bnMonthNames[Carbon::parse($item['month'])->month - 1] ?? $item['month'];
            return $item;
        }, $monthlyDues);

        // ======================================================================
        // NEW: Class-wise attendance detail (today, with leave)
        // ======================================================================
        $classWiseAttendance = \DB::table('attendance_records')
            ->join('students', 'attendance_records.student_id', '=', 'students.user_id')
            ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->join('academic_classes', 'enrollments.class_id', '=', 'academic_classes.id')
            ->where('attendance_records.tenant_id', $tenantId)
            ->whereDate('attendance_records.date', $today)
            ->selectRaw('enrollments.class_id, academic_classes.name_bn,
                SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as on_leave',
                ['present', 'absent', 'late', 'leave'])
            ->groupBy('enrollments.class_id', 'academic_classes.name_bn')
            ->get();

        $classWiseAttendanceArray = $classWiseAttendance->map(function ($row) {
            return [
                'class_name' => $row->name_bn ?? 'অজ্ঞাত শ্রেণি',
                'present' => (int) ($row->present ?? 0),
                'absent' => (int) ($row->absent ?? 0),
                'late' => (int) ($row->late ?? 0),
                'leave' => (int) ($row->on_leave ?? 0),
                'total' => (int) ($row->present + $row->absent + $row->late + $row->on_leave),
            ];
        })->values();

        // ======================================================================
        // NEW: Gender ratio
        // ======================================================================
        $genderStats = \DB::table('students')
            ->where('tenant_id', $tenantId)
            ->selectRaw('
                COALESCE(SUM(CASE WHEN gender = ? THEN 1 ELSE 0 END), 0) as male,
                COALESCE(SUM(CASE WHEN gender = ? THEN 1 ELSE 0 END), 0) as female,
                COALESCE(SUM(CASE WHEN gender NOT IN (?, ?) THEN 1 ELSE 0 END), 0) as other
            ', ['male', 'female', 'male', 'female'])
            ->first();

        $totalWithGender = (int) ($genderStats->male + $genderStats->female + $genderStats->other);
        $genderRatio = [
            'male' => (int) ($genderStats->male ?? 0),
            'female' => (int) ($genderStats->female ?? 0),
            'other' => (int) ($genderStats->other ?? 0),
            'male_percent' => $totalWithGender > 0 ? round(($genderStats->male / $totalWithGender) * 100, 1) : 0,
            'female_percent' => $totalWithGender > 0 ? round(($genderStats->female / $totalWithGender) * 100, 1) : 0,
            'total' => $totalWithGender,
        ];

        // ======================================================================
        // NEW: Top 3 funds by balance
        // ======================================================================
        $topFundsRaw = Fund::where('tenant_id', $tenantId)
            ->orderBy('balance', 'desc')
            ->limit(3)
            ->get();

        $totalFundBalance = Fund::where('tenant_id', $tenantId)->sum('balance') ?? 0;
        $topFunds = $topFundsRaw->map(function ($fund) use ($totalFundBalance) {
            return [
                'name' => $fund->name_bn ?? $fund->name ?? 'ফান্ড',
                'balance' => round((float) ($fund->balance ?? 0), 2),
                'percent_of_total' => $totalFundBalance > 0 ? round((($fund->balance ?? 0) / $totalFundBalance) * 100, 1) : 0,
            ];
        })->values();

        // ======================================================================
        // Response
        // ======================================================================
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
                'leave' => 0,
                'attendance_rate' => $attendanceRate,
            ],
            'finance' => [
                'total_donations' => $totalDonations,
                'total_expenses' => $totalExpenses,
                'total_fee_collected' => $totalFeeCollected,
                'net_balance' => $netBalance,
            ],
            // ---- New analytics fields ----
            'license' => [
                'status' => $licenseStatus,
                'plan' => $tenant->subscription_plan ?? 'ফ্রি',
                'remaining_days' => $remainingDays,
                'expiry_date' => $expiryDate ? Carbon::parse($expiryDate)->format('d M, Y') : null,
                'total_days' => $totalDays,
                'used_days' => $usedDays,
                'activated_at' => $tenant->activated_at ? Carbon::parse($tenant->activated_at)->format('d M, Y') : null,
            ],
            'class_wise_dues' => $classWiseDuesArray,
            'monthly_dues' => $monthlyDues,
            'class_wise_attendance_detail' => $classWiseAttendanceArray,
            'gender_ratio' => $genderRatio,
            'top_funds' => $topFunds,
        ], 'ড্যাশবোর্ড পরিসংখ্যান');
    }
}
