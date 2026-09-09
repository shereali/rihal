<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exam table
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->foreign('session_id')->references('id')->on('academic_sessions')->nullOnDelete();
            $table->string('class_id')->nullable();
            $table->string('name_bn');
            $table->string('name_en')->nullable();
            $table->string('exam_type')->default('final');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('status')->default('scheduled');
            $table->string('room_name')->nullable();
            $table->unsignedBigInteger('seat_plan_id')->nullable();
            // $table->foreign('seat_plan_id')->references('id')->on('exam_seat_plans')->nullOnDelete(); // same-migration FK
            $table->boolean('hall_ticket_generated')->default(false);
            $table->integer('total_marks')->nullable();
            $table->integer('passing_marks')->nullable();
            $table->integer('questions_count')->nullable();
            $table->json('grade_distribution')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('exam_type');
            $table->index('start_date');
        });

        // Exam Seat Plan
        Schema::create('exam_seat_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->json('seat_numbers')->nullable();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->foreign('generated_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Exam Question
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->foreign('subject_id')->references('id')->on('academic_subjects')->nullOnDelete();
            $table->longText('question_text');
            $table->string('question_type')->default('written');
            $table->integer('marks')->default(0);
            $table->integer('order')->default(0);
            $table->json('options')->nullable();
            $table->string('correct_answer')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['exam_id', 'subject_id']);
            $table->index('question_type');
        });

        // Mark Entry
        Schema::create('mark_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->foreign('subject_id')->references('id')->on('academic_subjects')->nullOnDelete();
            $table->integer('marks_obtained')->nullable();
            $table->integer('total_marks')->nullable();
            $table->string('grade')->nullable();
            $table->float('percentage')->nullable();
            $table->unsignedBigInteger('graded_by_teacher_id')->nullable();
            $table->foreign('graded_by_teacher_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->text('comment')->nullable();
            $table->string('validation_status')->default('pending');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['exam_id', 'student_id']);
            $table->index('validation_status');
            $table->index('grade');
        });

        // Result / Report Card
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('exam_id')->nullable();
            $table->foreign('exam_id')->references('id')->on('exams')->nullOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->foreign('session_id')->references('id')->on('academic_sessions')->nullOnDelete();
            $table->float('gpa')->nullable();
            $table->float('percentage')->nullable();
            $table->string('grade')->nullable();
            $table->string('pass_fail_status')->default('pending');
            $table->integer('class_position')->nullable();
            $table->integer('merit_list_position')->nullable();
            $table->string('report_card_pdf_url')->nullable();
            $table->string('qr_code')->nullable();
            $table->json('subject_results')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->foreign('published_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('parent_notified_at')->nullable();
            $table->string('parent_notified_method')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'session_id']);
            $table->index('pass_fail_status');
            $table->index('grade');
        });

        // Report Card Comment
        Schema::create('report_card_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained()->cascadeOnDelete();
            $table->text('ai_draft')->nullable();
            $table->boolean('teacher_reviewed')->default(false);
            $table->unsignedBigInteger('reviewed_by_teacher_id')->nullable();
            $table->foreign('reviewed_by_teacher_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_card_comments');
        Schema::dropIfExists('results');
        Schema::dropIfExists('mark_entries');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exam_seat_plans');
        Schema::dropIfExists('exams');
    }
};
