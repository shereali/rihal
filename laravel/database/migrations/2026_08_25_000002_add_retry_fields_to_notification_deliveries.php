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
        if (!Schema::hasTable('notification_deliveries')) return;
        Schema::table('notification_deliveries', function (Blueprint $table) {
            $columns = array_values(array_filter(
                ['dedupe_key', 'attempts', 'last_attempted_at'],
                fn (string $column) => Schema::hasColumn('notification_deliveries', $column)
            ));
            if ($columns) $table->dropColumn($columns);
        });
    }
};
