<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class FinancialAuditService
{
    public function record(string $action, Model $entity, array $changes, ?Request $request = null, ?string $description = null): AuditLog
    {
        return AuditLog::create([
            'tenant_id' => $entity->tenant_id,
            'user_id' => $request?->user()?->id,
            'action' => $action,
            'entity_type' => $entity::class,
            'entity_id' => $entity->getKey(),
            'changes' => $changes,
            'ip_address' => $request?->ip(),
            'description' => $description,
        ]);
    }
}
