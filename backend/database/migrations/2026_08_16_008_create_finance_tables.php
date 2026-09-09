<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fee Structure
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('class_id')->nullable();
            $table->string('session_id')->nullable();
            $table->string('name_bn');
            $table->string('name_en')->nullable();
            $table->json('fee_heads')->nullable();
            $table->decimal('admission_fee', 12, 2)->default(0);
            $table->decimal('monthly_fee', 12, 2)->default(0);
            $table->decimal('exam_fee', 12, 2)->default(0);
            $table->decimal('library_fee', 12, 2)->default(0);
            $table->decimal('sports_fee', 12, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->integer('grace_period_days')->default(7);
            $table->float('late_fee_rate')->default(0);
            $table->json('waiver_rules')->nullable();
            $table->boolean('is_online_payment_enabled')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
        });

        // Fee Payment
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedBigInteger('fee_structure_id')->nullable();
            $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->nullOnDelete();
            $table->string('fee_type');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('transaction_ref')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('receipt_url')->nullable();
            $table->string('status')->default('pending');
            $table->decimal('late_fee_charged', 12, 2)->default(0);
            $table->decimal('waiver_applied', 12, 2)->default(0);
            $table->unsignedBigInteger('paid_by_guardian_id')->nullable();
            $table->unsignedBigInteger('paid_by_teacher_id')->nullable();
            $table->integer('reminder_count')->default(0);
            $table->timestamp('last_reminder_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'due_date']);
            $table->index('payment_method');
            $table->index('student_id');
        });

        // Fund
        Schema::create('funds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name_bn');
            $table->string('name_en')->nullable();
            $table->string('fund_type')->default('general');
            $table->string('currency')->default('BDT');
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('total_income', 12, 2)->default(0);
            $table->decimal('total_expense', 12, 2)->default(0);
            $table->boolean('is_separate_accounting_required')->default(false);
            $table->decimal('zakat_eligible_balance', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('fund_type');
        });

        // Chart of Accounts
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('account_code')->unique();
            $table->string('account_name_bn');
            $table->string('account_name_en')->nullable();
            $table->string('account_type');
            $table->unsignedBigInteger('parent_account_id')->nullable();
            $table->foreign('parent_account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
            $table->boolean('is_zakat_account')->default(false);
            $table->unsignedBigInteger('fund_id')->nullable();
            $table->foreign('fund_id')->references('id')->on('funds')->nullOnDelete();
            $table->string('account_category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['account_type', 'account_category']);
        });

        // Journal Entry
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('fund_id')->nullable();
            $table->foreign('fund_id')->references('id')->on('funds')->nullOnDelete();
            $table->date('transaction_date');
            $table->string('description_bn');
            $table->string('description_en')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->foreign('approved_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->json('lines')->nullable();
            $table->boolean('is_balanced')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
        });

        // Journal Entry Line
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_entry_id');
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->cascadeOnDelete();
            $table->unsignedBigInteger('account_id');
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->cascadeOnDelete();
            $table->decimal('debit', 12, 2)->default(0);
            $table->decimal('credit', 12, 2)->default(0);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Cash Book
        Schema::create('cash_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('cash_box_name')->nullable();
            $table->date('transaction_date');
            $table->string('type');
            $table->decimal('amount', 12, 2);
            $table->string('description_bn');
            $table->string('description_en')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('related_journal_entry_id')->nullable();
            $table->foreign('related_journal_entry_id')->references('id')->on('journal_entries')->nullOnDelete();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('type');
        });

        // Vendor (created BEFORE expenses so the FK can resolve)
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name_bn');
            $table->string('name_en')->nullable();
            $table->string('type')->default('supplier');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address_bn')->nullable();
            $table->string('contact_person')->nullable();
            $table->json('payment_terms')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Donations (FK to donors created in migration 009 — comment out FK, add in seeder)
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('donor_id');
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->string('donation_type')->default('general');
            $table->unsignedBigInteger('fund_id')->nullable();
            $table->foreign('fund_id')->references('id')->on('funds')->nullOnDelete();
            $table->string('receipt_number')->nullable();
            $table->string('receipt_url')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_period')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('acknowledgment_sent_at')->nullable();
            $table->boolean('tax_receipt_generated')->default(false);
            $table->string('tax_receipt_number')->nullable();
            $table->text('notes')->nullable();
            $table->float('retention_score')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('donation_type');
        });

        // Expense
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('fund_id')->nullable();
            $table->foreign('fund_id')->references('id')->on('funds')->nullOnDelete();
            $table->date('transaction_date');
            $table->string('description_bn');
            $table->string('description_en')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('category')->nullable();
            $table->string('vendor_name')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->foreign('vendor_id')->references('id')->on('vendors')->nullOnDelete();
            $table->string('purchase_order_number')->nullable();
            $table->string('bill_number')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('transaction_ref')->nullable();
            $table->string('status')->default('pending');
            $table->string('approval_status')->default('pending');
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->foreign('approved_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('document_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('approval_status');
        });

        // Budget
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name_bn');
            $table->string('name_en')->nullable();
            $table->string('fiscal_year');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('draft');
            $table->json('categories')->nullable();
            $table->json('monthly_allocations')->nullable();
            $table->decimal('total_budget', 12, 2)->default(0);
            $table->decimal('total_actual', 12, 2)->default(0);
            $table->decimal('variance', 12, 2)->default(0);
            $table->json('variance_analysis')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->foreign('approved_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('donations');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('cash_books');
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('chart_of_accounts');
        Schema::dropIfExists('funds');
        Schema::dropIfExists('fee_payments');
        Schema::dropIfExists('fee_structures');
    }
};
