<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('loan_type')->default('general');
            $table->string('title_bn');
            $table->string('title_en')->nullable();
            $table->decimal('principal_amount', 12, 2);
            $table->decimal('remaining_amount', 12, 2);
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->string('interest_type')->default('flat');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('repayment_frequency')->default('monthly');
            $table->decimal('monthly_installment', 12, 2)->default(0);
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->decimal('total_interest', 12, 2)->default(0);
            $table->decimal('total_due', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->string('approval_status')->default('pending');
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->foreign('approved_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
            $table->index(['user_id', 'loan_type']);
        });

        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('loan_id');
            $table->foreign('loan_id')->references('id')->on('loans')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('payment_method')->nullable();
            $table->string('reference')->nullable();
            $table->string('receipt_number')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('collected_by_user_id')->nullable();
            $table->foreign('collected_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['loan_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
        Schema::dropIfExists('loans');
    }
};
