<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BoardingBazaar;
use App\Models\BoardingMeal;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BoardingController extends Controller
{
    public function indexBazaar(Request $request): JsonResponse
    {
        $bazaars = BoardingBazaar::when($request->search, function ($q, $search) {
            $q->where('voucher_no', 'like', "%{$search}%")
              ->orWhere('buyer_name', 'like', "%{$search}%")
              ->orWhere('items_summary', 'like', "%{$search}%");
        })
        ->latest('date')
        ->get();

        return response()->json([
            'status' => 200,
            'data' => $bazaars,
        ]);
    }

    public function storeBazaar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date'          => 'required|date',
            'buyer_name'    => 'required|string|max:255',
            'items_summary' => 'required|string',
            'total_qty'     => 'nullable|string|max:100',
            'amount'        => 'required|numeric|min:0',
        ]);

        $bazaar = BoardingBazaar::create(array_merge($validated, [
            'tenant_id'  => $request->user()?->tenant_id,
            'voucher_no' => 'BZR-' . date('Y') . '-' . rand(100, 999),
        ]));

        return response()->json([
            'status' => 201,
            'data' => $bazaar,
        ], 201);
    }

    public function destroyBazaar($id): JsonResponse
    {
        BoardingBazaar::findOrFail($id)->delete();
        return response()->json([
            'status' => 200,
            'message' => 'বাজার ভাউচার মুছে ফেলা হয়েছে',
        ]);
    }

    public function meals(Request $request): JsonResponse
    {
        $date = $request->query('date', now()->toDateString());
        $meals = BoardingMeal::with('student.user:id,name_bn,name_en')
            ->where('date', $date)
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $meals,
        ]);
    }

    public function storeMealsBulk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'meals' => 'required|array',
            'meals.*.student_id' => 'required|integer',
            'meals.*.breakfast'  => 'boolean',
            'meals.*.lunch'      => 'boolean',
            'meals.*.dinner'     => 'boolean',
        ]);

        $tenantId = $request->user()?->tenant_id;
        $date = $validated['date'];

        foreach ($validated['meals'] as $m) {
            BoardingMeal::updateOrCreate(
                [
                    'tenant_id'  => $tenantId,
                    'student_id' => $m['student_id'],
                    'date'       => $date,
                ],
                [
                    'breakfast'  => $m['breakfast'] ?? false,
                    'lunch'      => $m['lunch'] ?? false,
                    'dinner'     => $m['dinner'] ?? false,
                ]
            );
        }

        return response()->json([
            'status' => 200,
            'message' => 'দৈনিক মিল হাজিরা সফলভাবে সংরক্ষিত হয়েছে',
        ]);
    }
}
