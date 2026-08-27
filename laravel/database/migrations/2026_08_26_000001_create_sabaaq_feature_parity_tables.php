<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Lesson Evaluations
        if (!Schema::hasTable('lesson_evaluations')) {
            Schema::create('lesson_evaluations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('class_id');
                $table->unsignedBigInteger('subject_id');
                $table->unsignedBigInteger('book_id')->default(0);
                $table->date('date');
                $table->tinyInteger('day');
                $table->tinyInteger('month');
                $table->smallInteger('year');
                $table->string('grade', 10)->default(''); // G, M, L, A
                $table->timestamps();

                $table->index(['tenant_id', 'class_id', 'subject_id', 'month', 'year'], 'lesson_eval_search_idx');
                $table->unique(['tenant_id', 'student_id', 'subject_id', 'book_id', 'date'], 'lesson_eval_uniq_idx');
            });
        }

        // 2. Lesson Evaluation Books
        if (!Schema::hasTable('lesson_evaluation_books')) {
            Schema::create('lesson_evaluation_books', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('name');
                $table->timestamps();
            });
        }

        // 3. ADMS Push Commands
        if (!Schema::hasTable('adms_commands')) {
            Schema::create('adms_commands', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->string('device_sn')->index();
                $table->text('command');
                $table->text('response')->nullable();
                $table->string('status', 30)->default('pending'); // pending, executed, failed
                $table->timestamp('executed_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. RFID Cards
        if (!Schema::hasTable('rfid_cards')) {
            Schema::create('rfid_cards', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->string('card_uid')->index();
                $table->string('user_id')->nullable();
                $table->string('holder_name');
                $table->string('role', 30)->default('student'); // student, teacher, staff
                $table->string('designation')->nullable();
                $table->string('class_name')->nullable();
                $table->date('issue_date')->nullable();
                $table->string('status', 30)->default('active'); // active, blocked, lost
                $table->timestamps();
            });
        }

        // 5. Alumni & Graduates
        if (!Schema::hasTable('alumni_graduates')) {
            Schema::create('alumni_graduates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->string('name');
                $table->string('sanad_no')->nullable();
                $table->string('batch', 30);
                $table->string('phone', 50)->nullable();
                $table->string('degree')->nullable();
                $table->string('workplace')->nullable();
                $table->string('designation')->nullable();
                $table->string('status', 30)->default('employed'); // employed, jobless, higher_study
                $table->string('preferred_job')->nullable();
                $table->string('preferred_location')->nullable();
                $table->string('institution')->nullable();
                $table->string('country')->nullable();
                $table->date('joining_date')->nullable();
                $table->timestamps();
            });
        }

        // 6. Boarding Bazaars
        if (!Schema::hasTable('boarding_bazaars')) {
            Schema::create('boarding_bazaars', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->string('voucher_no')->unique();
                $table->date('date');
                $table->string('buyer_name');
                $table->text('items_summary');
                $table->string('total_qty')->nullable();
                $table->decimal('amount', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        // 7. Boarding Meals
        if (!Schema::hasTable('boarding_meals')) {
            Schema::create('boarding_meals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('student_id');
                $table->date('date');
                $table->boolean('breakfast')->default(false);
                $table->boolean('lunch')->default(false);
                $table->boolean('dinner')->default(false);
                $table->timestamps();

                $table->unique(['tenant_id', 'student_id', 'date'], 'brd_meal_uniq_idx');
            });
        }

        // 8. Fixed Assets
        if (!Schema::hasTable('fixed_assets')) {
            Schema::create('fixed_assets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->string('tag')->unique();
                $table->string('name');
                $table->string('category');
                $table->date('purchase_date');
                $table->string('location')->nullable();
                $table->decimal('cost', 12, 2)->default(0);
                $table->decimal('dep_rate', 5, 2)->default(10.00);
                $table->decimal('book_value', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        // 9. Complaint & Feedback
        if (!Schema::hasTable('complaint_feedbacks')) {
            Schema::create('complaint_feedbacks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->string('tracking_id')->unique();
                $table->date('date');
                $table->string('sender_name');
                $table->string('sender_type');
                $table->string('category');
                $table->string('priority')->default('medium');
                $table->string('subject');
                $table->text('description')->nullable();
                $table->string('status', 30)->default('pending'); // pending, resolved
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }

        // 10. Institutional Duty Assignments
        if (!Schema::hasTable('duty_assignments')) {
            Schema::create('duty_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->string('title');
                $table->string('department');
                $table->string('person_name');
                $table->string('designation')->nullable();
                $table->string('phone')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // 11. Staff Discharges
        if (!Schema::hasTable('staff_discharges')) {
            Schema::create('staff_discharges', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->string('staff_id')->nullable();
                $table->string('name');
                $table->string('designation');
                $table->string('department')->nullable();
                $table->date('joining_date')->nullable();
                $table->date('discharge_date');
                $table->string('reason');
                $table->string('status')->default('cleared');
                $table->timestamps();
            });
        }

        // 12. Needy Student Assistance
        if (!Schema::hasTable('needy_student_assistances')) {
            Schema::create('needy_student_assistances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->string('student_name');
                $table->string('class_name');
                $table->string('support_type');
                $table->string('amount_discount')->default('100%');
                $table->string('fund_source');
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('needy_student_assistances');
        Schema::dropIfExists('staff_discharges');
        Schema::dropIfExists('duty_assignments');
        Schema::dropIfExists('complaint_feedbacks');
        Schema::dropIfExists('fixed_assets');
        Schema::dropIfExists('boarding_meals');
        Schema::dropIfExists('boarding_bazaars');
        Schema::dropIfExists('alumni_graduates');
        Schema::dropIfExists('rfid_cards');
        Schema::dropIfExists('adms_commands');
        Schema::dropIfExists('lesson_evaluation_books');
        Schema::dropIfExists('lesson_evaluations');
    }
};
