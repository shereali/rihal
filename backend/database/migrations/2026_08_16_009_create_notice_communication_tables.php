<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Donor
        Schema::create('donors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name_bn');
            $table->string('name_en')->nullable();
            $table->string('type')->default('individual');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address_bn')->nullable();
            $table->string('organization_name')->nullable();
            $table->string('organization_type')->nullable();
            $table->string('tax_id')->nullable();
            $table->decimal('total_donated', 12, 2)->default(0);
            $table->date('last_donation_date')->nullable();
            $table->string('donor_tier')->default('regular');
            $table->string('recognition_level')->default('none');
            $table->json('communication_log')->nullable();
            $table->string('preferred_language')->default('bn');
            $table->string('preferred_channel')->default('app');
            $table->boolean('is_recurring_donor')->default(false);
            $table->float('churn_risk_score')->nullable();
            $table->string('avatar_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('type');
            $table->index('donor_tier');
        });

        // Notice
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('title_bn');
            $table->string('title_en')->nullable();
            $table->string('title_ar')->nullable();
            $table->longText('content_bn')->nullable();
            $table->longText('content_en')->nullable();
            $table->longText('content_ar')->nullable();
            $table->string('type')->default('notice');
            $table->json('target_audience')->nullable();
            $table->json('channels')->nullable();
            $table->json('channel_configs')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_scheduled')->default(false);
            $table->integer('read_by_count')->default(0);
            $table->json('read_by_list')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('type');
            $table->index('is_pinned');
        });

        // Notice Read Receipt
        Schema::create('notice_read_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamp('read_at');
            $table->string('read_method')->nullable();
            $table->timestamps();
            $table->unique(['notice_id', 'user_id']);
        });

        // Comms Template
        Schema::create('comms_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name_bn');
            $table->string('name_en')->nullable();
            $table->string('type');
            $table->json('content_bn')->nullable();
            $table->json('content_en')->nullable();
            $table->json('content_ar')->nullable();
            $table->json('channels')->nullable();
            $table->json('merge_fields')->nullable();
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index('type');
        });

        // Emergency Alert
        Schema::create('emergency_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('title_bn');
            $table->string('title_en')->nullable();
            $table->longText('content_bn')->nullable();
            $table->longText('content_en')->nullable();
            $table->string('priority')->default('high');
            $table->json('channels')->nullable();
            $table->json('target_audience')->nullable();
            $table->json('acknowledgment_required')->nullable();
            $table->json('escalation_cascade')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->json('sent_status')->nullable();
            $table->json('acknowledgment_status')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['priority', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_alerts');
        Schema::dropIfExists('comms_templates');
        Schema::dropIfExists('notice_read_receipts');
        Schema::dropIfExists('notices');
        Schema::dropIfExists('donors');
    }
};
