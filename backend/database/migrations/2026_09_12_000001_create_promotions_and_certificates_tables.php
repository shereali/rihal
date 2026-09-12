<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Promotions
        if (!Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->unsignedBigInteger('from_class_id')->nullable();
                $table->foreign('from_class_id')->references('id')->on('academic_classes')->nullOnDelete();
                $table->unsignedBigInteger('to_class_id')->nullable();
                $table->foreign('to_class_id')->references('id')->on('academic_classes')->nullOnDelete();
                $table->string('academic_year', 20);
                $table->date('promotion_date');
                $table->string('status', 30)->default('approved');
                $table->text('comments')->nullable();
                $table->unsignedBigInteger('promoted_by')->nullable();
                $table->foreign('promoted_by')->references('id')->on('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'student_id']);
                $table->index(['tenant_id', 'academic_year']);
                $table->index('status');
            });
        }

        // 2. Certificate Templates
        if (!Schema::hasTable('certificate_templates')) {
            Schema::create('certificate_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('title', 150);
                $table->string('template_type', 50)->default('annual');
                $table->json('template_data')->nullable();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->foreign('class_id')->references('id')->on('academic_classes')->nullOnDelete();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->foreign('subject_id')->references('id')->on('academic_subjects')->nullOnDelete();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('issued_by')->nullable();
                $table->foreign('issued_by')->references('id')->on('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index('tenant_id');
                $table->index('template_type');
            });
        }

        // 3. Issued Certificates
        if (!Schema::hasTable('issued_certificates')) {
            Schema::create('issued_certificates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('template_id')->nullable();
                $table->foreign('template_id')->references('id')->on('certificate_templates')->nullOnDelete();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->foreign('class_id')->references('id')->on('academic_classes')->nullOnDelete();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->foreign('subject_id')->references('id')->on('academic_subjects')->nullOnDelete();
                $table->string('certificate_number', 100)->unique();
                $table->date('issue_date');
                $table->string('authorized_by', 100)->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'student_id']);
                $table->index('issue_date');
            });
        }

        // 4. Certificate Marks
        if (!Schema::hasTable('certificate_marks')) {
            Schema::create('certificate_marks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('template_id')->nullable();
                $table->foreign('template_id')->references('id')->on('certificate_templates')->nullOnDelete();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->foreign('class_id')->references('id')->on('academic_classes')->nullOnDelete();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->foreign('subject_id')->references('id')->on('academic_subjects')->nullOnDelete();
                $table->integer('mark_obtained')->default(0);
                $table->integer('mark_total')->default(100);
                $table->integer('passing_mark')->nullable();
                $table->string('grade', 10)->nullable();
                $table->string('remark', 200)->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'student_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_marks');
        Schema::dropIfExists('issued_certificates');
        Schema::dropIfExists('certificate_templates');
        Schema::dropIfExists('promotions');
    }
};
