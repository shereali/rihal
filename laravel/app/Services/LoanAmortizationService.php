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
        $balance = $principal;
        $runningInterest = 0.0;

        for ($number = 1; $number <= $installments; $number++) {
            $interest = $type === 'flat'
                ? $totalInterest / $installments
                : $balance * $periodicRate;
            $principalPart = min($balance, $emi - $interest);
            if ($number === $installments) {
                $principalPart = $balance;
                $emiForRow = $principalPart + $interest;
            } else {
                $emiForRow = $emi;
            }
            $opening = $balance;
            $balance = max(0, $balance - $principalPart);
            $runningInterest += $interest;
            $date = $this->nextDate($date, $frequency);

            $rows[] = [
                'installment_number' => $number,
                'due_date' => $date->toDateString(),
                'opening_balance' => round($opening, 2),
                'principal_amount' => round($principalPart, 2),
                'interest_amount' => round($interest, 2),
                'installment_amount' => round($emiForRow, 2),
                'closing_balance' => $number === $installments ? 0.0 : round($balance, 2),
            ];
        }

        return [
            'emi' => round($emi, 2),
            'total_interest' => round($runningInterest, 2),
            'total_payable' => round($principal + $runningInterest, 2),
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
