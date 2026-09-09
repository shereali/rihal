<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stock
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('item_name_bn');
            $table->string('item_name_en')->nullable();
            $table->string('category')->nullable();
            $table->string('unit')->nullable();
            $table->integer('reorder_level')->default(0);
            $table->integer('max_stock')->default(0);
            $table->integer('current_quantity')->default(0);
            $table->date('last_stock_in')->nullable();
            $table->date('last_stock_out')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('stock_value', 12, 2)->default(0);
            $table->string('valuation_method')->default('weighted_avg');
            $table->date('last_purchase_date')->nullable();
            $table->decimal('last_purchase_price', 12, 2)->nullable();
            $table->string('status')->default('in_stock');
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
        });

        // Stock Transaction
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('stock_id')->nullable();
            $table->foreign('stock_id')->references('id')->on('stocks')->cascadeOnDelete();
            $table->string('transaction_type');
            $table->integer('quantity');
            $table->date('transaction_date');
            $table->string('reference_number')->nullable();
            $table->string('notes')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->index('transaction_type');
        });

        // Recruitment
        Schema::create('recruitments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('job_title');
            $table->string('department')->nullable();
            $table->longText('description')->nullable();
            $table->longText('requirements')->nullable();
            $table->string('status')->default('open');
            $table->date('posted_date');
            $table->date('closing_date')->nullable();
            $table->integer('applicant_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
        });

        // Job Application
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recruitment_id');
            $table->foreign('recruitment_id')->references('id')->on('recruitments')->cascadeOnDelete();
            $table->unsignedBigInteger('applicant_user_id')->nullable();
            $table->string('applicant_name')->nullable();
            $table->string('applicant_phone')->nullable();
            $table->string('applicant_email')->nullable();
            $table->text('applicant_address')->nullable();
            $table->json('qualifications')->nullable();
            $table->json('experience')->nullable();
            $table->string('resume_url')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('applied_at');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('recruitments');
        Schema::dropIfExists('stock_transactions');
        Schema::dropIfExists('stocks');
    }
};
