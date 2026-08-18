<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Academic Sessions
        Schema::create('academic_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name_bn');
            $table->string('name_en')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('active');
            $table->json('terms')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('tenant_id');
        });

        // Academic Classes
        Schema::create('academic_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->foreign('session_id')->references('id')->on('academic_sessions')->nullOnDelete();
            $table->string('name_bn');
            $table->string('name_en')->nullable();
            $table->string('class_type')->default('regular');
            $table->integer('grade_level')->default(0);
            $table->string('room_name')->nullable();
            $table->integer('student_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'session_id']);
            $table->index('class_type');
        });

        // Academic Sections
        Schema::create('academic_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->foreign('class_id')->references('id')->on('academic_classes')->nullOnDelete();
            $table->string('name_bn');
            $table->string('name_en')->nullable();
            $table->string('section_type')->default('regular');
            $table->integer('student_count')->default(0);
            $table->string('room_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'class_id']);
            $table->index('section_type');
        });

        // Academic Subjects
        Schema::create('academic_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name_bn');
            $table->string('name_en')->nullable();
            $table->string('code')->nullable();
            $table->string('subject_type')->default('regular');
            $table->string('education_board')->nullable();
            $table->integer('teaching_hours_per_week')->default(0);
            $table->integer('credit_hours')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index('subject_type');
            $table->index('tenant_id');
        });

        // Academic Timetable
        Schema::create('academic_timetable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->foreign('class_id')->references('id')->on('academic_classes')->nullOnDelete();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->foreign('section_id')->references('id')->on('academic_sections')->nullOnDelete();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->foreign('subject_id')->references('id')->on('academic_subjects')->nullOnDelete();
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
            $table->string('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'day_of_week']);
        });

        // Enrollments
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->foreign('class_id')->references('id')->on('academic_classes')->nullOnDelete();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->foreign('section_id')->references('id')->on('academic_sections')->nullOnDelete();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->foreign('session_id')->references('id')->on('academic_sessions')->nullOnDelete();
            $table->string('enrollment_number')->unique();
            $table->date('enrollment_date');
            $table->string('status')->default('enrolled');
            $table->date('promotion_date')->nullable();
            $table->string('promoted_to_class')->nullable();
            $table->string('promoted_to_section')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'student_id']);
            $table->index('status');
        });

        // Teacher Assignments
        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->foreign('subject_id')->references('id')->on('academic_subjects')->nullOnDelete();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->foreign('class_id')->references('id')->on('academic_classes')->nullOnDelete();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->foreign('section_id')->references('id')->on('academic_sections')->nullOnDelete();
            $table->string('assignment_type')->default('regular');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'teacher_id']);
            $table->index(['class_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_assignments');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('academic_timetable');
        Schema::dropIfExists('academic_subjects');
        Schema::dropIfExists('academic_sections');
        Schema::dropIfExists('academic_classes');
        Schema::dropIfExists('academic_sessions');
    }
};
