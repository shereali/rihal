<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── results: ExamResultController publish() + Result model ──────────────
        Schema::table('results', function (Blueprint $table) {
            $table->boolean('is_published')->default(false)->after('published_at');
            $table->integer('marks_obtained')->nullable()->after('gpa');
            $table->integer('total_marks')->nullable()->after('marks_obtained');
        });

        // ─── funds: FundController store validates type/target_amount/etc ────────
        Schema::table('funds', function (Blueprint $table) {
            $table->string('type')->nullable()->after('fund_type');
            $table->decimal('target_amount', 12, 2)->nullable()->after('currency');
            $table->decimal('collected_amount', 12, 2)->nullable()->after('target_amount');
            $table->text('description_bn')->nullable()->after('description');
            $table->boolean('is_active')->default(true)->after('zakat_eligible_balance');
        });

        // ─── journal_entries: controller queries entry_date, validates is_revenue ─
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->date('entry_date')->nullable()->after('transaction_date');
            $table->boolean('is_revenue')->nullable()->after('description_en');
        });

        // ─── cash_books: storeCashBook validates debit/credit/balance/reconciled ─
        Schema::table('cash_books', function (Blueprint $table) {
            $table->decimal('debit_amount', 12, 2)->nullable()->after('amount');
            $table->decimal('credit_amount', 12, 2)->nullable()->after('debit_amount');
            $table->decimal('balance', 12, 2)->nullable()->after('credit_amount');
            $table->boolean('is_reconciled')->default(false)->after('reference');
        });

        // ─── stocks: controller/model use category_id, unit_price, total_value ───
        Schema::table('stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('vendor_id');
            $table->decimal('unit_price', 12, 2)->nullable()->after('last_purchase_price');
            $table->decimal('total_value', 12, 2)->nullable()->after('unit_price');
        });

        // ─── stock_transactions: controller uses type + reason ──────────────────
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->string('type')->nullable()->after('transaction_type');
            $table->text('reason')->nullable()->after('notes');
        });

        // ─── donations: controller validates donation_date/is_anonymous/etc ──────
        Schema::table('donations', function (Blueprint $table) {
            $table->date('donation_date')->nullable()->after('date');
            $table->boolean('is_anonymous')->default(false)->after('is_recurring');
            $table->boolean('is_acknowledged')->default(false)->after('is_anonymous');
            $table->boolean('receipt_generated')->default(false)->after('is_acknowledged');
        });

        // ─── expenses: controller validates is_approved/is_paid ──────────────────
        Schema::table('expenses', function (Blueprint $table) {
            $table->boolean('is_approved')->nullable()->after('amount');
            $table->boolean('is_paid')->default(true)->after('is_approved');
        });

        // ─── fee_payments: summary + store use is_fully_paid ────────────────────
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->boolean('is_fully_paid')->default(false)->after('status');
        });

        // ─── teacher_assignments: controller validates session/topic/status/... ──
        Schema::table('teacher_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('session_id')->nullable()->after('subject_id');
            $table->string('topic_bn')->nullable()->after('session_id');
            $table->string('topic_en')->nullable()->after('topic_bn');
            $table->string('status')->default('active')->after('assignment_type');
            $table->boolean('is_active')->default(true)->after('status');
            $table->timestamp('assigned_at')->nullable()->after('is_active');
        });

        // ─── enrollments: model casts is_active but migration lacks it ───────────
        Schema::table('enrollments', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('status');
        });

        // ─── mark_entries: controller uses max_marks/is_graded/remarks/... ───────
        Schema::table('mark_entries', function (Blueprint $table) {
            $table->decimal('max_marks', 8, 2)->nullable()->after('marks_obtained');
            $table->boolean('is_graded')->default(false)->after('validation_status');
            $table->boolean('is_published_in_result')->default(false)->after('is_graded');
            $table->boolean('is_active')->default(true)->after('is_published_in_result');
            $table->text('remarks_bn')->nullable()->after('comment');
            $table->text('remarks_en')->nullable()->after('remarks_bn');
        });
    }

    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->dropColumn(['is_published', 'marks_obtained', 'total_marks']);
        });
        Schema::table('funds', function (Blueprint $table) {
            $table->dropColumn(['type', 'target_amount', 'collected_amount', 'description_bn', 'is_active']);
        });
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropColumn(['entry_date', 'is_revenue']);
        });
        Schema::table('cash_books', function (Blueprint $table) {
            $table->dropColumn(['debit_amount', 'credit_amount', 'balance', 'is_reconciled']);
        });
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn(['category_id', 'unit_price', 'total_value']);
        });
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropColumn(['type', 'reason']);
        });
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn(['donation_date', 'is_anonymous', 'is_acknowledged', 'receipt_generated']);
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['is_approved', 'is_paid']);
        });
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropColumn(['is_fully_paid']);
        });
        Schema::table('teacher_assignments', function (Blueprint $table) {
            $table->dropColumn(['session_id', 'topic_bn', 'topic_en', 'status', 'is_active', 'assigned_at']);
        });
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['is_active']);
        });
        Schema::table('mark_entries', function (Blueprint $table) {
            $table->dropColumn(['max_marks', 'is_graded', 'is_published_in_result', 'is_active', 'remarks_bn', 'remarks_en']);
        });
    }
};
