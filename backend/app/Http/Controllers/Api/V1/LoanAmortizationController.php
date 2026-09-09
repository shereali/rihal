<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Loan;
use App\Services\LoanAmortizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanAmortizationController extends ApiController
{
    public function show(Request $request, int $loanId): JsonResponse
    {
        $loan = Loan::where('tenant_id', $request->user()->tenant_id)->findOrFail($loanId);
        return $this->successResponse([
            'loan_id' => $loan->id,
            'emi' => (float) $loan->monthly_installment,
            'total_interest' => (float) $loan->total_interest,
            'total_payable' => (float) $loan->total_due + (float) $loan->total_paid,
            'installments' => $loan->installments()->get(),
        ]);
    }

    public function regenerate(Request $request, int $loanId, LoanAmortizationService $calculator): JsonResponse
    {
        $validated = $request->validate([
            'interest_rate' => ['nullable', 'numeric', 'min:0'],
            'interest_type' => ['nullable', 'in:flat,reducing'],
            'installment_count' => ['nullable', 'integer', 'min:1', 'max:600'],
            'repayment_frequency' => ['nullable', 'in:weekly,monthly,quarterly,yearly'],
            'start_date' => ['nullable', 'date'],
        ]);

        $regenerated = DB::transaction(function () use ($request, $loanId, $validated, $calculator) {
            $loan = Loan::where('tenant_id', $request->user()->tenant_id)
                ->whereKey($loanId)
                ->lockForUpdate()
                ->firstOrFail();
            if ($loan->payments()->exists()) return false;

            $loan->fill($validated);
            $schedule = $calculator->build(
                (float) $loan->principal_amount,
                (float) $loan->interest_rate,
                (int) $loan->installment_count,
                optional($loan->start_date)->toDateString() ?? today()->toDateString(),
                $loan->repayment_frequency,
                $loan->interest_type,
            );
            $loan->installments()->delete();
            foreach ($schedule['installments'] as $row) {
                $loan->installments()->create(['tenant_id' => $loan->tenant_id, ...$row]);
            }
            $loan->update([
                'monthly_installment' => $schedule['emi'],
                'total_interest' => $schedule['total_interest'],
                'total_due' => $schedule['total_payable'],
                'remaining_amount' => $schedule['total_payable'],
            ]);
            return true;
        });

        if (!$regenerated) {
            return $this->errorResponse('প্রদান শুরু হওয়ার পর কিস্তি সূচি পরিবর্তন করা যাবে না', 409);
        }
        return $this->show($request, $loanId);
    }
}
