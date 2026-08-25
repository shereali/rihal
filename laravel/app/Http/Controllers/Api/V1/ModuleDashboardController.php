<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModuleDashboardController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        if ($tenantId === null) {
            return $this->errorResponse('Tenant context is required.', 403);
        }

        return $this->successResponse([
            'students' => $this->section('users', $tenantId, ['role' => 'student'], '/students'),
            'teachers' => $this->section('users', $tenantId, ['role' => 'teacher'], '/hr'),
            'attendance' => [
                'today' => $this->count('attendance_records', $tenantId, ['date' => now()->toDateString()]),
                'present' => $this->count('attendance_records', $tenantId, ['date' => now()->toDateString(), 'status' => 'present']),
                'link' => '/attendance',
            ],
            'exams' => $this->section('exams', $tenantId, [], '/exams'),
            'finance' => [
                'funds' => $this->count('funds', $tenantId),
                'donations' => $this->sum('donations', $tenantId, 'amount'),
                'expenses' => $this->sum('expenses', $tenantId, 'amount'),
                'link' => '/finance',
            ],
            'fees' => [
                'payments' => $this->sum('fee_payments', $tenantId, 'paid_amount'),
                'link' => '/fees',
            ],
            'loans' => [
                'total' => $this->count('loans', $tenantId),
                'outstanding' => $this->sum('loans', $tenantId, 'remaining_amount', ['status' => 'active']),
                'overdue' => $this->countOverdueLoans($tenantId),
                'link' => '/loan-due',
            ],
            'orphans' => [
                'total' => $this->count('orphans', $tenantId),
                'sponsored' => $this->count('orphans', $tenantId, ['sponsorship_status' => 'sponsored']),
                'active_sponsorships' => $this->count('orphan_sponsorships', $tenantId, ['status' => 'active']),
                'link' => '/orphan-sponsorship',
            ],
            'hostel' => $this->section('hostel_rooms', $tenantId, [], '/hostel'),
            'transport' => $this->section('transport_routes', $tenantId, [], '/transport'),
            'properties' => $this->section('properties', $tenantId, [], '/properties'),
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    private function section(string $table, ?int $tenantId, array $where, string $link): array
    {
        return ['total' => $this->count($table, $tenantId, $where), 'link' => $link];
    }

    private function query(string $table, ?int $tenantId)
    {
        if ($tenantId === null || !Schema::hasTable($table)) return null;
        if (!Schema::hasColumn($table, 'tenant_id')) return null;
        $query = DB::table($table)->where('tenant_id', $tenantId);
        if (Schema::hasColumn($table, 'deleted_at')) $query->whereNull('deleted_at');
        return $query;
    }

    private function count(string $table, ?int $tenantId, array $where = []): int
    {
        $query = $this->query($table, $tenantId);
        if (!$query) return 0;
        foreach ($where as $column => $value) {
            if (Schema::hasColumn($table, $column)) $query->where($column, $value);
        }
        return $query->count();
    }

    private function sum(string $table, ?int $tenantId, string $column, array $where = []): float
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) return 0;
        $query = $this->query($table, $tenantId);
        foreach ($where as $filter => $value) {
            if (Schema::hasColumn($table, $filter)) $query->where($filter, $value);
        }
        return (float) $query->sum($column);
    }

    private function countOverdueLoans(?int $tenantId): int
    {
        $query = $this->query('loans', $tenantId);
        return $query ? $query->where('status', 'active')->whereDate('due_date', '<', today())->count() : 0;
    }
}
