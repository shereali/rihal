<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->unsignedInteger('installment_count')->default(1)->after('repayment_frequency');
        });

        Schema::create('loan_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('installment_number');
            $table->date('due_date');
            $table->decimal('opening_balance', 12, 2);
            $table->decimal('principal_amount', 12, 2);
            $table->decimal('interest_amount', 12, 2)->default(0);
            $table->decimal('installment_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('closing_balance', 12, 2);
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique(['loan_id', 'installment_number']);
            $table->index(['tenant_id', 'status', 'due_date']);
        });

        Schema::create('orphan_sponsorships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('orphan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('donor_id')->constrained('donors')->cascadeOnDelete();
            $table->decimal('monthly_commitment', 12, 2)->default(0);
            $table->decimal('share_percent', 5, 2)->nullable();
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['orphan_id', 'donor_id', 'starts_at']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::table('sponsorship_payments', function (Blueprint $table) {
            $table->foreignId('orphan_sponsorship_id')->nullable()->after('sponsor_id')
                ->constrained('orphan_sponsorships')->nullOnDelete();
        });

        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel');
            $table->string('recipient');
            $table->string('type');
            $table->string('dedupe_key', 64)->nullable()->unique();
            $table->string('status')->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->text('message');
            $table->text('provider_response')->nullable();
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'channel', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
        Schema::table('sponsorship_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('orphan_sponsorship_id');
        });
        Schema::dropIfExists('orphan_sponsorships');
        Schema::dropIfExists('loan_installments');
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('installment_count');
        });
    }
};
