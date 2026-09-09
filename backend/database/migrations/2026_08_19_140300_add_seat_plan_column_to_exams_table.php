<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('exams', 'seat_plan')) {
            DB::transaction(function () {
                Schema::table('exams', function (Blueprint $table) {
                    $table->json('seat_plan')->nullable()->after('seat_number');
                });
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('exams', 'seat_plan')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->dropColumn('seat_plan');
            });
        }
    }
};
