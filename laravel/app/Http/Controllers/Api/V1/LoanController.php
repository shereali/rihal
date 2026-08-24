<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'loan_type' => 'nullable|string|max:100',
            'title_bn' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'principal_amount' => 'required|numeric|min:1',
            'interest_rate' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['remaining_amount'] = $data['principal_amount'];
        $data['total_due'] = $data['principal_amount'];

        $loan = Loan::create($data);

        return $this->successResponse($loan, 'ঋণ তৈরি সফল', 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $loan = Loan::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $id)
            ->with(['user:id,name_bn,name_en', 'payments'])
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

    public function recordPayment(Request $request, int $loanId): JsonResponse
    {
        $loan = Loan::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $loanId)
            ->firstOrFail();

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

        DB::transaction(function () use ($loan, $request, $validator) {
            $data = $validator->validated();
            $data['tenant_id'] = $request->user()->tenant_id;
            $data['loan_id'] = $loan->id;
            $data['payment_date'] = $data['payment_date'] ?? now()->toDateString();
            $data['collected_by_user_id'] = $request->user()->id;

            LoanPayment::create($data);

            $newTotalPaid = ($loan->total_paid ?? 0) + $data['amount'];
            $loan->total_paid = $newTotalPaid;
            $loan->remaining_amount = max(0, ($loan->remaining_amount ?? $loan->principal_amount) - $data['amount']);
            $loan->total_due = max(0, $loan->total_due - $data['amount']);

            if ($loan->remaining_amount <= 0) {
                $loan->status = 'paid';
                $loan->remaining_amount = 0;
                $loan->total_due = 0;
            }

            $loan->save();
        });

        return $this->successResponse($loan->fresh('payments'), 'প্রদান রেকর্ড সফল');
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
