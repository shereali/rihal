<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notification_deliveries')) return;

        Schema::table('notification_deliveries', function (Blueprint $table) {
            if (!Schema::hasColumn('notification_deliveries', 'dedupe_key')) {
                $table->string('dedupe_key', 64)->nullable()->unique()->after('type');
            }
            if (!Schema::hasColumn('notification_deliveries', 'attempts')) {
                $table->unsignedInteger('attempts')->default(0)->after('status');
            }
            if (!Schema::hasColumn('notification_deliveries', 'last_attempted_at')) {
                $table->timestamp('last_attempted_at')->nullable()->after('provider_response');
            }
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive: these columns are part of migration
        // 000001 on fresh installs. This compatibility migration only repairs
        // databases where 000001 had already run before the columns existed.
    }
};
