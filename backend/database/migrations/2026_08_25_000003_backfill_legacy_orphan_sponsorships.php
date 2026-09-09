<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orphans') || !Schema::hasTable('orphan_sponsorships')) return;

        DB::table('orphans')->whereNotNull('sponsor_id')->whereNull('deleted_at')->orderBy('id')
            ->chunkById(100, function ($orphans) {
                foreach ($orphans as $orphan) {
                    $startsAt = $orphan->sponsorship_start_date
                        ?: (isset($orphan->created_at) ? substr((string) $orphan->created_at, 0, 10) : now()->toDateString());
                    DB::table('orphan_sponsorships')->updateOrInsert([
                        'orphan_id' => $orphan->id,
                        'donor_id' => $orphan->sponsor_id,
                        'starts_at' => $startsAt,
                    ], [
                        'tenant_id' => $orphan->tenant_id,
                        'monthly_commitment' => $orphan->monthly_amount ?? 0,
                        'ends_at' => $orphan->sponsorship_end_date,
                        'status' => in_array($orphan->sponsorship_status, ['closed', 'completed']) ? 'ended' : 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Legacy sponsor_id remains intact, so this data migration is intentionally non-destructive.
    }
};
