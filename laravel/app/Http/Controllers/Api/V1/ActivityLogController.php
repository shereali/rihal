<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ActivityLogController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 20), 100);

        $query = ActivityLog::where('tenant_id', $user->tenant_id)
            ->when($request->has('action'), fn($q) => $q->where('action', $request->input('action')))
            ->when($request->has('user_id'), fn($q) => $q->where('user_id', $request->input('user_id')))
            ->when($request->has('entity_type'), fn($q) => $q->where('entity_type', $request->input('entity_type')))
            ->when($request->has('from') && $request->has('to'), fn($q) => $q->whereBetween('created_at', [
                $request->input('from'), $request->input('to')
            ]))
            ->when($request->has('from') && !$request->has('to'), fn($q) => $q->where('created_at', '>=', $request->input('from')))
            ->when(!$request->has('from') && $request->has('to'), fn($q) => $q->where('created_at', '<=', $request->input('to')))
            ->with('user')
            ->orderBy('created_at', 'desc');

        $logs = $query->paginate($perPage);

        // Flatten user info for frontend
        $logs->getCollection()->transform(function ($log) {
            $log->user_name = $log->user?->name_bn ?? $log->user?->name_en ?? $log->user?->email ?? 'অজ্ঞাত';
            $log->user_email = $log->user?->email ?? null;
            $log->user_type = $log->user?->role ?? null;
            return $log;
        });

        return $this->successResponse($logs, 'গতিবিধি লগ');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $log = ActivityLog::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->with('user')
            ->first();

        if (!$log) {
            return $this->errorResponse('লগ পাওয়া যায়নি', 404);
        }

        $log->user_name = $log->user?->name_bn ?? $log->user?->name_en ?? $log->user?->email ?? 'অজ্ঞাত';
        $log->user_email = $log->user?->email ?? null;
        $log->user_type = $log->user?->role ?? null;

        return $this->successResponse($log, 'লগ বিস্তারিত');
    }
}
