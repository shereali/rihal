<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('employee_id')->unique();
            $table->string('designation');
            $table->string('department');
            $table->json('qualifications')->nullable();
            $table->json('certifications')->nullable();
            $table->json('experience')->nullable();
            $table->json('subjects')->nullable();
            $table->json('classes')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('nid_number')->nullable();
            $table->date('join_date')->nullable();
            $table->date('leave_date')->nullable();
            $table->string('status')->default('active');
            $table->string('biometric_id')->nullable();
            $table->string('rfid_card_id')->nullable();
            $table->string('contract_id')->nullable();
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('user_id');
            $table->index('employee_id');
            $table->index('status');
        });

        Schema::create('teacher_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->string('certificate');
            $table->string('institution');
            $table->string('board')->nullable();
            $table->integer('year')->nullable();
            $table->string('grade')->nullable();
            $table->string('document_url')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('teacher_id');
        });

        Schema::create('teacher_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->string('contract_type');
            $table->text('terms')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('active');
            $table->decimal('salary', 12, 2)->nullable();
            $table->json('salary_structure')->nullable();
            $table->string('document_url')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('teacher_id');
            $table->index('status');
        });

        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('phone');
            $table->string('relationship')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_contacts');
        Schema::dropIfExists('teacher_contracts');
        Schema::dropIfExists('teacher_qualifications');
        Schema::dropIfExists('teachers');
    }
};
