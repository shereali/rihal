<?php

namespace Tests\Unit;

use App\Services\LoanAmortizationService;
use PHPUnit\Framework\TestCase;

class LoanAmortizationServiceTest extends TestCase
{
    public function test_reducing_balance_schedule_is_balanced_and_fully_amortized(): void
    {
        $schedule = (new LoanAmortizationService())->build(
            principal: 120000,
            annualRate: 12,
            installments: 12,
            startDate: '2026-01-01',
            frequency: 'monthly',
            type: 'reducing'
        );

        $this->assertCount(12, $schedule['installments']);
        $this->assertSame(0.0, $schedule['installments'][11]['closing_balance']);
        $this->assertEqualsWithDelta(
            120000 + $schedule['total_interest'],
            $schedule['total_payable'],
            0.02
        );
        $this->assertGreaterThan(0, $schedule['emi']);
    }

    public function test_zero_interest_schedule_evenly_splits_principal(): void
    {
        $schedule = (new LoanAmortizationService())->build(12000, 0, 12, '2026-01-01');

        $this->assertSame(1000.0, $schedule['emi']);
        $this->assertSame(0.0, $schedule['total_interest']);
        $this->assertSame(0.0, $schedule['installments'][11]['closing_balance']);
    }
}
