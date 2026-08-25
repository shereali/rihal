<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\Donor;
use App\Models\Loan;
use App\Models\NotificationDelivery;
use App\Models\Orphan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\FinancialAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class FinancialEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name_bn' => 'পরীক্ষা মাদ্রাসা',
            'name_en' => 'Test Madrasa',
            'slug' => 'financial-enhancements-test',
            'type' => 'madrasa',
            'subscription_tier' => 'free',
            'subscription_status' => 'active',
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'পরীক্ষা প্রশাসক',
            'email' => 'financial-test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->headers = [
            'Authorization' => 'Bearer '.$this->user->createToken('financial-test')->plainTextToken,
            'Accept' => 'application/json',
        ];
    }

    public function test_module_dashboard_returns_real_multi_module_sections(): void
    {
        $this->getJson('/api/v1/module-dashboard', $this->headers)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['students', 'finance', 'loans', 'orphans', 'generated_at']]);
    }

    public function test_financial_apis_require_admin_role(): void
    {
        $student = User::create([
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'শিক্ষার্থী',
            'email' => 'student-financial@example.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'is_active' => true,
        ]);
        $headers = ['Authorization' => 'Bearer '.$student->createToken('student')->plainTextToken];

        $loan = Loan::create([
            'tenant_id' => $this->tenant->id,
            'title_bn' => 'সুরক্ষিত ঋণ',
            'principal_amount' => 1000,
            'remaining_amount' => 1000,
            'total_due' => 1000,
        ]);
        $orphan = Orphan::create([
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'সুরক্ষিত শিশু',
            'gender' => 'male',
        ]);

        $this->post('/api/v1/uploads/photos', [
            'file' => UploadedFile::fake()->image('blocked.jpg'),
        ], $headers)->assertForbidden();
        $this->get('/api/v1/reports/loans.csv', $headers)->assertForbidden();
        $this->getJson('/api/v1/financial-audit', $headers)->assertForbidden();
        $this->postJson('/api/v1/finance/funds', [], $headers)->assertForbidden();
        $this->postJson('/api/v1/loans', [], $headers)->assertForbidden();
        $this->putJson("/api/v1/loans/{$loan->id}/amortization", [], $headers)->assertForbidden();
        $this->postJson("/api/v1/loans/{$loan->id}/payments", ['amount' => 10], $headers)->assertForbidden();
        $this->postJson("/api/v1/orphans/{$orphan->id}/sponsors", [], $headers)->assertForbidden();

        // Tenant-wide financial and personal data is admin-only.
        $this->getJson('/api/v1/module-dashboard', $headers)->assertForbidden();
        $this->getJson('/api/v1/finance/summary', $headers)->assertForbidden();
        $this->getJson('/api/v1/loans', $headers)->assertForbidden();
        $this->getJson("/api/v1/loans/{$loan->id}", $headers)->assertForbidden();
        $this->getJson("/api/v1/orphans/{$orphan->id}/sponsors", $headers)->assertForbidden();
    }

    public function test_module_dashboard_rejects_users_without_tenant(): void
    {
        $platformUser = User::create([
            'tenant_id' => null,
            'name_bn' => 'প্ল্যাটফর্ম ব্যবহারকারী',
            'email' => 'platform-financial@example.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);
        $headers = ['Authorization' => 'Bearer '.$platformUser->createToken('platform')->plainTextToken];

        $this->getJson('/api/v1/module-dashboard', $headers)->assertForbidden();
        $this->post('/api/v1/uploads/photos', [
            'file' => UploadedFile::fake()->image('no-tenant.jpg'),
        ], $headers)->assertForbidden();
        $this->get('/api/v1/reports/loans.csv', $headers)->assertForbidden();
        $this->getJson('/api/v1/finance/summary', $headers)->assertForbidden();
        $this->postJson('/api/v1/loans', [
            'title_bn' => 'অবৈধ', 'principal_amount' => 1000,
        ], $headers)->assertForbidden();
    }

    public function test_cross_tenant_borrowers_and_sponsors_are_rejected(): void
    {
        $otherTenant = Tenant::create([
            'name_bn' => 'অন্য প্রতিষ্ঠান', 'slug' => 'other-financial-tenant', 'type' => 'madrasa',
            'subscription_tier' => 'free', 'subscription_status' => 'active',
        ]);
        $otherUser = User::create([
            'tenant_id' => $otherTenant->id, 'name_bn' => 'অন্য ব্যবহারকারী',
            'email' => 'other-borrower@example.com', 'password' => Hash::make('password123'),
            'role' => 'user', 'is_active' => true,
        ]);
        $otherDonor = Donor::create(['tenant_id' => $otherTenant->id, 'name_bn' => 'অন্য দাতা']);

        $this->postJson('/api/v1/loans', [
            'title_bn' => 'অবৈধ ঋণ', 'principal_amount' => 1000, 'user_id' => $otherUser->id,
        ], $this->headers)->assertUnprocessable();
        $this->postJson('/api/v1/orphans', [
            'name_bn' => 'শিশু', 'gender' => 'male', 'sponsor_id' => $otherDonor->id,
        ], $this->headers)->assertUnprocessable();
    }

    public function test_expired_bearer_token_is_rejected(): void
    {
        $token = $this->user->createToken('expired');
        $token->accessToken->forceFill(['expires_at' => now()->subMinute()])->save();

        $this->getJson('/api/v1/module-dashboard', [
            'Authorization' => 'Bearer '.$token->plainTextToken,
        ])->assertUnauthorized();
    }

    public function test_photo_upload_stores_image_and_returns_public_url(): void
    {
        Storage::fake('public');

        $response = $this->post('/api/v1/uploads/photos', [
            'file' => UploadedFile::fake()->image('orphan.jpg', 600, 600),
        ], $this->headers);

        $response->assertCreated()->assertJsonPath('success', true);
        Storage::disk('public')->assertExists($response->json('data.path'));
    }

    public function test_loan_creation_generates_persisted_emi_schedule(): void
    {
        $response = $this->postJson('/api/v1/loans', [
            'title_bn' => 'উন্নয়ন ঋণ',
            'principal_amount' => 120000,
            'interest_rate' => 12,
            'interest_type' => 'reducing',
            'installment_count' => 12,
            'start_date' => '2026-01-01',
        ], $this->headers);

        $response->assertCreated();
        $loanId = $response->json('data.id');
        $this->assertDatabaseCount('loan_installments', 12);
        $this->putJson("/api/v1/loans/{$loanId}", [
            'principal_amount' => 999999,
        ], $this->headers)->assertUnprocessable();
        $this->getJson("/api/v1/loans/{$loanId}/amortization", $this->headers)
            ->assertOk()->assertJsonCount(12, 'data.installments');
    }

    public function test_orphan_can_have_multiple_active_sponsors(): void
    {
        $orphan = Orphan::create([
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'শিশু',
            'gender' => 'male',
        ]);
        $first = Donor::create(['tenant_id' => $this->tenant->id, 'name_bn' => 'দাতা এক']);
        $second = Donor::create(['tenant_id' => $this->tenant->id, 'name_bn' => 'দাতা দুই']);

        foreach ([[$first, 600], [$second, 400]] as [$donor, $amount]) {
            $this->postJson("/api/v1/orphans/{$orphan->id}/sponsors", [
                'donor_id' => $donor->id,
                'monthly_commitment' => $amount,
                'starts_at' => '2026-01-01',
            ], $this->headers)->assertCreated();
        }

        $this->getJson("/api/v1/orphans/{$orphan->id}/sponsors", $this->headers)
            ->assertOk()->assertJsonCount(2, 'data');
        $this->assertDatabaseCount('orphan_sponsorships', 2);
    }

    public function test_financial_payment_creates_immutable_audit_entry(): void
    {
        $loan = Loan::create([
            'tenant_id' => $this->tenant->id,
            'title_bn' => 'অডিট ঋণ',
            'principal_amount' => 10000,
            'remaining_amount' => 10000,
            'total_due' => 10000,
        ]);

        $this->postJson("/api/v1/loans/{$loan->id}/payments", [
            'amount' => 1.001,
        ], $this->headers)->assertUnprocessable();
        $this->postJson("/api/v1/loans/{$loan->id}/payments", [
            'amount' => 1000,
            'payment_date' => '2026-01-15',
            'payment_method' => 'cash',
        ], $this->headers)->assertOk();

        $loan->refresh();
        $this->assertSame('9000.00', $loan->total_due);
        $this->getJson("/api/v1/loans/{$loan->id}/amortization", $this->headers)
            ->assertOk()->assertJsonPath('data.total_payable', 10000);
        $this->putJson("/api/v1/loans/{$loan->id}/amortization", [
            'installment_count' => 6,
        ], $this->headers)->assertConflict();

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $this->tenant->id,
            'entity_type' => Loan::class,
            'entity_id' => $loan->id,
            'action' => 'loan.payment_recorded',
        ]);
    }

    public function test_financial_audit_entries_cannot_be_updated_or_deleted(): void
    {
        $log = AuditLog::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'entity_type' => Loan::class,
            'entity_id' => 123,
            'action' => 'loan.payment_recorded',
            'changes' => ['amount' => 500],
        ]);

        $this->expectException(\LogicException::class);
        $log->update(['action' => 'tampered']);
    }

    public function test_payment_rolls_back_when_audit_write_fails(): void
    {
        $loan = Loan::create([
            'tenant_id' => $this->tenant->id, 'title_bn' => 'লেনদেন ঋণ',
            'principal_amount' => 10000, 'remaining_amount' => 10000, 'total_due' => 10000,
        ]);
        $mock = \Mockery::mock(FinancialAuditService::class);
        $mock->shouldReceive('record')->once()->andThrow(new RuntimeException('audit unavailable'));
        $this->app->instance(FinancialAuditService::class, $mock);

        $this->postJson("/api/v1/loans/{$loan->id}/payments", ['amount' => 1000], $this->headers)
            ->assertServerError();

        $this->assertDatabaseMissing('loan_payments', ['loan_id' => $loan->id]);
        $this->assertSame('10000.00', $loan->fresh()->remaining_amount);
    }

    public function test_csv_exports_escape_spreadsheet_formulas(): void
    {
        Loan::create([
            'tenant_id' => $this->tenant->id, 'title_bn' => '=HYPERLINK("https://evil.test")',
            'principal_amount' => 5000, 'remaining_amount' => 5000, 'total_due' => 5000,
        ]);
        Loan::create([
            'tenant_id' => $this->tenant->id, 'title_bn' => " \t=1+1",
            'principal_amount' => 1000, 'remaining_amount' => 1000, 'total_due' => 1000,
        ]);
        $content = $this->get('/api/v1/reports/loans.csv', $this->headers)
            ->assertOk()->streamedContent();

        $this->assertStringContainsString("'=HYPERLINK", $content);
        $this->assertStringContainsString("' \t=1+1", $content);
        $this->assertStringNotContainsString(",=HYPERLINK", $content);
    }

    public function test_loan_and_orphan_exports_download_csv(): void
    {
        Loan::create([
            'tenant_id' => $this->tenant->id,
            'title_bn' => 'রপ্তানি ঋণ',
            'principal_amount' => 5000,
            'remaining_amount' => 5000,
            'total_due' => 5000,
        ]);
        Orphan::create(['tenant_id' => $this->tenant->id, 'name_bn' => 'রপ্তানি শিশু', 'gender' => 'female']);

        $this->get('/api/v1/reports/loans.csv', $this->headers)
            ->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->get('/api/v1/reports/orphans.csv', $this->headers)
            ->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_failed_overdue_email_is_retried_without_duplicate_delivery_or_in_app_notification(): void
    {
        $borrower = User::create([
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'বকেয়া ঋণগ্রহীতা',
            'email' => 'overdue-borrower@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_active' => true,
        ]);
        $loan = Loan::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $borrower->id,
            'title_bn' => 'বকেয়া ঋণ',
            'principal_amount' => 10000,
            'remaining_amount' => 10000,
            'total_due' => 10000,
            'status' => 'active',
            'due_date' => today()->subDay()->toDateString(),
        ]);

        Mail::shouldReceive('raw')->once()->andThrow(new RuntimeException('temporary mail failure'));
        $this->artisan('financial:send-overdue-notifications')->assertSuccessful();

        $delivery = NotificationDelivery::where('type', 'loan_overdue')->where('channel', 'email')->sole();
        $this->assertSame('failed', $delivery->status);
        $this->assertSame(1, $delivery->attempts);

        Mail::fake();
        $this->artisan('financial:send-overdue-notifications')->assertSuccessful();

        $delivery->refresh();
        $this->assertSame('sent', $delivery->status);
        $this->assertSame(2, $delivery->attempts);
        $this->assertDatabaseCount('notification_deliveries', 1);
        $this->assertSame(1, AppNotification::where('recipient_id', $borrower->id)
            ->where('type', 'loan_overdue')
            ->where('related_id', $loan->id)
            ->count());
    }

    public function test_payment_creates_in_app_notification_for_relevant_user(): void
    {
        $borrower = User::create([
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'ঋণগ্রহীতা',
            'email' => 'borrower@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_active' => true,
        ]);
        $loan = Loan::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $borrower->id,
            'title_bn' => 'নোটিফিকেশন ঋণ',
            'principal_amount' => 10000,
            'remaining_amount' => 10000,
            'total_due' => 10000,
        ]);

        $this->postJson("/api/v1/loans/{$loan->id}/payments", [
            'amount' => 500,
            'payment_date' => '2026-01-15',
        ], $this->headers)->assertOk();

        $this->assertDatabaseHas('notifications', [
            'tenant_id' => $this->tenant->id,
            'recipient_id' => $borrower->id,
            'type' => 'loan_payment',
            'related_id' => $loan->id,
        ]);
    }
}
