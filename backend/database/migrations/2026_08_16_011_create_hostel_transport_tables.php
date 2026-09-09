<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hostel Room
        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('room_number');
            $table->integer('floor')->default(1);
            $table->string('building')->nullable();
            $table->string('room_type')->default('shared');
            $table->string('gender_type')->default('boys');
            $table->integer('capacity')->default(4);
            $table->integer('current_occupancy')->default(0);
            $table->json('students_assigned')->nullable();
            $table->unsignedBigInteger('hostel_warden_id')->nullable();
            $table->foreign('hostel_warden_id')->references('id')->on('users')->nullOnDelete();
            $table->string('mess_assigned')->nullable();
            $table->decimal('monthly_fee', 12, 2)->default(0);
            $table->json('amenities')->nullable();
            $table->string('maintenance_status')->default('good');
            $table->boolean('repair_needed')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['floor', 'building']);
            $table->unique(['tenant_id', 'room_number']);
        });

        // Hostel Visitor
        Schema::create('hostel_visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('hostel_room_id')->nullable();
            $table->foreign('hostel_room_id')->references('id')->on('hostel_rooms')->nullOnDelete();
            $table->string('visitor_name');
            $table->string('visitor_phone')->nullable();
            $table->string('visitor_relation')->nullable();
            $table->timestamp('visit_time');
            $table->timestamp('departure_time')->nullable();
            $table->text('purpose')->nullable();
            $table->string('status')->default('checked_in');
            $table->unsignedBigInteger('checked_by_user_id')->nullable();
            $table->foreign('checked_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->index('status');
        });

        // Transport Route (must be created before transport_buses due to FK)
        Schema::create('transport_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('route_name');
            $table->string('route_code')->nullable();
            $table->json('stops')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
        });

        // Transport Bus
        Schema::create('transport_buses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('bus_number');
            $table->string('license_plate')->nullable();
            $table->integer('capacity')->default(50);
            $table->string('vehicle_type')->default('minibus');
            $table->unsignedBigInteger('route_id')->nullable();
            $table->foreign('route_id')->references('id')->on('transport_routes')->nullOnDelete();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->foreign('driver_id')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('conductor_id')->nullable();
            $table->foreign('conductor_id')->references('id')->on('users')->nullOnDelete();
            $table->boolean('gps_tracking_enabled')->default(false);
            $table->json('last_known_location')->nullable();
            $table->date('maintenance_due_date')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->string('status')->default('active');
            $table->json('students_assigned')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->unique(['tenant_id', 'bus_number']);
        });

        // Transport Assignment
        Schema::create('transport_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedBigInteger('bus_id')->nullable();
            $table->foreign('bus_id')->references('id')->on('transport_buses')->nullOnDelete();
            $table->unsignedBigInteger('route_id')->nullable();
            $table->foreign('route_id')->references('id')->on('transport_routes')->nullOnDelete();
            $table->string('stop_name')->nullable();
            $table->time('pickup_time')->nullable();
            $table->time('dropoff_time')->nullable();
            $table->decimal('transport_fee', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_assignments');
        Schema::dropIfExists('transport_buses');
        Schema::dropIfExists('transport_routes');
        Schema::dropIfExists('hostel_visitors');
        Schema::dropIfExists('hostel_rooms');
    }
};
