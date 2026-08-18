<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
        {
            // Tenants
            Schema::create('tenants', function (Blueprint $table) {
                $table->id();
                $table->string('name_bn');
                $table->string('name_en')->nullable();
                $table->string('slug')->unique();
                $table->string('type')->default('madrasa');
                $table->string('registration_number')->nullable();
                $table->integer('established_year')->nullable();
                $table->text('address_bn')->nullable();
                $table->string('city')->nullable();
                $table->string('district')->nullable();
                $table->string('postcode')->nullable();
                $table->string('country')->default('Bangladesh');
                $table->string('contact_email')->nullable();
                $table->string('contact_phone')->nullable();
                $table->string('principal_name')->nullable();
                $table->string('principal_email')->nullable();
                $table->string('logo_url')->nullable();
                $table->string('primary_color')->default('#1a5c2a');
                $table->string('secondary_color')->default('#d4af37');
                $table->string('favicon_url')->nullable();
                $table->string('custom_domain')->nullable();
                $table->boolean('is_white_label')->default(false);
                $table->json('settings')->nullable();
                $table->json('modules_enabled')->nullable();
                $table->string('subscription_tier')->default('free');
                $table->string('subscription_status')->default('active');
                $table->date('trial_ends_at')->nullable();
                $table->timestamp('activated_at')->nullable();
                $table->timestamp('deactivated_at')->nullable();
                $table->unsignedBigInteger('parent_organization_id')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index('slug');
                $table->index('type');
                $table->index('subscription_tier');
                $table->index('subscription_status');
            });

            // Tenant Branches
            Schema::create('tenant_branches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('name_bn');
                $table->string('name_en')->nullable();
                $table->string('code')->nullable();
                $table->text('address_bn')->nullable();
                $table->string('city')->nullable();
                $table->string('district')->nullable();
                $table->string('contact_phone')->nullable();
                $table->string('contact_email')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->boolean('is_active')->default(true);
                $table->tinyInteger('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
                $table->index(['tenant_id', 'is_primary']);
            });
        }

    public function down(): void
    {
        Schema::dropIfExists('tenant_branches');
        Schema::dropIfExists('tenants');
    }
};
