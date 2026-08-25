<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApplicationConfigurationTest extends TestCase
{
    public function test_application_uses_bengali_locale_and_dhaka_timezone(): void
    {
        $this->assertSame('Asia/Dhaka', config('app.timezone'));
        $this->assertSame('bn', config('app.locale'));
        $this->assertSame('bn', config('app.fallback_locale'));
    }
}
