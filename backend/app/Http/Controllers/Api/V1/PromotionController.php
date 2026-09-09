<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Promotion;
use App\Models\Student;
use App\Models\AcademicClass;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Promotion::with(['student', 'fromClass', 'toClass'])
                ->when($request->search, function ($q, $search) {
                    $q->whereHas('student', fn($sq) => $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%"));
                })
                ->when($request->status, fn($q, $status) => $q->where('status', $status))
                ->when($request->class_id, fn($q, $cid) => $q->where('from_class_id', $cid))
                ->when($request->year, fn($q, $year) => $q->where('academic_year', $year))
                ->orderBy('promotion_date', 'desc')
                ->paginate($request->per_page ?? 15);

            return response()->json([
                'status'  => 200,
                'message' => 'প্রমোশন তালিকা পাওয়া গেছে',
                'data'    => $query,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'প্রমোশন তালিকা লোড করতে সমস্যা: ' . $e->getMessage(),
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'student_id'     => 'required|integer|exists:students,id,tenant_id,' . tenant('id'),
                'from_class_id'  => 'required|integer|exists:academic_classes,id,tenant_id,' . tenant('id'),
                'to_class_id'    => 'required|integer|exists:academic_classes,id,tenant_id,' . tenant('id'),
                'academic_year'  => 'required|string|max:20',
                'promotion_date' => 'required|date',
                'status'         => 'required|in:pending,approved,rejected',
                'comments'       => 'nullable|string|max:500',
            ]);

            $promotion = Promotion::create(array_merge($validated, [
                'tenant_id'   => tenant('id'),
                'promoted_by' => auth()->id(),
            ]));

            return response()->json([
                'status'  => 201,
                'message' => 'প্রমোশন সফলভাবে যোগ করা হয়েছে',
                'data'    => $promotion->load(['student', 'fromClass', 'toClass']),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 422,
                'message' => 'বৈধতা ত্রুটি',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'প্রমোশন যোগ করতে সমস্যা: ' . $e->getMessage(),
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $promotion = Promotion::with(['student', 'fromClass', 'toClass'])->findOrFail($id);
            return response()->json([
                'status'  => 200,
                'message' => 'প্রমোশন তথ্য পাওয়া গেছে',
                'data'    => $promotion,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'প্রমোশন পাওয়া যায়নি',
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $promotion = Promotion::findOrFail($id);
            $validated = $request->validate([
                'student_id'     => 'sometimes|integer|exists:students,id',
                'from_class_id'  => 'sometimes|integer|exists:academic_classes,id',
                'to_class_id'    => 'sometimes|integer|exists:academic_classes,id',
                'academic_year'  => 'sometimes|string|max:20',
                'promotion_date' => 'sometimes|date',
                'status'         => 'sometimes|in:pending,approved,rejected',
                'comments'       => 'nullable|string|max:500',
            ]);
            $promotion->update($validated);
            return response()->json([
                'status'  => 200,
                'message' => 'প্রমোশন আপডেট করা হয়েছে',
                'data'    => $promotion->load(['student', 'fromClass', 'toClass']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 422,
                'message' => 'বৈধতা ত্রুটি',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'প্রমোশন আপডেট করতে সমস্যা: ' . $e->getMessage(),
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $promotion = Promotion::findOrFail($id);
            $promotion->delete();
            return response()->json([
                'status'  => 200,
                'message' => 'প্রমোশন মুছে ফেলা হয়েছে',
                'data'    => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'প্রমোশন মুছে ফেলতে সমস্যা: ' . $e->getMessage(),
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function approve($id)
    {
        try {
            $promotion = Promotion::findOrFail($id);
            $promotion->update(['status' => 'approved', 'promoted_by' => auth()->id()]);
            return response()->json([
                'status'  => 200,
                'message' => 'প্রমোশন অনুমোদিত হয়েছে',
                'data'    => $promotion->fresh()->load(['student', 'fromClass', 'toClass']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'অনুমোদনে সমস্যা: ' . $e->getMessage(),
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function bulkPromote(Request $request)
    {
        try {
            $validated = $request->validate([
                'from_class_id'  => 'required|integer|exists:academic_classes,id,tenant_id,' . tenant('id'),
                'to_class_id'    => 'required|integer|exists:academic_classes,id,tenant_id,' . tenant('id'),
                'academic_year'  => 'required|string|max:20',
                'promotion_date' => 'required|date',
                'student_ids'    => 'required|array|min:1',
            ]);

            $results = ['promoted' => 0, 'skipped' => 0, 'errors' => []];

            foreach ($validated['student_ids'] as $studentId) {
                try {
                    $exists = Promotion::where('student_id', $studentId)
                        ->where('from_class_id', $validated['from_class_id'])
                        ->where('academic_year', $validated['academic_year'])
                        ->exists();
                    if ($exists) { $results['skipped']++; continue; }
                    Promotion::create([
                        'student_id'     => $studentId,
                        'from_class_id'  => $validated['from_class_id'],
                        'to_class_id'    => $validated['to_class_id'],
                        'academic_year'  => $validated['academic_year'],
                        'promotion_date' => $validated['promotion_date'],
                        'status'         => 'approved',
                        'tenant_id'      => tenant('id'),
                        'promoted_by'    => auth()->id(),
                    ]);
                    $results['promoted']++;
                } catch (\Exception $e) {
                    $results['errors'][] = ['student_id' => $studentId, 'message' => $e->getMessage()];
                }
            }

            return response()->json([
                'status'  => 200,
                'message' => $results['promoted'] . ' জন শিক্ষার্থী প্রমোশন করা হয়েছে',
                'data'    => $results,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 422,
                'message' => 'বৈধতা ত্রুটি',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'বাল্ক প্রমোশনে সমস্যা: ' . $e->getMessage(),
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function classWise(Request $request)
    {
        try {
            $classes = AcademicClass::where('tenant_id', tenant('id'))
                ->withCount(['promotions' => fn($q) => $q->where('status', 'approved')])
                ->orderBy('name')
                ->get();
            return response()->json([
                'status'  => 200,
                'message' => 'শ্রেণি অনুযায়ী প্রমোশন তথ্য পাওয়া গেছে',
                'data'    => $classes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'শ্রেণি অনুযায়ী প্রমোশন লোড করতে সমস্যা: ' . $e->getMessage(),
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
