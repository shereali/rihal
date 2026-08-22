<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('exams', 'seat_hall_rows')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->unsignedInteger('seat_hall_rows')->nullable()->after('name_en');
                $table->unsignedInteger('seat_hall_cols')->nullable()->after('seat_hall_rows');
                $table->string('seat_venue', 255)->nullable()->after('seat_hall_cols');
                $table->boolean('seat_generated')->default(false)->after('seat_venue');
                $table->timestamp('seat_generated_at')->nullable()->after('seat_generated');
                $table->string('seat_number', 255)->nullable()->after('seat_generated_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('exams', 'seat_number')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->dropColumn([
                    'seat_hall_rows',
                    'seat_hall_cols',
                    'seat_venue',
                    'seat_generated',
                    'seat_generated_at',
                    'seat_number',
                ]);
            });
        }
    }
};
