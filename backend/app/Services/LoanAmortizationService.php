<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

class LoanAmortizationService
{
    public function build(
        float $principal,
        float $annualRate,
        int $installments,
        string $startDate,
        string $frequency = 'monthly',
        string $type = 'reducing'
    ): array {
        if ($principal <= 0 || $installments < 1 || $annualRate < 0) {
            throw new InvalidArgumentException('Principal and installments must be positive and rate cannot be negative.');
        }

        $periodsPerYear = match ($frequency) {
            'weekly' => 52,
            'quarterly' => 4,
            'yearly' => 1,
            default => 12,
        };
        $periodicRate = ($annualRate / 100) / $periodsPerYear;
        $date = CarbonImmutable::parse($startDate);

        if ($type === 'flat') {
            $totalInterest = $principal * ($annualRate / 100) * ($installments / $periodsPerYear);
            $emi = ($principal + $totalInterest) / $installments;
        } elseif ($periodicRate == 0.0) {
            $totalInterest = 0.0;
            $emi = $principal / $installments;
        } else {
            $factor = pow(1 + $periodicRate, $installments);
            $emi = $principal * $periodicRate * $factor / ($factor - 1);
            $totalInterest = ($emi * $installments) - $principal;
        }

        $rows = [];
        $principalCents = (int) round($principal * 100);
        $balanceCents = $principalCents;
        $emiCents = (int) round($emi * 100);
        $targetFlatInterestCents = (int) round($totalInterest * 100);
        $runningInterestCents = 0;

        for ($number = 1; $number <= $installments; $number++) {
            if ($type === 'flat') {
                $interestCents = $number === $installments
                    ? $targetFlatInterestCents - $runningInterestCents
                    : (int) round($targetFlatInterestCents / $installments);
            } else {
                $interestCents = (int) round(($balanceCents / 100) * $periodicRate * 100);
            }

            $principalPartCents = $number === $installments
                ? $balanceCents
                : min($balanceCents, max(0, $emiCents - $interestCents));
            $installmentCents = $principalPartCents + $interestCents;
            $openingCents = $balanceCents;
            $balanceCents = max(0, $balanceCents - $principalPartCents);
            $runningInterestCents += $interestCents;
            $date = $this->nextDate($date, $frequency);

            $rows[] = [
                'installment_number' => $number,
                'due_date' => $date->toDateString(),
                'opening_balance' => (float) ($openingCents / 100),
                'principal_amount' => (float) ($principalPartCents / 100),
                'interest_amount' => (float) ($interestCents / 100),
                'installment_amount' => (float) ($installmentCents / 100),
                'closing_balance' => (float) ($balanceCents / 100),
            ];
        }

        return [
            'emi' => (float) ($emiCents / 100),
            'total_interest' => (float) ($runningInterestCents / 100),
            'total_payable' => (float) (($principalCents + $runningInterestCents) / 100),
            'installments' => $rows,
        ];
    }

    private function nextDate(CarbonImmutable $date, string $frequency): CarbonImmutable
    {
        return match ($frequency) {
            'weekly' => $date->addWeek(),
            'quarterly' => $date->addMonthsNoOverflow(3),
            'yearly' => $date->addYear(),
            default => $date->addMonthNoOverflow(),
        };
    }
}
