<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Student table (one per user who is a student)
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('admission_number')->unique()->nullable();
            $table->string('name_bn');
            $table->string('name_en')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('father_name')->nullable();
            $table->string('father_phone')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_phone')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('guardian_relation')->nullable();
            $table->text('address_bn')->nullable();
            $table->string('email')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('nationality')->nullable();
            $table->json('health_summary')->nullable();
            $table->unsignedBigInteger('sponsor_id')->nullable();
            //$table->foreign('sponsor_id')->references('id')->on('donors')->nullOnDelete(); // forward ref: donors created in migration 009
            $table->string('id_card_url')->nullable();
            $table->string('mobile_wallet_pass_id')->nullable();
            $table->string('status')->default('active');
            $table->date('admission_date')->nullable();
            $table->date('graduation_date')->nullable();
            $table->string('graduation_type')->nullable();
            $table->string('current_class')->nullable();
            $table->string('current_section')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('admission_date');
        });

        // Student Guardian
        Schema::create('student_guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('relationship');
            $table->boolean('is_primary')->default(false);
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->json('communication_preferences')->nullable();
            $table->boolean('has_app')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['student_id', 'is_primary']);
        });

        // Student Document
        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('document_type');
            $table->string('file_url');
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('file_type')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('document_type');
            $table->index('is_verified');
        });

        // Student Health Record
        Schema::create('student_health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('vaccination_status')->nullable();
            $table->json('allergies')->nullable();
            $table->json('chronic_conditions')->nullable();
            $table->string('blood_group')->nullable();
            $table->float('height')->nullable();
            $table->float('weight')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Student Medical History
        Schema::create('student_medical_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('type');
            $table->text('description')->nullable();
            $table->string('doctor_name')->nullable();
            $table->text('treatment')->nullable();
            $table->string('prescription_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_medical_histories');
        Schema::dropIfExists('student_health_records');
        Schema::dropIfExists('student_documents');
        Schema::dropIfExists('student_guardians');
        Schema::dropIfExists('students');
    }
};
