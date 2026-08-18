<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // System Settings
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('setting_key')->unique();
            $table->json('setting_value')->nullable();
            $table->string('setting_type')->default('custom');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('updated_by_user_id')->nullable();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('updated_at')->useCurrent();
        });

        // Reminder Task
        Schema::create('reminder_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('task_type');
            $table->json('target')->nullable();
            $table->string('title_bn')->nullable();
            $table->string('title_en')->nullable();
            $table->longText('description')->nullable();
            $table->string('schedule_type')->default('once');
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('pending');
            $table->string('channel')->nullable();
            $table->json('delivery_status')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->index('status');
            $table->index('schedule_type');
        });

        // Plugin
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('vendor')->nullable();
            $table->string('version');
            $table->string('type')->default('built_in');
            $table->text('description')->nullable();
            $table->string('icon_url')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('license_type')->default('free');
            $table->json('hooks')->nullable();
            $table->json('config_schema')->nullable();
            $table->json('installed_config')->nullable();
            $table->boolean('enabled')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('installed_at')->nullable();
            $table->unsignedBigInteger('installed_by_user_id')->nullable();
            $table->foreign('installed_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->json('dependencies')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('type');
            $table->index('enabled');
        });

        // Activity Log
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('action');
            $table->json('before_value')->nullable();
            $table->json('after_value')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['entity_type', 'entity_id']);
            $table->index('action');
            $table->index('created_at');
        });

        // Audit Log
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('changes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['entity_type', 'entity_id']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('plugins');
        Schema::dropIfExists('reminder_tasks');
        Schema::dropIfExists('system_settings');
    }
};
