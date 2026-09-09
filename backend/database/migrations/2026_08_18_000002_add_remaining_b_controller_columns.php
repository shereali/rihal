<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── donations: controller validates payment_method ───────────────────────
        Schema::table('donations', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('donation_type');
        });

        // ─── stocks: controller validates quantity (table has current_quantity) ──
        Schema::table('stocks', function (Blueprint $table) {
            $table->integer('quantity')->nullable()->after('current_quantity');
        });

        // ─── cash_books: type is NOT NULL but store doesn't set it ────────────────
        Schema::table('cash_books', function (Blueprint $table) {
            $table->string('type')->nullable()->change();
        });

        // ─── donations: controller sets donation_date but `date` is NOT NULL ──────
        Schema::table('donations', function (Blueprint $table) {
            $table->date('date')->nullable()->change();
        });

        // ─── stocks: model/controller use is_active but migration lacks it ─────────
        Schema::table('stocks', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('status');
        });

        // ─── cash_books: amount NOT NULL but store sets debit/credit not amount ────
        Schema::table('cash_books', function (Blueprint $table) {
            $table->decimal('amount', 12, 2)->nullable()->change();
        });

        // ─── fee_structures: controller validates total_fee ────────────────────────
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->decimal('total_fee', 12, 2)->nullable()->after('name_en');
        });

        // ─── fee_payments: fee_type NOT NULL no default but store doesn't set it ────
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->string('fee_type')->default('general')->change();
        });

        // ─── exams: model/controller use is_active but migration lacks it ──────────
        Schema::table('exams', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('exam_type');
            $table->date('start_date')->nullable()->change();
            $table->date('end_date')->nullable()->change();
        });

        // ─── enrollments: controller validates admission_type / previous_* / remarks / documents ─
        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('admission_type')->nullable()->after('status');
            $table->string('previous_school')->nullable()->after('admission_type');
            $table->string('previous_board')->nullable()->after('previous_school');
            $table->integer('passing_year')->nullable()->after('previous_board');
            $table->text('remarks_bn')->nullable()->after('passing_year');
            $table->text('remarks_en')->nullable()->after('remarks_bn');
            $table->json('documents')->nullable()->after('remarks_en');
        });

        // ─── mark_entries: controller sets tenant_id but migration lacks it ────────
        Schema::table('mark_entries', function (Blueprint $table) {
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        });

        // ─── stock_transactions: transaction_type NOT NULL no default but store sets type ─
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->string('transaction_type')->nullable()->change();
        });

        // ─── journal_entries: transaction_date NOT NULL but store sets entry_date ─
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->date('transaction_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn(['payment_method']);
        });
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn(['quantity']);
        });
        Schema::table('cash_books', function (Blueprint $table) {
            $table->string('type')->nullable(false)->default('receipt')->change();
        });
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->date('transaction_date')->nullable(false)->change();
        });
    }
};
