<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Staff Table
        if (!Schema::hasTable('staff')) {
            Schema::create('staff', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('name_bn');
                $table->string('name_en')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('designation')->nullable();
                $table->string('department')->nullable();
                $table->string('qualification')->nullable();
                $table->decimal('salary', 12, 2)->nullable();
                $table->date('join_date')->nullable();
                $table->date('leave_date')->nullable();
                $table->string('mobile_wallet_pass_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'is_active']);
            });
        }

        // 2. Properties Table
        if (!Schema::hasTable('properties')) {
            Schema::create('properties', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->string('property_name_bn');
                $table->string('property_name_en')->nullable();
                $table->string('property_type')->nullable();
                $table->string('category')->nullable();
                $table->string('sub_category')->nullable();
                $table->text('location_address_bn')->nullable();
                $table->text('location_address_en')->nullable();
                $table->decimal('land_area_sqft', 12, 2)->nullable();
                $table->decimal('building_area_sqft', 12, 2)->nullable();
                $table->integer('floor_count')->nullable();
                $table->integer('room_count')->nullable();
                $table->string('deed_number')->nullable();
                $table->date('registration_date')->nullable();
                $table->decimal('estimated_value', 14, 2)->nullable();
                $table->date('purchase_date')->nullable();
                $table->decimal('purchase_value', 14, 2)->nullable();
                $table->decimal('market_value', 14, 2)->nullable();
                $table->string('usage_type')->nullable();
                $table->string('status')->default('active');
                $table->boolean('is_active')->default(true);
                $table->json('documents')->nullable();
                $table->json('images')->nullable();
                $table->json('location_details')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'is_active']);
            });
        }

        // 3. Property Documents Table
        if (!Schema::hasTable('property_documents')) {
            Schema::create('property_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('property_id')->index();
                $table->string('title_bn')->nullable();
                $table->string('document_type')->nullable();
                $table->string('file_path')->nullable();
                $table->string('file_url')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->boolean('is_verified')->default(false);
                $table->timestamp('verified_at')->nullable();
                $table->unsignedBigInteger('verified_by')->nullable()->index();
                $table->timestamps();
            });
        }

        // 4. Property Maintenance Table
        if (!Schema::hasTable('property_maintenances')) {
            Schema::create('property_maintenances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('property_id')->index();
                $table->string('title_bn')->nullable();
                $table->string('maintenance_type')->nullable();
                $table->text('description')->nullable();
                $table->decimal('estimated_cost', 12, 2)->nullable();
                $table->decimal('actual_cost', 12, 2)->nullable();
                $table->string('status')->default('pending');
                $table->boolean('is_active')->default(true);
                $table->date('maintenance_date')->nullable();
                $table->date('completion_date')->nullable();
                $table->unsignedBigInteger('assigned_by_user_id')->nullable()->index();
                $table->unsignedBigInteger('completed_by_user_id')->nullable()->index();
                $table->timestamps();
            });
        }

        // 5. Property Visitors Table
        if (!Schema::hasTable('property_visitors')) {
            Schema::create('property_visitors', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('property_id')->index();
                $table->string('visitor_name');
                $table->string('visitor_phone')->nullable();
                $table->string('purpose')->nullable();
                $table->date('visit_date')->nullable();
                $table->timestamp('visit_time')->nullable();
                $table->timestamp('departure_time')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 6. Leave Applications Table
        if (!Schema::hasTable('leave_applications')) {
            Schema::create('leave_applications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('leave_type');
                $table->string('title_bn');
                $table->string('title')->nullable();
                $table->text('description_bn')->nullable();
                $table->date('start_date');
                $table->date('end_date');
                $table->integer('days_count')->default(1);
                $table->string('status')->default('pending');
                $table->text('notes')->nullable();
                $table->boolean('is_urgent')->default(false);
                $table->unsignedBigInteger('approved_by_user_id')->nullable()->index();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status']);
                $table->index(['user_id', 'leave_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
        Schema::dropIfExists('property_visitors');
        Schema::dropIfExists('property_maintenances');
        Schema::dropIfExists('property_documents');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('staff');
    }
};
