<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TenantController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\TeacherController;
use App\Http\Controllers\Api\V1\ExamController;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\FinanceController;
use App\Http\Controllers\Api\V1\NoticeController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\AcademicController;
use App\Http\Controllers\Api\V1\GuardianController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\HostelController;
use App\Http\Controllers\Api\V1\TransportController;
use App\Http\Controllers\Api\V1\PropertyController;
use App\Http\Controllers\Api\V1\HRController;
use App\Http\Controllers\Api\V1\ExamResultController;
use App\Http\Controllers\Api\V1\MarkEntryController;
use App\Http\Controllers\Api\V1\EnrollmentController;
use App\Http\Controllers\Api\V1\TeacherAssignmentController;
use App\Http\Controllers\Api\V1\HomeworkController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('v1')->group(function () {
    // Auth routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Protected routes (require token)
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/user', [AuthController::class, 'user']);

        // Dashboard
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

        // Academic (read-only lookups for dropdowns)
        Route::get('/academic/classes', [AcademicController::class, 'classes']);
        Route::get('/academic/sections', [AcademicController::class, 'sections']);
        Route::get('/academic/subjects', [AcademicController::class, 'subjects']);

        // Guardian portal
        Route::get('/guardian/portal', [GuardianController::class, 'portal']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/absence', [NotificationController::class, 'sendAbsence']);
        Route::post('/notifications/fee-due', [NotificationController::class, 'sendFeeDue']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);

        // Reports (attendance matrix + exam results, with CSV export)
        Route::get('/reports/attendance', [ReportController::class, 'attendance']);
        Route::get('/reports/results', [ReportController::class, 'results']);
        Route::get('/reports/attendance/export', [ReportController::class, 'exportAttendanceCsv']);
        Route::get('/reports/results/export', [ReportController::class, 'exportResultsCsv']);

        // Tenants (super_admin only for create/delete, admin for view/update)
        Route::get('/tenants', [TenantController::class, 'index']);
        Route::post('/tenants', [TenantController::class, 'store'])->middleware('role:super_admin');
        Route::get('/tenants/{slug}', [TenantController::class, 'show']);
        Route::put('/tenants/{slug}', [TenantController::class, 'update']);
        Route::delete('/tenants/{slug}', [TenantController::class, 'destroy'])->middleware('role:super_admin');
        Route::get('/tenants/current', [TenantController::class, 'current']);

        // Users
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store'])->middleware('role:admin');
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('role:admin');

        // ─── Students ─────────────────────────────────────────────────────────
        Route::get('/students', [StudentController::class, 'index']);
        Route::post('/students', [StudentController::class, 'store']);
        Route::get('/students/{id}', [StudentController::class, 'show']);
        Route::put('/students/{id}', [StudentController::class, 'update']);
        Route::delete('/students/{id}', [StudentController::class, 'destroy']);

        // ─── Teachers ─────────────────────────────────────────────────────────
        Route::get('/teachers', [TeacherController::class, 'index']);
        Route::post('/teachers', [TeacherController::class, 'store']);
        Route::get('/teachers/{id}', [TeacherController::class, 'show']);
        Route::put('/teachers/{id}', [TeacherController::class, 'update']);
        Route::delete('/teachers/{id}', [TeacherController::class, 'destroy']);

        // ─── Notices ──────────────────────────────────────────────────────────
        Route::get('/notices', [NoticeController::class, 'index']);
        Route::post('/notices', [NoticeController::class, 'store']);
        Route::get('/notices/{id}', [NoticeController::class, 'show']);
        Route::put('/notices/{id}', [NoticeController::class, 'update']);
        Route::patch('/notices/{id}/pin', [NoticeController::class, 'pin']);
        Route::patch('/notices/{id}/unpin', [NoticeController::class, 'unpin']);
        Route::delete('/notices/{id}', [NoticeController::class, 'destroy']);

        // ─── Exams ────────────────────────────────────────────────────────────
        Route::get('/exams', [ExamController::class, 'index']);
        Route::post('/exams', [ExamController::class, 'store']);
        Route::get('/exams/{id}', [ExamController::class, 'show']);
        Route::put('/exams/{id}', [ExamController::class, 'update']);
        Route::delete('/exams/{id}', [ExamController::class, 'destroy']);
        Route::get('/exams/{id}/results', [ExamResultController::class, 'index']);
        Route::post('/exam-results', [ExamResultController::class, 'store']);
        Route::get('/exam-results/{id}', [ExamResultController::class, 'show']);
        Route::put('/exam-results/{id}', [ExamResultController::class, 'update']);
        Route::delete('/exam-results/{id}', [ExamResultController::class, 'destroy']);
        Route::patch('/exam-results/{id}/publish', [ExamResultController::class, 'publish']);
        Route::patch('/exam-results/{id}/unpublish', [ExamResultController::class, 'unpublish']);

        // ─── Attendance ───────────────────────────────────────────────────────
        Route::get('/attendance', [AttendanceController::class, 'index']);
        Route::post('/attendance', [AttendanceController::class, 'store']);
        Route::get('/attendance/{id}', [AttendanceController::class, 'show']);
        Route::put('/attendance/{id}', [AttendanceController::class, 'update']);
        Route::delete('/attendance/{id}', [AttendanceController::class, 'destroy']);
        Route::get('/attendance/summary', [AttendanceController::class, 'summary']);

        // ─── Finance ──────────────────────────────────────────────────────────
        Route::get('/finance/funds', [FinanceController::class, 'funds']);
        Route::post('/finance/funds', [FinanceController::class, 'storeFund']);
        Route::get('/finance/funds/{id}', [FinanceController::class, 'showFund']);
        Route::get('/finance/donors', [FinanceController::class, 'donors']);
        Route::post('/finance/donors', [FinanceController::class, 'storeDonor']);
        Route::get('/finance/donations', [FinanceController::class, 'donations']);
        Route::post('/finance/donations', [FinanceController::class, 'storeDonation']);
        Route::get('/finance/donations/{id}', [FinanceController::class, 'showDonation']);
        Route::get('/finance/expenses', [FinanceController::class, 'expenses']);
        Route::post('/finance/expenses', [FinanceController::class, 'storeExpense']);
        Route::get('/finance/expenses/{id}', [FinanceController::class, 'showExpense']);
        Route::get('/finance/vendors', [FinanceController::class, 'vendors']);
        Route::post('/finance/vendors', [FinanceController::class, 'storeVendor']);
        Route::get('/finance/fee-structures', [FinanceController::class, 'feeStructures']);
        Route::post('/finance/fee-structures', [FinanceController::class, 'storeFeeStructure']);
        Route::get('/finance/fee-payments', [FinanceController::class, 'feePayments']);
        Route::post('/finance/fee-payments', [FinanceController::class, 'storeFeePayment']);
        Route::get('/finance/journal-entries', [FinanceController::class, 'journalEntries']);
        Route::post('/finance/journal-entries', [FinanceController::class, 'storeJournalEntry']);
        Route::get('/finance/cash-books', [FinanceController::class, 'cashBooks']);
        Route::post('/finance/cash-books', [FinanceController::class, 'storeCashBook']);
        Route::get('/finance/summary', [FinanceController::class, 'summary']);
        Route::get('/finance/stocks', [FinanceController::class, 'stocks']);
        Route::post('/finance/stocks', [FinanceController::class, 'storeStock']);
        Route::get('/finance/stock-transactions', [FinanceController::class, 'stockTransactions']);
        Route::post('/finance/stock-transactions', [FinanceController::class, 'storeStockTransaction']);

        // ─── Homework ─────────────────────────────────────────────────────────
        Route::get('/homework-assignments', [HomeworkController::class, 'index']);
        Route::post('/homework-assignments', [HomeworkController::class, 'store']);
        Route::get('/homework-assignments/{id}', [HomeworkController::class, 'show']);
        Route::put('/homework-assignments/{id}', [HomeworkController::class, 'update']);
        Route::delete('/homework-assignments/{id}', [HomeworkController::class, 'destroy']);
        Route::get('/homework-assignments/{id}/submissions', [HomeworkController::class, 'submissions']);
        Route::post('/homework-submissions', [HomeworkController::class, 'storeSubmission']);
        Route::put('/homework-submissions/{id}', [HomeworkController::class, 'updateSubmission']);
        Route::get('/lesson-plans', [HomeworkController::class, 'lessonPlans']);
        Route::post('/lesson-plans', [HomeworkController::class, 'storeLessonPlan']);
        Route::put('/lesson-plans/{id}', [HomeworkController::class, 'updateLessonPlan']);
        Route::delete('/lesson-plans/{id}', [HomeworkController::class, 'destroyLessonPlan']);

        // ─── Hostel ───────────────────────────────────────────────────────────
        Route::get('/hostel-rooms', [HostelController::class, 'index']);
        Route::post('/hostel-rooms', [HostelController::class, 'store']);
        Route::get('/hostel-rooms/{id}', [HostelController::class, 'show']);
        Route::put('/hostel-rooms/{id}', [HostelController::class, 'update']);
        Route::delete('/hostel-rooms/{id}', [HostelController::class, 'destroy']);

        // ─── Transport ────────────────────────────────────────────────────────
        Route::get('/transport/routes', [TransportController::class, 'indexRoutes']);
        Route::post('/transport/routes', [TransportController::class, 'storeRoute']);
        Route::get('/transport/routes/{id}', [TransportController::class, 'showRoute']);
        Route::put('/transport/routes/{id}', [TransportController::class, 'updateRoute']);
        Route::delete('/transport/routes/{id}', [TransportController::class, 'destroyRoute']);
        Route::get('/transport/buses', [TransportController::class, 'indexBuses']);
        Route::post('/transport/buses', [TransportController::class, 'storeBus']);
        Route::get('/transport/buses/{id}', [TransportController::class, 'showBus']);
        Route::put('/transport/buses/{id}', [TransportController::class, 'updateBus']);
        Route::delete('/transport/buses/{id}', [TransportController::class, 'destroyBus']);

        // ─── HR ───────────────────────────────────────────────────────────────
        Route::get('/hr/staff', [HRController::class, 'index']);
        Route::post('/hr/staff', [HRController::class, 'store']);
        Route::get('/hr/staff/{id}', [HRController::class, 'show']);
        Route::put('/hr/staff/{id}', [HRController::class, 'update']);
        Route::delete('/hr/staff/{id}', [HRController::class, 'destroy']);
        Route::get('/hr/recruitments', [HRController::class, 'recruitments']);
        Route::post('/hr/recruitments', [HRController::class, 'storeRecruitment']);
        Route::put('/hr/recruitments/{id}', [HRController::class, 'updateRecruitment']);
        Route::delete('/hr/recruitments/{id}', [HRController::class, 'destroyRecruitment']);
        Route::get('/hr/recruitments/{id}/applications', [HRController::class, 'applications']);
        Route::post('/hr/applications', [HRController::class, 'storeApplication']);
        Route::put('/hr/applications/{id}', [HRController::class, 'updateApplication']);
        Route::get('/hr/hostel-visitors', [HRController::class, 'visitors']);
        Route::post('/hr/hostel-visitors', [HRController::class, 'storeVisitor']);
        Route::put('/hr/hostel-visitors/{id}', [HRController::class, 'updateVisitor']);
        Route::get('/hr/holidays', [HRController::class, 'holidays']);
        Route::post('/hr/holidays', [HRController::class, 'storeHoliday']);
        Route::put('/hr/holidays/{id}', [HRController::class, 'updateHoliday']);
        Route::delete('/hr/holidays/{id}', [HRController::class, 'destroyHoliday']);
        Route::get('/hr/events', [HRController::class, 'events']);
        Route::post('/hr/events', [HRController::class, 'storeEvent']);
        Route::put('/hr/events/{id}', [HRController::class, 'updateEvent']);
        Route::delete('/hr/events/{id}', [HRController::class, 'destroyEvent']);
        Route::get('/hr/events/{id}/registrations', [HRController::class, 'registrations']);
        Route::post('/hr/registrations', [HRController::class, 'storeRegistration']);

        // ─── Property ─────────────────────────────────────────────────────────
        Route::get('/properties', [PropertyController::class, 'index']);
        Route::post('/properties', [PropertyController::class, 'store']);
        Route::get('/properties/{id}', [PropertyController::class, 'show']);
        Route::put('/properties/{id}', [PropertyController::class, 'update']);
        Route::delete('/properties/{id}', [PropertyController::class, 'destroy']);

        // ─── Exam Results ──────────────────────────────────────────────────────
        Route::get('/exam-results', [ExamResultController::class, 'index']);
        Route::post('/exam-results', [ExamResultController::class, 'store']);
        Route::get('/exam-results/{id}', [ExamResultController::class, 'show']);
        Route::put('/exam-results/{id}', [ExamResultController::class, 'update']);
        Route::delete('/exam-results/{id}', [ExamResultController::class, 'destroy']);
        Route::patch('/exam-results/{id}/publish', [ExamResultController::class, 'publish']);
        Route::patch('/exam-results/{id}/unpublish', [ExamResultController::class, 'unpublish']);

        // ─── Mark Entries ─────────────────────────────────────────────────────
        Route::get('/mark-entries', [MarkEntryController::class, 'index']);
        Route::post('/mark-entries', [MarkEntryController::class, 'store']);
        Route::get('/mark-entries/{id}', [MarkEntryController::class, 'show']);
        Route::put('/mark-entries/{id}', [MarkEntryController::class, 'update']);
        Route::delete('/mark-entries/{id}', [MarkEntryController::class, 'destroy']);
        Route::post('/mark-entries/bulk-grade', [MarkEntryController::class, 'bulkGrade']);

        // ─── Enrollments ──────────────────────────────────────────────────────
        Route::get('/enrollments', [EnrollmentController::class, 'index']);
        Route::post('/enrollments', [EnrollmentController::class, 'store']);
        Route::get('/enrollments/{id}', [EnrollmentController::class, 'show']);
        Route::put('/enrollments/{id}', [EnrollmentController::class, 'update']);
        Route::delete('/enrollments/{id}', [EnrollmentController::class, 'destroy']);
        Route::patch('/enrollments/{id}/transfer', [EnrollmentController::class, 'transfer']);

        // ─── Teacher Assignments ──────────────────────────────────────────────
        Route::get('/teacher-assignments', [TeacherAssignmentController::class, 'index']);
        Route::post('/teacher-assignments', [TeacherAssignmentController::class, 'store']);
        Route::get('/teacher-assignments/{id}', [TeacherAssignmentController::class, 'show']);
        Route::put('/teacher-assignments/{id}', [TeacherAssignmentController::class, 'update']);
        Route::delete('/teacher-assignments/{id}', [TeacherAssignmentController::class, 'destroy']);
        Route::get('/teachers/{teacherId}/schedule', [TeacherAssignmentController::class, 'teacherSchedule']);
    });
});
