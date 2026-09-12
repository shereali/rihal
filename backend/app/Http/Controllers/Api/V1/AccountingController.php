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
            ->orderBy('account_code')
            ->get()
            ->map(function ($acc) {
                return [
                    'id'                => $acc->id,
                    'code'              => $acc->account_code,
                    'account_code'      => $acc->account_code,
                    'name'              => $acc->account_name_bn,
                    'account_name_bn'   => $acc->account_name_bn,
                    'account_name_en'   => $acc->account_name_en,
                    'account_type'      => $acc->account_type,
                    'parent_id'         => $acc->parent_account_id,
                    'parent_account_id' => $acc->parent_account_id,
                    'is_active'         => (bool) $acc->is_active,
                    'created_at'        => $acc->created_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'status' => 200,
            'data'   => $accounts,
        ]);
    }

    public function storeChartAccount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'              => 'nullable|string|max:20',
            'account_code'      => 'nullable|string|max:20',
            'name'              => 'nullable|string|max:100',
            'account_name_bn'   => 'nullable|string|max:100',
            'account_name_en'   => 'nullable|string|max:100',
            'account_type'      => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id'         => 'nullable|integer',
            'parent_account_id' => 'nullable|integer',
        ]);

        $code = $validated['account_code'] ?? $validated['code'] ?? ('ACC-' . rand(100, 999));
        $nameBn = $validated['account_name_bn'] ?? $validated['name'] ?? 'হিসাব খাত';
        $parentId = $validated['parent_account_id'] ?? $validated['parent_id'] ?? null;

        $account = ChartOfAccount::create([
            'tenant_id'         => $request->user()?->tenant_id,
            'account_code'      => $code,
            'account_name_bn'   => $nameBn,
            'account_name_en'   => $validated['account_name_en'] ?? null,
            'account_type'      => $validated['account_type'],
            'parent_account_id' => $parentId,
            'is_active'         => true,
        ]);

        return response()->json([
            'status' => 201,
            'data'   => $account,
        ], 201);
    }

    public function vouchers(Request $request): JsonResponse
    {
        $vouchers = JournalEntry::with('lines.chartOfAccount')
            ->when($request->type, fn($q, $t) => $q->where('status', $t))
            ->latest('id')
            ->paginate($request->query('per_page', 25));

        return response()->json([
            'status' => 200,
            'data'   => $vouchers,
        ]);
    }

    public function storeVoucher(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entry_type'        => 'required|in:journal,payment,receipt,contra,PV,RV,JV,CV',
            'date'              => 'required|date',
            'description'       => 'required|string',
            'amount'            => 'required|numeric|min:0.01',
            'debit_account_id'  => 'nullable|integer',
            'credit_account_id' => 'nullable|integer',
        ]);

        $date = $validated['date'];
        $desc = $validated['description'];
        $amount = (float) $validated['amount'];

        $entry = JournalEntry::create([
            'tenant_id'          => $request->user()?->tenant_id,
            'reference_no'       => strtoupper(substr($validated['entry_type'], 0, 2)) . '-' . date('Y') . '-' . rand(100, 999),
            'transaction_date'   => $date,
            'description_bn'     => $desc,
            'description_en'     => $desc,
            'status'             => 'approved',
            'created_by_user_id' => $request->user()?->id,
        ]);

        if (!empty($validated['debit_account_id'])) {
            JournalEntryLine::create([
                'tenant_id'        => $request->user()?->tenant_id,
                'journal_entry_id' => $entry->id,
                'debit_account_id' => $validated['debit_account_id'],
                'type'             => 'debit',
                'amount'           => $amount,
                'description'      => $desc,
            ]);
        }

        if (!empty($validated['credit_account_id'])) {
            JournalEntryLine::create([
                'tenant_id'         => $request->user()?->tenant_id,
                'journal_entry_id'  => $entry->id,
                'credit_account_id' => $validated['credit_account_id'],
                'type'              => 'credit',
                'amount'            => $amount,
                'description'       => $desc,
            ]);
        }

        return response()->json([
            'status' => 201,
            'data'   => $entry,
        ], 201);
    }

    public function trialBalance(Request $request): JsonResponse
    {
        $accounts = ChartOfAccount::orderBy('account_code')->get()->map(function ($acc) {
            $debit = (float) ($acc->debitEntries()->sum('amount') ?? 0);
            $credit = (float) ($acc->creditEntries()->sum('amount') ?? 0);

            return [
                'id'              => $acc->id,
                'code'            => $acc->account_code,
                'account_code'    => $acc->account_code,
                'name'            => $acc->account_name_bn,
                'account_name_bn' => $acc->account_name_bn,
                'account_type'    => $acc->account_type,
                'debit'           => $debit,
                'credit'          => $credit,
                'balance'         => abs($debit - $credit),
            ];
        });

        return response()->json([
            'status' => 200,
            'data'   => $accounts,
        ]);
    }

    public function fixedAssets(Request $request): JsonResponse
    {
        $assets = FixedAsset::latest('purchase_date')->get();
        return response()->json([
            'status' => 200,
            'data'   => $assets,
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
            'data'   => $asset,
        ], 201);
    }

    public function destroyFixedAsset($id): JsonResponse
    {
        FixedAsset::findOrFail($id)->delete();
        return response()->json([
            'status'  => 200,
            'message' => 'স্থায়ী সম্পদ মুছে ফেলা হয়েছে',
        ]);
    }
}
