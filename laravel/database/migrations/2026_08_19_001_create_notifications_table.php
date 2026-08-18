<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete(); // guardian user
            $table->string('type'); // absence | fee_due | general
            $table->string('title_bn');
            $table->text('body_bn');
            $table->string('related_type')->nullable(); // attendance_record | fee_payment | result
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('channel')->default('in_app'); // in_app | sms | email
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'recipient_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
