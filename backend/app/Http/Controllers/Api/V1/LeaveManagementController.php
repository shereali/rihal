<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\LeaveApplication;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;

class LeaveManagementController extends Controller
{
    public function index(Request $request)
    {
        $tenant = $request->get('tenant');
        $query = LeaveApplication::where('tenant_id', $tenant?->id)
            ->with('user')
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('leave_type'), fn($q) => $q->where('leave_type', $request->leave_type))
            ->orderByDesc('id');

        $perPage = min((int) $request->query('per_page', 15), 100);
        $items = $query->paginate($perPage);

        return ApiResource::collection($items, fn($leave) => [
            'id' => $leave->id,
            'user_id' => $leave->user_id,
            'user_name_bn' => $leave->user->name_bn ?? '',
            'user_name' => $leave->user->name ?? '',
            'user_email' => $leave->user->email ?? '',
            'leave_type' => $leave->leave_type,
            'title_bn' => $leave->title_bn,
            'title' => $leave->title,
            'description_bn' => $leave->description_bn,
            'start_date' => $leave->start_date->format('d M, Y'),
            'end_date' => $leave->end_date->format('d M, Y'),
            'days_count' => $leave->days_count,
            'status' => $leave->status,
            'notes' => $leave->notes,
            'is_urgent' => (bool) ($leave->is_urgent ?? false),
            'created_at' => $leave->created_at?->format('d M, Y h:i A'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'leave_type' => ['required', 'string', 'in:ছুটি,রোগ,ব্যক্তিগত,মাতৃত্ব,সিহ্ব,অনুপস্থিতি,অন্য'],
            'title_bn' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string'],
            'description_bn' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string'],
            'is_urgent' => ['boolean'],
        ]);

        $tenant = $request->get('tenant');
        $validated['tenant_id'] = $tenant?->id;
        $validated['status'] = 'pending';
        $validated['days_count'] = \Carbon\Carbon::parse($validated['start_date'])->diffInDays(\Carbon\Carbon::parse($validated['end_date'])) + 1;

        $leave = LeaveApplication::create($validated);
        return ApiResource::item($leave->fresh()->load('user'), fn($l) => [
            'id' => $l->id, 'user_id' => $l->user_id,
            'user_name_bn' => $l->user->name_bn ?? '', 'user_name' => $l->user->name ?? '',
            'user_email' => $l->user->email ?? '',
            'leave_type' => $l->leave_type, 'title_bn' => $l->title_bn, 'title' => $l->title,
            'description_bn' => $l->description_bn,
            'start_date' => $l->start_date->format('d M, Y'),
            'end_date' => $l->end_date->format('d M, Y'),
            'days_count' => $l->days_count, 'status' => $l->status,
            'notes' => $l->notes, 'is_urgent' => (bool) ($l->is_urgent ?? false),
            'created_at' => $l->created_at?->format('d M, Y h:i A'),
        ]);
    }

    public function show(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $leave = LeaveApplication::where('tenant_id', $tenant?->id)->with('user')->findOrFail($id);
        return ApiResource::item($leave, fn($l) => [
            'id' => $l->id, 'user_id' => $l->user_id,
            'user_name_bn' => $l->user->name_bn ?? '', 'user_name' => $l->user->name ?? '',
            'user_email' => $l->user->email ?? '',
            'leave_type' => $l->leave_type, 'title_bn' => $l->title_bn, 'title' => $l->title,
            'description_bn' => $l->description_bn,
            'start_date' => $l->start_date->format('d M, Y'),
            'end_date' => $l->end_date->format('d M, Y'),
            'days_count' => $l->days_count, 'status' => $l->status,
            'notes' => $l->notes, 'is_urgent' => (bool) ($l->is_urgent ?? false),
            'created_at' => $l->created_at?->format('d M, Y h:i A'),
        ]);
    }

    public function update(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $leave = LeaveApplication::where('tenant_id', $tenant?->id)->findOrFail($id);
        $validated = $request->validate([
            'leave_type' => ['sometimes', 'string', 'in:ছুটি,রোগ,ব্যক্তিগত,মাতৃত্ব,সিহ্ব,অনুপস্থিতি,অন্য'],
            'title_bn' => ['sometimes', 'string', 'max:255'],
            'title' => ['nullable', 'string'],
            'description_bn' => ['sometimes', 'string'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string'],
            'is_urgent' => ['boolean'],
            'status' => ['sometimes', 'string', 'in:pending,approved,rejected,cancelled'],
        ]);
        $leave->update($validated);
        return ApiResource::item($leave->fresh()->load('user'), fn($l) => [
            'id' => $l->id, 'user_id' => $l->user_id,
            'user_name_bn' => $l->user->name_bn ?? '', 'user_name' => $l->user->name ?? '',
            'user_email' => $l->user->email ?? '',
            'leave_type' => $l->leave_type, 'title_bn' => $l->title_bn, 'title' => $l->title,
            'description_bn' => $l->description_bn,
            'start_date' => $l->start_date->format('d M, Y'),
            'end_date' => $l->end_date->format('d M, Y'),
            'days_count' => $l->days_count, 'status' => $l->status,
            'notes' => $l->notes, 'is_urgent' => (bool) ($l->is_urgent ?? false),
            'created_at' => $l->created_at?->format('d M, Y h:i A'),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $leave = LeaveApplication::where('tenant_id', $tenant?->id)->findOrFail($id);
        $leave->delete();
        return response()->json(['success' => true, 'message' => 'ছুটির আবেদন মুছে ফেলা হয়েছে।']);
    }

    public function myLeave(Request $request)
    {
        $user = $request->user();
        $tenant = $request->get('tenant');
        $query = LeaveApplication::where('tenant_id', $tenant?->id)
            ->where('user_id', $user->id)
            ->orderByDesc('id');

        $perPage = min((int) $request->query('per_page', 15), 100);
        $items = $query->paginate($perPage);

        return ApiResource::collection($items, fn($l) => [
            'id' => $l->id, 'user_id' => $l->user_id,
            'user_name_bn' => $l->user->name_bn ?? '', 'user_name' => $l->user->name ?? '',
            'user_email' => $l->user->email ?? '',
            'leave_type' => $l->leave_type, 'title_bn' => $l->title_bn, 'title' => $l->title,
            'description_bn' => $l->description_bn,
            'start_date' => $l->start_date->format('d M, Y'),
            'end_date' => $l->end_date->format('d M, Y'),
            'days_count' => $l->days_count, 'status' => $l->status,
            'notes' => $l->notes, 'is_urgent' => (bool) ($l->is_urgent ?? false),
            'created_at' => $l->created_at?->format('d M, Y h:i A'),
        ]);
    }
}
