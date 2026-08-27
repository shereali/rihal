<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\FixedAsset;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountingController extends Controller
{
    public function chart(Request $request): JsonResponse
    {
        $accounts = ChartOfAccount::when($request->type, fn($q, $t) => $q->where('account_type', $t))
            ->orderBy('code')
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $accounts,
        ]);
    }

    public function storeChartAccount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'         => 'required|string|max:20',
            'name'         => 'required|string|max:100',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id'    => 'nullable|integer',
        ]);

        $account = ChartOfAccount::create(array_merge($validated, [
            'tenant_id' => $request->user()?->tenant_id,
            'status'    => 'active',
        ]));

        return response()->json([
            'status' => 201,
            'data' => $account,
        ], 201);
    }

    public function vouchers(Request $request): JsonResponse
    {
        $vouchers = JournalEntry::with('lines.chartOfAccount')
            ->when($request->type, fn($q, $t) => $q->where('entry_type', $t))
            ->latest('date')
            ->paginate($request->query('per_page', 25));

        return response()->json([
            'status' => 200,
            'data' => $vouchers,
        ]);
    }

    public function storeVoucher(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entry_type'  => 'required|in:journal,payment,receipt,contra,PV,RV,JV,CV',
            'date'        => 'required|date',
            'description' => 'required|string',
            'amount'      => 'required|numeric|min:0.01',
            'debit_account_id'  => 'nullable|integer',
            'credit_account_id' => 'nullable|integer',
        ]);

        $entry = JournalEntry::create([
            'tenant_id'    => $request->user()?->tenant_id,
            'entry_number' => strtoupper(substr($validated['entry_type'], 0, 2)) . '-' . date('Y') . '-' . rand(100, 999),
            'entry_type'   => $validated['entry_type'],
            'date'         => $validated['date'],
            'description'  => $validated['description'],
            'status'       => 'approved',
            'total_debit'  => $validated['amount'],
            'total_credit' => $validated['amount'],
            'created_by'   => $request->user()?->id,
        ]);

        return response()->json([
            'status' => 201,
            'data' => $entry,
        ], 201);
    }

    public function trialBalance(Request $request): JsonResponse
    {
        $accounts = ChartOfAccount::orderBy('code')->get();
        return response()->json([
            'status' => 200,
            'data' => $accounts,
        ]);
    }

    public function fixedAssets(Request $request): JsonResponse
    {
        $assets = FixedAsset::latest('purchase_date')->get();
        return response()->json([
            'status' => 200,
            'data' => $assets,
        ]);
    }

    public function storeFixedAsset(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'category'      => 'required|string|max:100',
            'purchase_date' => 'required|date',
            'cost'          => 'required|numeric|min:0',
            'dep_rate'      => 'nullable|numeric|min:0|max:100',
            'location'      => 'nullable|string|max:255',
        ]);

        $cost = $validated['cost'];
        $depRate = $validated['dep_rate'] ?? 10.00;

        $asset = FixedAsset::create(array_merge($validated, [
            'tenant_id'  => $request->user()?->tenant_id,
            'tag'        => 'AST-' . rand(100, 999),
            'dep_rate'   => $depRate,
            'book_value' => $cost,
        ]));

        return response()->json([
            'status' => 201,
            'data' => $asset,
        ], 201);
    }

    public function destroyFixedAsset($id): JsonResponse
    {
        FixedAsset::findOrFail($id)->delete();
        return response()->json([
            'status' => 200,
            'message' => 'স্থায়ী সম্পদ মুছে ফেলা হয়েছে',
        ]);
    }
}
