<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ReminderTask;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;

class ReminderTaskController extends Controller
{
    public function index(Request $request)
    {
        $tenant = $request->get('tenant');
        $query = ReminderTask::where('tenant_id', $tenant?->id)
            ->with('user')
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->when($request->filled('priority'), fn($q) => $q->where('priority', $request->priority))
            ->orderByDesc('id');

        $perPage = min((int) $request->query('per_page', 15), 100);
        $items = $query->paginate($perPage);

        return ApiResource::collection($items, function ($task) {
            return [
                'id' => $task->id,
                'title_bn' => $task->title_bn,
                'title' => $task->title,
                'type' => $task->type,
                'priority' => $task->priority,
                'status' => $task->status,
                'scheduled_for' => $task->scheduled_for?->format('d M, Y h:i A'),
                'sent_at' => $task->sent_at?->format('d M, Y h:i A'),
                'delivery_channels' => $task->delivery_channels ?? [],
                'description_bn' => $task->description_bn,
                'is_recurring' => (bool) ($task->is_recurring ?? false),
                'is_active' => (bool) ($task->is_active ?? true),
                'created_by' => $task->created_by,
                'created_by_user' => $task->whenLoaded('user', fn() => [
                    'id' => $task->user?->id,
                    'name_bn' => $task->user?->name_bn ?? $task->user?->name,
                    'name' => $task->user?->name,
                ]),
                'created_at' => $task->created_at?->format('d M, Y h:i A'),
            ];
        });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title_bn' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'type' => 'required|string|in:reminder alert,sms,email,system,attendance,billing,academic',
            'priority' => 'nullable|string|in:low,normal,high,urgent',
            'status' => 'nullable|string|in:pending,sent,acknowledged,failed,archived',
            'scheduled_for' => 'nullable|date',
            'description_bn' => 'nullable|string|max:1000',
            'delivery_channels' => 'nullable|array',
            'is_recurring' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['tenant_id'] = $request->get('tenant')?->id;
        $validated['scheduled_for'] = $validated['scheduled_for'] ? \Carbon\Carbon::parse($validated['scheduled_for']) : null;
        $validated['status'] = $request->status ?? 'pending';
        $validated['priority'] = $request->priority ?? 'normal';
        $validated['is_active'] = $request->is_active ?? true;
        $validated['is_recurring'] = $request->is_recurring ?? false;
        $validated['created_by'] = $request->user()?->id;
        $validated['delivery_channels'] = $request->delivery_channels ?? ['in_app'];

        $task = ReminderTask::create($validated);

        return ApiResource::success([
            'message' => 'রিমাইন্ডার টাস্ক তৈরি হয়েছে।',
            'data' => [
                'id' => $task->id,
                'title_bn' => $task->title_bn,
                'type' => $task->type,
                'priority' => $task->priority,
                'status' => $task->status,
                'scheduled_for' => $task->scheduled_for?->format('d M, Y h:i A'),
                'is_active' => (bool) $task->is_active,
                'created_at' => $task->created_at?->format('d M, Y h:i A'),
            ],
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $task = ReminderTask::where('tenant_id', $tenant?->id)
            ->with('user')
            ->findOrFail($id);

        return ApiResource::success([
            'id' => $task->id,
            'title_bn' => $task->title_bn,
            'title' => $task->title,
            'type' => $task->type,
            'priority' => $task->priority,
            'status' => $task->status,
            'scheduled_for' => $task->scheduled_for?->format('d M, Y h:i A'),
            'sent_at' => $task->sent_at?->format('d M, Y h:i A'),
            'delivery_channels' => $task->delivery_channels ?? [],
            'description_bn' => $task->description_bn,
            'is_recurring' => (bool) ($task->is_recurring ?? false),
            'is_active' => (bool) ($task->is_active ?? true),
            'created_by' => $task->created_by,
            'created_by_user' => [
                'id' => $task->user?->id,
                'name_bn' => $task->user?->name_bn ?? $task->user?->name,
                'name' => $task->user?->name,
            ],
            'created_at' => $task->created_at?->format('d M, Y h:i A'),
            'updated_at' => $task->updated_at?->format('d M, Y h:i A'),
        ]);
    }

    public function update(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $task = ReminderTask::where('tenant_id', $tenant?->id)->findOrFail($id);

        $validated = $request->validate([
            'title_bn' => 'sometimes|string|max:255',
            'title' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:reminder alert,sms,email,system,attendance,billing,academic',
            'priority' => 'nullable|string|in:low,normal,high,urgent',
            'status' => 'nullable|string|in:pending,sent,acknowledged,failed,archived',
            'scheduled_for' => 'nullable|date',
            'description_bn' => 'nullable|string|max:1000',
            'delivery_channels' => 'nullable|array',
            'is_recurring' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['scheduled_for']) && $validated['scheduled_for']) {
            $validated['scheduled_for'] = \Carbon\Carbon::parse($validated['scheduled_for']);
        }

        $task->update($validated);

        return ApiResource::success([
            'message' => 'রিমাইন্ডার আপডেট হয়েছে।',
            'data' => [
                'id' => $task->id,
                'title_bn' => $task->title_bn,
                'type' => $task->type,
                'priority' => $task->priority,
                'status' => $task->status,
                'scheduled_for' => $task->scheduled_for?->format('d M, Y h:i A'),
                'updated_at' => $task->updated_at?->format('d M, Y h:i A'),
            ],
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $task = ReminderTask::where('tenant_id', $tenant?->id)->findOrFail($id);
        $task->delete();

        return ApiResource::success(['message' => 'রিমাইন্ডার ডিলিট হয়েছে।']);
    }

    public function stats(Request $request)
    {
        $tenant = $request->get('tenant');

        $pending = ReminderTask::where('tenant_id', $tenant?->id)->where('status', 'pending')->count();
        $sent = ReminderTask::where('tenant_id', $tenant?->id)->where('status', 'sent')->count();
        $acknowledged = ReminderTask::where('tenant_id', $tenant?->id)->where('status', 'acknowledged')->count();
        $failed = ReminderTask::where('tenant_id', $tenant?->id)->where('status', 'failed')->count();
        $today = ReminderTask::where('tenant_id', $tenant?->id)
            ->whereDate('scheduled_for', now()->toDateString())
            ->count();

        return ApiResource::success([
            'pending' => $pending,
            'sent' => $sent,
            'acknowledged' => $acknowledged,
            'failed' => $failed,
            'today' => $today,
            'total' => ReminderTask::where('tenant_id', $tenant?->id)->count(),
        ]);
    }
}