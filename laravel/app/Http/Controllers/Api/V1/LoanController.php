<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Services\FinancialAuditService;
use App\Services\FinancialNotificationService;
use App\Services\LoanAmortizationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class LoanController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Loan::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->where('title_bn', 'like', '%' . $request->input('search') . '%'))
            ->when($request->has('type'), fn($q) => $q->where('loan_type', $request->input('type')))
            ->when($request->has('status'), fn($q) => $q->where('status', $request->input('status')))
            ->when($request->input('is_overdue') === 'true', fn($q) => $q->where('due_date', '<', now()))
            ->with('user:id,name_bn,name_en')
            ->orderBy('created_at', 'desc');

        $loans = $query->paginate($perPage);

        return $this->successResponse($loans);
    }

    public function store(Request $request, LoanAmortizationService $calculator): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'loan_type' => 'nullable|string|max:100',
            'title_bn' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'principal_amount' => 'required|numeric|min:1',
            'interest_rate' => 'nullable|numeric|min:0',
            'interest_type' => 'nullable|in:flat,reducing',
            'installment_count' => 'nullable|integer|min:1|max:600',
            'repayment_frequency' => 'nullable|in:weekly,monthly,quarterly,yearly',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        if (!empty($data['user_id']) && !\App\Models\User::where('tenant_id', $data['tenant_id'])->whereKey($data['user_id'])->exists()) {
            return $this->errorResponse('ঋণগ্রহীতা এই প্রতিষ্ঠানের নয়', 422);
        }
        $data['interest_rate'] = $data['interest_rate'] ?? 0;
        $data['interest_type'] = $data['interest_type'] ?? 'reducing';
        $data['installment_count'] = $data['installment_count'] ?? 1;
        $data['repayment_frequency'] = $data['repayment_frequency'] ?? 'monthly';
        $data['start_date'] = $data['start_date'] ?? today()->toDateString();

        $schedule = $calculator->build(
            (float) $data['principal_amount'],
            (float) $data['interest_rate'],
            (int) $data['installment_count'],
            $data['start_date'],
            $data['repayment_frequency'],
            $data['interest_type'],
        );
        $data['monthly_installment'] = $schedule['emi'];
        $data['total_interest'] = $schedule['total_interest'];
        $data['total_due'] = $schedule['total_payable'];
        $data['remaining_amount'] = $schedule['total_payable'];

        $loan = DB::transaction(function () use ($data, $schedule) {
            $loan = Loan::create($data);
            foreach ($schedule['installments'] as $row) {
                $loan->installments()->create(['tenant_id' => $loan->tenant_id, ...$row]);
            }
            return $loan;
        });

        return $this->successResponse($loan->load('installments'), 'ঋণ তৈরি সফল', 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $loan = Loan::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $id)
            ->with(['user:id,name_bn,name_en,email,phone', 'payments', 'installments'])
            ->firstOrFail();

        return $this->successResponse($loan);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $loan = Loan::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $id)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'loan_type' => 'nullable|string|max:100',
            'title_bn' => 'nullable|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'principal_amount' => 'nullable|numeric|min:1',
            'interest_rate' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:active,paid,overdue,closed',
            'approval_status' => 'nullable|in:pending,approved,rejected',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $loan->update($validator->validated());

        return $this->successResponse($loan, 'ঋণ আপডেট সফল');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $loan = Loan::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $id)
            ->firstOrFail();

        $loan->delete();

        return $this->successResponse(null, 'ঋণ মুছে ফেলা সফল');
    }

    public function recordPayment(
        Request $request,
        int $loanId,
        FinancialAuditService $audit,
        FinancialNotificationService $notifications
    ): JsonResponse {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:100',
            'reference' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }
        $data = $validator->validated();
        $tenantId = $request->user()->tenant_id;
        $paymentCents = (int) round((float) $data['amount'] * 100);

        $loan = DB::transaction(function () use ($loanId, $tenantId, $request, $data, $paymentCents, $audit) {
            $loan = Loan::where('tenant_id', $tenantId)->whereKey($loanId)->lockForUpdate()->firstOrFail();
            $remainingCents = (int) round((float) $loan->remaining_amount * 100);
            if ($paymentCents > $remainingCents) {
                throw ValidationException::withMessages(['amount' => 'প্রদানের পরিমাণ অবশিষ্ট ঋণের চেয়ে বেশি']);
            }

            $before = $loan->only(['total_paid', 'remaining_amount', 'total_due', 'status']);
            $payment = LoanPayment::create([
                ...$data,
                'tenant_id' => $tenantId,
                'loan_id' => $loan->id,
                'amount' => $paymentCents / 100,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'collected_by_user_id' => $request->user()->id,
            ]);

            $unallocatedCents = $paymentCents;
            $installments = $loan->installments()->whereIn('status', ['pending', 'partial', 'overdue'])
                ->orderBy('installment_number')->lockForUpdate()->get();
            foreach ($installments as $installment) {
                if ($unallocatedCents <= 0) break;
                $installmentCents = (int) round((float) $installment->installment_amount * 100);
                $alreadyPaidCents = (int) round((float) $installment->paid_amount * 100);
                $allocatedCents = min($installmentCents - $alreadyPaidCents, $unallocatedCents);
                $newPaidCents = $alreadyPaidCents + $allocatedCents;
                $fullyPaid = $newPaidCents >= $installmentCents;
                $installment->update([
                    'paid_amount' => $newPaidCents / 100,
                    'status' => $fullyPaid ? 'paid' : 'partial',
                    'paid_at' => $fullyPaid ? now() : null,
                ]);
                $unallocatedCents -= $allocatedCents;
            }

            $newPaidCents = (int) round((float) $loan->total_paid * 100) + $paymentCents;
            $newRemainingCents = max(0, $remainingCents - $paymentCents);
            $loan->total_paid = $newPaidCents / 100;
            $loan->remaining_amount = $newRemainingCents / 100;
            $loan->total_due = $newRemainingCents / 100;
            if ($newRemainingCents === 0) $loan->status = 'paid';
            $loan->save();

            $audit->record('loan.payment_recorded', $loan, [
                'before' => $before,
                'after' => $loan->only(['total_paid', 'remaining_amount', 'total_due', 'status']),
                'payment_id' => $payment->id,
                'amount' => $paymentCents / 100,
            ], $request, 'ঋণের কিস্তি রেকর্ড করা হয়েছে');

            return $loan;
        });

        $loan->load('user');
        try {
            $notifications->loanPaymentRecorded($loan, $paymentCents / 100);
        } catch (Throwable $exception) {
            report($exception);
        }

        return $this->successResponse($loan->fresh(['payments', 'installments']), 'প্রদান রেকর্ড সফল');
    }

    public function payments(Request $request, int $loanId): JsonResponse
    {
        $loan = Loan::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $loanId)
            ->firstOrFail();

        $payments = LoanPayment::where('loan_id', $loanId)
            ->where('tenant_id', $request->user()->tenant_id)
            ->orderBy('payment_date', 'desc')
            ->get();

        return $this->successResponse($payments);
    }

    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $totalLoans = Loan::where('tenant_id', $user->tenant_id)->count();
        $totalOutstanding = Loan::where('tenant_id', $user->tenant_id)
            ->where('status', 'active')
            ->sum('remaining_amount');
        $totalOverdue = Loan::where('tenant_id', $user->tenant_id)
            ->where('due_date', '<', now())
            ->where('status', 'active')
            ->sum('remaining_amount');
        $totalPaid = Loan::where('tenant_id', $user->tenant_id)->sum('total_paid');

        return $this->successResponse([
            'total_loans' => $totalLoans,
            'total_outstanding' => $totalOutstanding,
            'total_overdue' => $totalOverdue,
            'total_paid' => $totalPaid,
        ]);
    }
}
