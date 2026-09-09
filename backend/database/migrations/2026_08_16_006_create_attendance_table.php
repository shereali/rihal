<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Attendance Device (must be created before Attendance Record due to FK)
        Schema::create('attendance_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('device_name');
            $table->string('device_type')->default('fingerprint');
            $table->string('ip_address')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('status')->default('active');
            $table->unsignedBigInteger('assigned_to_class_id')->nullable();
            $table->string('assigned_to_teacher_id')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->json('sync_status')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('device_type');
        });

        // Attendance Record
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->foreign('student_id')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->foreign('staff_id')->references('id')->on('users')->nullOnDelete();
            $table->date('date');
            $table->string('status')->default('present');
            $table->string('method')->default('manual');
            $table->unsignedBigInteger('device_id')->nullable();
            $table->foreign('device_id')->references('id')->on('attendance_devices')->nullOnDelete();
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->integer('late_minutes')->nullable();
            $table->text('absence_reason')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('parent_notified')->default(false);
            $table->timestamp('parent_notified_at')->nullable();
            $table->string('parent_notified_method')->nullable();
            $table->boolean('is_boarder')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'date']);
            $table->index('status');
            $table->index('method');
            $table->index('student_id');
        });

        // Attendance Pattern (aggregated)
        Schema::create('attendance_patterns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->float('attendance_rate')->nullable();
            $table->integer('total_days')->nullable();
            $table->integer('present_days')->nullable();
            $table->integer('absent_days')->nullable();
            $table->integer('late_days')->nullable();
            $table->json('risk_factors')->nullable();
            $table->float('risk_score')->nullable();
            $table->string('risk_level')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'period_start']);
            $table->index('risk_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_patterns');
        Schema::dropIfExists('attendance_devices');
        Schema::dropIfExists('attendance_records');
    }
};
