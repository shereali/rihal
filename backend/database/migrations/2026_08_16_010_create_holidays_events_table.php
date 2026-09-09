<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Holiday
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->foreign('session_id')->references('id')->on('academic_sessions')->nullOnDelete();
            $table->string('name_bn');
            $table->string('name_en')->nullable();
            $table->date('date');
            $table->string('type')->default('religious');
            $table->boolean('is_full_day')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('type');
        });

        // Event
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('title_bn');
            $table->string('title_en')->nullable();
            $table->longText('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('type')->default('general');
            $table->string('location')->nullable();
            $table->json('attendees')->nullable();
            $table->integer('max_attendees')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->json('recurrence_pattern')->nullable();
            $table->boolean('registration_enabled')->default(false);
            $table->integer('registered_count')->default(0);
            $table->string('status')->default('scheduled');
            $table->json('reminders')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('type');
            $table->index('status');
        });

        // Event Registration
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamp('registered_at');
            $table->string('status')->default('registered');
            $table->boolean('attended')->default(false);
            $table->timestamp('attended_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('events');
        Schema::dropIfExists('holidays');
    }
};
