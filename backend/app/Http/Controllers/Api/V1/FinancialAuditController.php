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
        $filters = $request->validate([
            'action' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $logs = AuditLog::where('tenant_id', $request->user()->tenant_id)
            ->where(function ($query) {
                $query->where('action', 'like', 'loan.%')->orWhere('action', 'like', 'orphan.%')
                    ->orWhere('action', 'like', 'finance.%');
            })
            ->when(isset($filters['action']), fn ($query) => $query->where('action', $filters['action']))
            ->when(isset($filters['from']), fn ($query) => $query->whereDate('created_at', '>=', $filters['from']))
            ->when(isset($filters['to']), fn ($query) => $query->whereDate('created_at', '<=', $filters['to']))
            ->with('user:id,name_bn,name_en,email')
            ->latest('id')
            ->paginate($filters['per_page'] ?? 30);

        return $this->successResponse($logs);
    }
}
