<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Homework Assignment
        Schema::create('homework_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
            $table->string('class_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->foreign('subject_id')->references('id')->on('academic_subjects')->nullOnDelete();
            $table->string('title_bn');
            $table->string('title_en')->nullable();
            $table->longText('description')->nullable();
            $table->date('due_date');
            $table->time('due_time')->nullable();
            $table->json('attachments')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['class_id', 'subject_id']);
            $table->index('status');
            $table->index('due_date');
        });

        // Homework Submission
        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_assignment_id')->constrained('homework_assignments')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('submission_type')->default('file');
            $table->string('file_url')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('submitted');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->integer('marks')->nullable();
            $table->text('teacher_feedback')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['homework_assignment_id', 'student_id']);
            $table->index('status');
        });

        // Lesson Plan
        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
            $table->string('class_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->foreign('subject_id')->references('id')->on('academic_subjects')->nullOnDelete();
            $table->string('topic_bn');
            $table->string('topic_en')->nullable();
            $table->date('planned_date');
            $table->integer('duration_minutes')->nullable();
            $table->string('teaching_method')->nullable();
            $table->json('resources')->nullable();
            $table->json('assessment_plan')->nullable();
            $table->string('curriculum_standard')->nullable();
            $table->string('status')->default('planned');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['teacher_id', 'class_id']);
            $table->index('status');
            $table->index('planned_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_plans');
        Schema::dropIfExists('homework_submissions');
        Schema::dropIfExists('homework_assignments');
    }
};
