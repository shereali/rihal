<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orphans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name_bn');
            $table->string('name_en')->nullable();
            $table->string('guardian_name_bn')->nullable();
            $table->string('guardian_name_en')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->text('address_bn')->nullable();
            $table->text('address_en')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->default('other');
            $table->string('admission_year')->nullable();
            $table->string('class_id')->nullable();
            $table->string('section_id')->nullable();
            $table->string('photo_url')->nullable();
            $table->text('story')->nullable();
            $table->string('sponsorship_status')->default('pending');
            $table->unsignedBigInteger('sponsor_id')->nullable();
            $table->foreign('sponsor_id')->references('id')->on('donors')->nullOnDelete();
            $table->decimal('monthly_amount', 12, 2)->default(0);
            $table->decimal('total_sponsored', 12, 2)->default(0);
            $table->date('sponsorship_start_date')->nullable();
            $table->date('sponsorship_end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_orphaned')->default(true);
            $table->boolean('is_needy')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'is_active']);
            $table->index('sponsorship_status');
        });

        Schema::create('sponsorship_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('orphan_id');
            $table->foreign('orphan_id')->references('id')->on('orphans')->cascadeOnDelete();
            $table->unsignedBigInteger('sponsor_id')->nullable();
            $table->foreign('sponsor_id')->references('id')->on('donors')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('purpose_bn')->nullable();
            $table->string('purpose_en')->nullable();
            $table->date('payment_date');
            $table->string('payment_method')->nullable();
            $table->string('reference')->nullable();
            $table->string('receipt_number')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('collected_by_user_id')->nullable();
            $table->foreign('collected_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['orphan_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsorship_payments');
        Schema::dropIfExists('orphans');
    }
};
