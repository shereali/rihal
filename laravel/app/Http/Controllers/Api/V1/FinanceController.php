<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Donation;
use App\Models\Expense;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\JournalEntry;
use App\Models\CashBook;
use App\Models\Fund;
use App\Models\Vendor;
use App\Models\Stock;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FinanceController extends ApiController
{
    // ─── Funds ────────────────────────────────────────────────────────────────

    public function funds(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Fund::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->where('name_bn', 'like', "%{$request->input('search')}%"))
            ->orderBy('name_bn');

        $funds = $query->paginate($perPage);

        return $this->successResponse($funds);
    }

    public function storeFund(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name_bn' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'type' => 'nullable|in:রাশনির্দিষ্ট,অনানুদানিক,উন্নয়ন,শেয়ার,অন্যান্য',
            'target_amount' => 'nullable|numeric',
            'collected_amount' => 'nullable|numeric',
            'description_bn' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;

        $fund = Fund::create($data);

        return $this->successResponse($fund, 'ফান্ড তৈরি সফল', 201);
    }

    // ─── Donors ───────────────────────────────────────────────────────────────

    public function donors(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = \App\Models\Donor::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->where('name_bn', 'like', "%{$request->input('search')}%"))
            ->orderBy('name_bn');

        $donors = $query->paginate($perPage);

        return $this->successResponse($donors);
    }

    public function storeDonor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name_bn' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'address_bn' => 'nullable|string',
            'blood_group' => 'nullable|string|max:5',
            'designation' => 'nullable|string|max:255',
            'organization' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;

        $donor = \App\Models\Donor::create($data);

        return $this->successResponse($donor, 'উদারকারী তৈরি সফল', 201);
    }

    // ─── Donations ────────────────────────────────────────────────────────────

    public function donations(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Donation::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->where('transaction_reference', 'like', "%{$request->input('search')}%"))
            ->when($request->has('donor_id'), fn($q) => $q->where('donor_id', $request->input('donor_id')))
            ->when($request->has('fund_id'), fn($q) => $q->where('fund_id', $request->input('fund_id')))
            ->when($request->has('payment_method'), fn($q) => $q->where('payment_method', $request->input('payment_method')))
            ->when($request->has('from_date'), fn($q) => $q->where('donation_date', '>=', $request->input('from_date')))
            ->when($request->has('to_date'), fn($q) => $q->where('donation_date', '<=', $request->input('to_date')))
            ->with('donor:id,name_bn,name_en')
            ->with('fund:id,name_bn')
            ->orderBy('donation_date', 'desc');

        $donations = $query->paginate($perPage);

        return $this->successResponse($donations);
    }

    public function storeDonation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'donor_id' => 'required|integer|exists:donors,id',
            'fund_id' => 'required|integer|exists:funds,id',
            'amount' => 'required|numeric|min:0',
            'donation_date' => 'nullable|date',
            'payment_method' => 'nullable|in:নগদ,ব্যাংক,মোবাইল ব্যাংকিং,ক্রেডিট কার্ড,অনলাইন,চেক,অন্যান্য',
            'transaction_reference' => 'nullable|string|max:100',
            'is_recurring' => 'nullable|boolean',
            'is_anonymous' => 'nullable|boolean',
            'is_acknowledged' => 'nullable|boolean',
            'receipt_generated' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['donation_date'] = $data['donation_date'] ?? today();

        $donation = Donation::create($data);

        $donation->load('donor:id,name_bn,name_en');
        $donation->load('fund:id,name_bn');

        return $this->successResponse($donation, 'দান তৈরি সফল', 201);
    }

    // ─── Expenses ────────────────────────────────────────────────────────────

    public function expenses(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Expense::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->where('description_bn', 'like', "%{$request->input('search')}%"))
            ->when($request->has('vendor_id'), fn($q) => $q->where('vendor_id', $request->input('vendor_id')))
            ->when($request->has('fund_id'), fn($q) => $q->where('fund_id', $request->input('fund_id')))
            ->when($request->has('approval_status'), fn($q) => $q->where('approval_status', $request->input('approval_status')))
            ->when($request->has('from_date'), fn($q) => $q->where('transaction_date', '>=', $request->input('from_date')))
            ->when($request->has('to_date'), fn($q) => $q->where('transaction_date', '<=', $request->input('to_date')))
            ->with('vendor:id,name_bn,name_en')
            ->with('fund:id,name_bn')
            ->orderBy('transaction_date', 'desc');

        $expenses = $query->paginate($perPage);

        return $this->successResponse($expenses);
    }

    public function storeExpense(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'description_bn' => 'required|string|max:500',
            'description_en' => 'nullable|string|max:500',
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'nullable|date',
            'vendor_id' => 'nullable|integer|exists:vendors,id',
            'fund_id' => 'nullable|integer|exists:funds,id',
            'payment_method' => 'nullable|in:নগদ,ব্যাংক,মোবাইল ব্যাংকিং,চেক,অন্যান্য',
            'is_approved' => 'nullable|boolean',
            'is_paid' => 'nullable|boolean',
            'approval_status' => 'nullable|in:pending,approved,rejected',
            'approved_by_user_id' => 'nullable|integer|exists:users,id',
            'document_url' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['transaction_date'] = $data['transaction_date'] ?? today();
        $data['is_paid'] = $data['is_paid'] ?? true;

        $expense = Expense::create($data);

        $expense->load('vendor:id,name_bn,name_en');
        $expense->load('fund:id,name_bn');

        return $this->successResponse($expense, 'ব্যয় তৈরি সফল', 201);
    }

    // ─── Vendors ──────────────────────────────────────────────────────────────

    public function vendors(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Vendor::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->where('name_bn', 'like', "%{$request->input('search')}%"))
            ->orderBy('name_bn');

        $vendors = $query->paginate($perPage);

        return $this->successResponse($vendors);
    }

    public function storeVendor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name_bn' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:100',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'address_bn' => 'nullable|string',
            'tax_id' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;

        $vendor = Vendor::create($data);

        return $this->successResponse($vendor, 'পূরোদন তৈরি সফল', 201);
    }

    // ─── Fee Structure ────────────────────────────────────────────────────────

    public function feeStructures(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = FeeStructure::where('tenant_id', $user->tenant_id)
            ->when($request->has('class_id'), fn($q) => $q->where('class_id', $request->input('class_id')))
            ->when($request->has('session_id'), fn($q) => $q->where('session_id', $request->input('session_id')))
            ->orderBy('name_bn');

        $structures = $query->paginate($perPage);

        return $this->successResponse($structures);
    }

    public function storeFeeStructure(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name_bn' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'class_id' => 'nullable|integer',
            'session_id' => 'nullable|integer',
            'total_fee' => 'nullable|numeric',
            'description_bn' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;

        $structure = FeeStructure::create($data);

        return $this->successResponse($structure, 'ফি কাঠামো তৈরি সফল', 201);
    }

    // ─── Fee Payments ─────────────────────────────────────────────────────────

    public function feePayments(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = FeePayment::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->whereHas('student', fn($s) => $s->where('name_bn', 'like', "%{$request->input('search')}%")))
            ->when($request->has('student_id'), fn($q) => $q->where('student_id', $request->input('student_id')))
            ->when($request->has('fee_structure_id'), fn($q) => $q->where('fee_structure_id', $request->input('fee_structure_id')))
            ->when($request->has('is_fully_paid'), fn($q) => $q->where('is_fully_paid', filter_var($request->input('is_fully_paid'), FILTER_VALIDATE_BOOLEAN)))
            ->when($request->has('from_date'), fn($q) => $q->where('due_date', '>=', $request->input('from_date')))
            ->when($request->has('to_date'), fn($q) => $q->where('due_date', '<=', $request->input('to_date')))
            ->with('student:id,name_bn,name_en')
            ->with('feeStructure:id,name_bn')
            ->orderBy('due_date', 'desc');

        $payments = $query->paginate($perPage);

        return $this->successResponse($payments);
    }

    public function storeFeePayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'fee_structure_id' => 'nullable|integer|exists:fee_structures,id',
            'fee_type' => 'nullable|string|max:100',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'paid_date' => 'nullable|date',
            'payment_method' => 'nullable|in:নগদ,ব্যাংক,মোবাইল ব্যাংকিং,অনলাইন,অন্যান্য',
            'transaction_ref' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'late_fee_charged' => 'nullable|numeric',
            'waiver_applied' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['paid_amount'] = $data['paid_amount'] ?? 0;
        $data['balance'] = $data['total_amount'] - $data['paid_amount'];
        $data['is_fully_paid'] = $data['balance'] <= 0;
        $data['paid_date'] = $data['paid_date'] ?? ($data['is_fully_paid'] ? today() : null);

        $payment = FeePayment::create($data);

        $payment->load('student.user:id,name_bn,name_en');

        return $this->successResponse($payment, 'ফি পেমেন্ট তৈরি সফল', 201);
    }

    // ─── Journal + Cash Book ──────────────────────────────────────────────────

    public function journalEntries(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = JournalEntry::where('tenant_id', $user->tenant_id)
            ->when($request->has('from_date'), fn($q) => $q->where('entry_date', '>=', $request->input('from_date')))
            ->when($request->has('to_date'), fn($q) => $q->where('entry_date', '<=', $request->input('to_date')))
            ->with('fund:id,name_bn')
            ->orderBy('entry_date', 'desc')
            ->orderBy('created_at', 'desc');

        $entries = $query->paginate($perPage);

        return $this->successResponse($entries);
    }

    public function storeJournalEntry(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'entry_date' => 'nullable|date',
            'reference_number' => 'nullable|string|max:100',
            'description_bn' => 'nullable|string',
            'description_en' => 'nullable|string',
            'is_revenue' => 'nullable|boolean',
            'fund_id' => 'nullable|integer|exists:funds,id',
            'lines' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['entry_date'] = $data['entry_date'] ?? today();

        $entry = JournalEntry::create($data);

        $entry->load('fund:id,name_bn');

        return $this->successResponse($entry, 'জার্নাল এন্ট্রি তৈরি সফল', 201);
    }

    public function cashBooks(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = CashBook::where('tenant_id', $user->tenant_id)
            ->when($request->has('from_date'), fn($q) => $q->where('transaction_date', '>=', $request->input('from_date')))
            ->when($request->has('to_date'), fn($q) => $q->where('transaction_date', '<=', $request->input('to_date')))
            ->with('fund:id,name_bn')
            ->orderBy('transaction_date', 'desc');

        $books = $query->paginate($perPage);

        return $this->successResponse($books);
    }

    public function storeCashBook(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transaction_date' => 'nullable|date',
            'description_bn' => 'required|string|max:500',
            'description_en' => 'nullable|string|max:500',
            'debit_amount' => 'nullable|numeric|min:0',
            'credit_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'transaction_reference' => 'nullable|string|max:100',
            'balance' => 'nullable|numeric',
            'is_reconciled' => 'nullable|boolean',
            'fund_id' => 'nullable|integer|exists:funds,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['transaction_date'] = $data['transaction_date'] ?? today();

        $book = CashBook::create($data);

        $book->load('fund:id,name_bn');

        return $this->successResponse($book, 'ক্যাশবুক এন্ট্রি তৈরি সফল', 201);
    }

    // ─── Inventory (Stock) ────────────────────────────────────────────────────

    public function stocks(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Stock::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->where('item_name_bn', 'like', "%{$request->input('search')}%"))
            ->when($request->has('category_id'), fn($q) => $q->where('category_id', $request->input('category_id')))
            ->when($request->has('vendor_id'), fn($q) => $q->where('vendor_id', $request->input('vendor_id')))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->with('vendor:id,name_bn,name_en')
            ->with('category:id,name_bn,name_en')
            ->orderBy('item_name_bn');

        $stocks = $query->paginate($perPage);

        return $this->successResponse($stocks);
    }

    public function storeStock(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'item_name_bn' => 'required|string|max:255',
            'item_name_en' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer',
            'vendor_id' => 'nullable|integer|exists:vendors,id',
            'sku' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:50',
            'quantity' => 'nullable|numeric|min:0',
            'minimum_quantity' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric',
            'purchase_price' => 'nullable|numeric',
            'status' => 'nullable|in:active,inactive,out_of_stock,damaged,lost',
            'location' => 'nullable|string|max:255',
            'description_bn' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['is_active'] = $data['is_active'] ?? true;
        $data['quantity'] = $data['quantity'] ?? 0;

        $stock = Stock::create($data);

        $stock->load('vendor:id,name_bn,name_en');

        return $this->successResponse($stock, 'স্টক তৈরি সফল', 201);
    }

    public function updateStock(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $stock = Stock::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$stock) {
            return $this->errorResponse('স্টক পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'item_name_bn' => 'nullable|string|max:255',
            'item_name_en' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer',
            'vendor_id' => 'nullable|integer|exists:vendors,id',
            'sku' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:50',
            'quantity' => 'nullable|numeric|min:0',
            'minimum_quantity' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric',
            'purchase_price' => 'nullable|numeric',
            'status' => 'nullable|in:active,inactive,out_of_stock,damaged,lost',
            'location' => 'nullable|string|max:255',
            'description_bn' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $stock->update($validator->validated());

        return $this->successResponse($stock->fresh(), 'স্টক আপডেট সফল');
    }

    public function destroyStock(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $stock = Stock::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$stock) {
            return $this->errorResponse('স্টক পাওয়া যায়নি', 404);
        }

        $stock->delete();

        return $this->successResponse(null, 'স্টক মুছে ফেলা সফল');
    }

    // ─── Stock Transactions ───────────────────────────────────────────────────

    public function stockTransactions(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = StockTransaction::where('tenant_id', $user->tenant_id)
            ->when($request->has('stock_id'), fn($q) => $q->where('stock_id', $request->input('stock_id')))
            ->when($request->has('type'), fn($q) => $q->where('type', $request->input('type')))
            ->when($request->has('from_date'), fn($q) => $q->where('transaction_date', '>=', $request->input('from_date')))
            ->when($request->has('to_date'), fn($q) => $q->where('transaction_date', '<=', $request->input('to_date')))
            ->with('stock:id,item_name_bn')
            ->with('createdByUser:id,name_bn,name_en')
            ->orderBy('transaction_date', 'desc');

        $transactions = $query->paginate($perPage);

        return $this->successResponse($transactions);
    }

    public function storeStockTransaction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'stock_id' => 'required|integer|exists:stocks,id',
            'type' => 'required|in:receive,issue,adjust,transfer,return',
            'quantity' => 'required|numeric',
            'transaction_date' => 'nullable|date',
            'reason' => 'nullable|string|max:500',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['transaction_date'] = $data['transaction_date'] ?? today();
        $data['created_by_user_id'] = $request->user()->id;

        $transaction = StockTransaction::create($data);

        $transaction->load('stock:id,item_name_bn');

        return $this->successResponse($transaction, 'স্টক লেনদেন তৈরি সফল', 201);
    }

    // ─── Detail endpoints (single resource) ──────────────────────────────────

    public function showFund(Request $request, int $id): JsonResponse
    {
        $fund = Fund::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$fund) {
            return $this->errorResponse('ফান্ড পাওয়া যায়নি', 404);
        }

        return $this->successResponse($fund, 'ফান্ড বিবরণ');
    }

    public function showDonation(Request $request, int $id): JsonResponse
    {
        $donation = Donation::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $id)
            ->with('donor:id,name_bn,name_en')
            ->with('fund:id,name_bn')
            ->first();

        if (!$donation) {
            return $this->errorResponse('দান পাওয়া যায়নি', 404);
        }

        return $this->successResponse($donation, 'দান বিবরণ');
    }

    public function showExpense(Request $request, int $id): JsonResponse
    {
        $expense = Expense::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $id)
            ->with('vendor:id,name_bn,name_en')
            ->with('fund:id,name_bn')
            ->first();

        if (!$expense) {
            return $this->errorResponse('ব্যয় পাওয়া যায়নি', 404);
        }

        return $this->successResponse($expense, 'ব্যয় বিবরণ');
    }

    // ─── Summary ──────────────────────────────────────────────────────────────
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalDonations = Donation::where('tenant_id', $user->tenant_id)->sum('amount') ?? 0;
        $totalExpenses = Expense::where('tenant_id', $user->tenant_id)->sum('amount') ?? 0;
        $totalFeeCollected = FeePayment::where('tenant_id', $user->tenant_id)->where('is_fully_paid', true)->sum('paid_amount') ?? 0;
        $totalOutstanding = FeePayment::where('tenant_id', $user->tenant_id)->where('is_fully_paid', false)->sum('balance') ?? 0;

        return $this->successResponse([
            'total_donations' => $totalDonations,
            'total_expenses' => $totalExpenses,
            'total_fee_collected' => $totalFeeCollected,
            'total_outstanding_fees' => $totalOutstanding,
            'net_balance' => $totalDonations + $totalFeeCollected - $totalExpenses,
        ], 'আর্থিক সারসংক্ষেপ');
    }
}
