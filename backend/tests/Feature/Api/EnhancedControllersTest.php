<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Notice;
use App\Models\Fund;
use App\Models\AttendanceDevice;
use App\Models\Recruitment;
use Illuminate\Support\Facades\Hash;

class EnhancedControllersTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name_bn' => 'পরীক্ষা মাদ্রাসা',
            'name_en' => 'Test Madrasa',
            'slug' => 'test-madrasa',
            'type' => 'madrasa',
            'contact_email' => 'test@test.com',
            'subscription_tier' => 'pro',
            'subscription_status' => 'active',
            'modules_enabled' => ['all'],
            'settings' => ['theme' => 'emerald'],
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'অ্যাডমিন',
            'name_en' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'is_active' => true,
            'is_platform_admin' => false,
        ]);

        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    protected function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ];
    }

    public function test_notice_pin_and_unpin(): void
    {
        $notice = Notice::create([
            'tenant_id' => $this->tenant->id,
            'title_bn' => 'ছুটির নোটিশ',
            'content_bn' => 'আগামীকাল মাদ্রাসা বন্ধ থাকবে',
            'is_pinned' => false,
        ]);

        $pinRes = $this->patchJson("/api/v1/notices/{$notice->id}/pin", [], $this->headers());
        $pinRes->assertStatus(200);
        $this->assertTrue((bool) $pinRes->json('data.is_pinned'));

        $unpinRes = $this->patchJson("/api/v1/notices/{$notice->id}/unpin", [], $this->headers());
        $unpinRes->assertStatus(200);
        $this->assertFalse((bool) $unpinRes->json('data.is_pinned'));
    }

    public function test_settings_update(): void
    {
        $payload = [
            'name_bn' => 'আপডেট মাদ্রাসা',
            'invoice_design' => ['paper_size' => 'A4', 'primary_color' => '#145032'],
        ];

        $res = $this->postJson('/api/v1/settings/update', $payload, $this->headers());
        $res->assertStatus(200);
        $this->assertEquals('আপডেট মাদ্রাসা', $res->json('data.tenant.name_bn'));
        $this->assertEquals('A4', $res->json('data.settings.invoice_design.paper_size'));
    }

    public function test_finance_fund_update_and_delete(): void
    {
        $fund = Fund::create([
            'tenant_id' => $this->tenant->id,
            'name_bn' => 'যাকাত তহবিল',
            'balance' => 10000,
        ]);

        $updateRes = $this->putJson("/api/v1/finance/funds/{$fund->id}", [
            'name_bn' => 'সাধারণ যাকাত তহবিল',
            'target_amount' => 50000,
        ], $this->headers());
        $updateRes->assertStatus(200);
        $this->assertEquals('সাধারণ যাকাত তহবিল', $updateRes->json('data.name_bn'));

        $delRes = $this->deleteJson("/api/v1/finance/funds/{$fund->id}", [], $this->headers());
        $delRes->assertStatus(200);
    }

    public function test_digital_attendance_device_crud_and_status(): void
    {
        $device = AttendanceDevice::create([
            'tenant_id' => $this->tenant->id,
            'device_name' => 'Main Gate Fingerprint',
            'serial_number' => 'SN-998877',
            'device_type' => 'biometric',
            'status' => 'active',
        ]);

        $showRes = $this->getJson("/api/v1/digital-attendance/devices/{$device->id}", $this->headers());
        $showRes->assertStatus(200);
        $this->assertEquals('Main Gate Fingerprint', $showRes->json('data.name'));

        $updateRes = $this->putJson("/api/v1/digital-attendance/devices/{$device->id}", [
            'name' => 'Front Gate Biometric',
        ], $this->headers());
        $updateRes->assertStatus(200);
        $this->assertEquals('Front Gate Biometric', $updateRes->json('data.name'));

        $statusRes = $this->getJson('/api/v1/digital-attendance/sync-status', $this->headers());
        $statusRes->assertStatus(200);
        $this->assertArrayHasKey('total_devices', $statusRes->json('data'));

        $delRes = $this->deleteJson("/api/v1/digital-attendance/devices/{$device->id}", [], $this->headers());
        $delRes->assertStatus(200);
    }

    public function test_hr_recruitment_and_application(): void
    {
        $recRes = $this->postJson('/api/v1/hr/recruitments', [
            'job_title' => 'হিফজ শিক্ষক',
            'department' => 'হিফজ বিভাগ',
            'description' => 'অভিজ্ঞ শিক্ষক আবশ্যক',
        ], $this->headers());
        $recRes->assertStatus(201);
        $recId = $recRes->json('data.id');

        $appRes = $this->postJson('/api/v1/hr/applications', [
            'recruitment_id' => $recId,
            'applicant_name' => 'কারী আব্দুল্লাহ',
            'applicant_phone' => '01711223344',
        ], $this->headers());
        $appRes->assertStatus(201);
        $this->assertEquals('কারী আব্দুল্লাহ', $appRes->json('data.applicant_name'));
    }

    public function test_save_comments_endpoint(): void
    {
        $res = $this->postJson('/api/v1/exam-marks/save-comments', [
            'exam_id' => 1,
            'comments' => [
                ['id' => 1, 'name' => 'ছাত্র ১', 'conduct' => 'উত্তম', 'comment' => 'খুব ভালো']
            ]
        ], $this->headers());
        $res->assertStatus(200);
    }
}
