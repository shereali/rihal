<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\NotificationService;
use App\Http\Controllers\Api\ApiController;
use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends ApiController
{
    protected NotificationService $service;

    public function __construct(NotificationService $service)
    {
        $this->service = $service;
    }

    /**
     * List notifications.
     * - guardian role: only their own received notifications
     * - others (admin/teacher): all sent in the tenant
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'guardian') {
            $query = AppNotification::where('recipient_id', $user->id);
        } else {
            $query = AppNotification::where('tenant_id', $user->tenant_id);
        }

        $notifications = $query->with('recipient:id,name_bn,name_en')
            ->orderBy('created_at', 'desc')
            ->paginate(min((int) $request->input('per_page', 20), 100));

        return $this->successResponse($notifications);
    }

    /**
     * Send absence notifications for the given attendance record ids.
     */
    public function sendAbsence(Request $request): JsonResponse
    {
        $request->validate([
            'attendance_record_ids' => 'required|array|min:1',
            'attendance_record_ids.*' => 'integer|exists:attendance_records,id',
            'channel' => 'nullable|string|in:in_app,sms,email',
        ]);

        $sent = $this->service->sendAbsence(
            $request->input('attendance_record_ids'),
            $request->input('channel', 'in_app')
        );

        return $this->successResponse(['sent' => $sent], "{$sent}টি অনুপস্থিতি নোটিফিকেশন পাঠানো হয়েছে");
    }

    /**
     * Send fee-due notifications for the given fee payment ids.
     */
    public function sendFeeDue(Request $request): JsonResponse
    {
        $request->validate([
            'fee_payment_ids' => 'required|array|min:1',
            'fee_payment_ids.*' => 'integer|exists:fee_payments,id',
            'channel' => 'nullable|string|in:in_app,sms,email',
        ]);

        $sent = $this->service->sendFeeDue(
            $request->input('fee_payment_ids'),
            $request->input('channel', 'in_app')
        );

        return $this->successResponse(['sent' => $sent], "{$sent}টি ফি-বকেয়া নোটিফিকেশন পাঠানো হয়েছে");
    }

    /**
     * Mark a notification as read (guardian only, own record).
     */
    public function markRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $notification = AppNotification::where('id', $id)
            ->where('recipient_id', $user->id)
            ->firstOrFail();

        $notification->update(['is_read' => true]);

        return $this->successResponse($notification, 'পঠিত হিসেবে চিহ্নিত করা হয়েছে');
    }
}
