<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ComplaintFeedback;
use App\Models\DutyAssignment;
use App\Models\NeedyStudentAssistance;
use App\Models\StaffDischarge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AdministrationController extends Controller
{
    // ─── Complaints & Feedback ──────────────────────────────────────────
    public function complaints(Request $request): JsonResponse
    {
        $complaints = ComplaintFeedback::when($request->category, fn($q, $c) => $q->where('category', $c))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest('date')
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $complaints,
        ]);
    }

    public function storeComplaint(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sender_name' => 'required|string|max:255',
            'sender_type' => 'required|string|max:100',
            'category'    => 'required|string|max:100',
            'priority'    => 'required|string|in:urgent,medium,low',
            'subject'     => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $complaint = ComplaintFeedback::create(array_merge($validated, [
            'tenant_id'   => $request->user()?->tenant_id,
            'tracking_id' => 'CMP-' . date('Y') . '-' . rand(100, 999),
            'date'        => now()->toDateString(),
            'status'      => 'pending',
        ]));

        return response()->json([
            'status' => 201,
            'data' => $complaint,
        ], 201);
    }

    public function toggleResolveComplaint($id): JsonResponse
    {
        $complaint = ComplaintFeedback::findOrFail($id);
        $newStatus = $complaint->status === 'resolved' ? 'pending' : 'resolved';
        $complaint->update([
            'status' => $newStatus,
            'resolved_at' => $newStatus === 'resolved' ? now() : null,
        ]);

        return response()->json([
            'status' => 200,
            'data' => $complaint,
        ]);
    }

    // ─── Duty Assignments ───────────────────────────────────────────────
    public function duties(Request $request): JsonResponse
    {
        $duties = DutyAssignment::when($request->department, fn($q, $d) => $q->where('department', $d))
            ->latest()
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $duties,
        ]);
    }

    public function storeDuty(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'department'  => 'required|string|max:100',
            'person_name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:100',
            'phone'       => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $duty = DutyAssignment::create(array_merge($validated, [
            'tenant_id' => $request->user()?->tenant_id,
        ]));

        return response()->json([
            'status' => 201,
            'data' => $duty,
        ], 201);
    }

    public function destroyDuty($id): JsonResponse
    {
        DutyAssignment::findOrFail($id)->delete();
        return response()->json([
            'status' => 200,
            'message' => 'দায়িত্ব অর্পণ বাতিল করা হয়েছে',
        ]);
    }

    // ─── Staff Discharge Registry ───────────────────────────────────────
    public function discharges(Request $request): JsonResponse
    {
        $discharges = StaffDischarge::latest('discharge_date')->get();
        return response()->json([
            'status' => 200,
            'data' => $discharges,
        ]);
    }

    public function storeDischarge(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'designation'    => 'required|string|max:100',
            'department'     => 'nullable|string|max:100',
            'discharge_date' => 'required|date',
            'reason'         => 'required|string|max:255',
        ]);

        $discharge = StaffDischarge::create(array_merge($validated, [
            'tenant_id' => $request->user()?->tenant_id,
            'staff_id'  => 'STF-' . rand(100, 999),
            'status'    => 'cleared',
        ]));

        return response()->json([
            'status' => 201,
            'data' => $discharge,
        ], 201);
    }

    // ─── Needy Students Assistance ──────────────────────────────────────
    public function needy(Request $request): JsonResponse
    {
        $list = NeedyStudentAssistance::latest()->get();
        return response()->json([
            'status' => 200,
            'data' => $list,
        ]);
    }

    public function storeNeedy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_name'    => 'required|string|max:255',
            'class_name'      => 'required|string|max:100',
            'support_type'    => 'required|string|max:100',
            'fund_source'     => 'required|string|max:100',
            'amount_discount' => 'nullable|string|max:50',
        ]);

        $entry = NeedyStudentAssistance::create(array_merge($validated, [
            'tenant_id' => $request->user()?->tenant_id,
            'status'    => 'active',
        ]));

        return response()->json([
            'status' => 201,
            'data' => $entry,
        ], 201);
    }

    // ─── Clear Cache ────────────────────────────────────────────────────
    public function clearCache(Request $request): JsonResponse
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');

        return response()->json([
            'status' => 200,
            'message' => 'সিস্টেম ক্যাশ সফলভাবে সাফ করা হয়েছে',
        ]);
    }
}
