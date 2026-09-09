<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name_bn');
            $table->string('name_en')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('password');
            $table->string('role')->default('user');
            $table->string('title')->nullable();
            $table->string('department')->nullable();
            $table->json('profile')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_platform_admin')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('email');
            $table->index('role');
            $table->index('is_platform_admin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
