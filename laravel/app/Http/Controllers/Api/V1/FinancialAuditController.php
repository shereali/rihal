<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialAuditController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $logs = AuditLog::where('tenant_id', $request->user()->tenant_id)
            ->where(function ($query) {
                $query->where('action', 'like', 'loan.%')->orWhere('action', 'like', 'orphan.%')
                    ->orWhere('action', 'like', 'finance.%');
            })
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->input('action')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('to')))
            ->with('user:id,name_bn,name_en,email')
            ->latest('id')
            ->paginate(min((int) $request->input('per_page', 30), 100));

        return $this->successResponse($logs);
    }
}
