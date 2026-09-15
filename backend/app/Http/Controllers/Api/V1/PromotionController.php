<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Promotion;
use App\Models\Student;
use App\Models\AcademicClass;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        try {
            if (!Schema::hasTable('promotions')) {
                return response()->json([
                    'status'  => 200,
                    'message' => 'প্রমোশন তালিকা পাওয়া গেছে',
                    'data'    => [
                        'current_page' => 1,
                        'data' => [],
                        'from' => 0,
                        'last_page' => 1,
                        'per_page' => (int) ($request->per_page ?? 15),
                        'to' => 0,
                        'total' => 0,
                    ],
                ]);
            }
            $tenantId = $request->user()?->tenant_id ?? auth()->user()?->tenant_id;
            $query = Promotion::with(['student', 'fromClass', 'toClass'])
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->when($request->search, function ($q, $search) {
                    $q->whereHas('student', fn($sq) => $sq->where('name_bn', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%"));
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
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), '1146')) {
                return response()->json([
                    'status'  => 200,
                    'message' => 'প্রমোশন তালিকা পাওয়া গেছে',
                    'data'    => [
                        'current_page' => 1,
                        'data' => [],
                        'from' => 0,
                        'last_page' => 1,
                        'per_page' => (int) ($request->per_page ?? 15),
                        'to' => 0,
                        'total' => 0,
                    ],
                ]);
            }
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
            $tenantId = $request->user()?->tenant_id ?? auth()->user()?->tenant_id;
            $validated = $request->validate([
                'student_id'     => 'required|integer|exists:students,id' . ($tenantId ? ',tenant_id,' . $tenantId : ''),
                'from_class_id'  => 'required|integer|exists:academic_classes,id' . ($tenantId ? ',tenant_id,' . $tenantId : ''),
                'to_class_id'    => 'required|integer|exists:academic_classes,id' . ($tenantId ? ',tenant_id,' . $tenantId : ''),
                'academic_year'  => 'required|string|max:20',
                'promotion_date' => 'required|date',
                'status'         => 'required|in:pending,approved,rejected',
                'comments'       => 'nullable|string|max:500',
            ]);

            if ((int) $validated['from_class_id'] === (int) $validated['to_class_id']) {
                return response()->json([
                    'status'  => 422,
                    'message' => 'পূর্ববর্তী শ্রেণি এবং পরবর্তী শ্রেণি একই হতে পারে না।',
                    'errors'  => ['to_class_id' => ['উত্তীর্ণ শ্রেণি অবশ্যই ভিন্ন হতে হবে।']],
                ], 422);
            }

            $promotion = DB::transaction(function () use ($validated, $tenantId) {
                $promo = Promotion::create(array_merge($validated, [
                    'tenant_id'   => $tenantId,
                    'promoted_by' => auth()->id(),
                ]));

                if ($validated['status'] === 'approved') {
                    $this->syncStudentEnrollment(
                        (int) $validated['student_id'],
                        (int) $validated['from_class_id'],
                        (int) $validated['to_class_id'],
                        (string) $validated['promotion_date'],
                        $tenantId
                    );
                }

                return $promo;
            });

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
            $tenantId = $promotion->tenant_id ?? (request()->user()?->tenant_id ?? auth()->user()?->tenant_id);

            DB::transaction(function () use ($promotion, $tenantId) {
                $promotion->update(['status' => 'approved', 'promoted_by' => auth()->id()]);
                $this->syncStudentEnrollment(
                    (int) $promotion->student_id,
                    (int) $promotion->from_class_id,
                    (int) $promotion->to_class_id,
                    (string) ($promotion->promotion_date ?? today()->toDateString()),
                    $tenantId
                );
            });

            return response()->json([
                'status'  => 200,
                'message' => 'প্রমোশন সফলভাবে অনুমোদিত এবং শিক্ষার্থীর শ্রেণি হালনাগাদ করা হয়েছে',
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
            $tenantId = $request->user()?->tenant_id ?? auth()->user()?->tenant_id;
            $validated = $request->validate([
                'from_class_id'  => 'required|integer|exists:academic_classes,id' . ($tenantId ? ',tenant_id,' . $tenantId : ''),
                'to_class_id'    => 'required|integer|exists:academic_classes,id' . ($tenantId ? ',tenant_id,' . $tenantId : ''),
                'academic_year'  => 'required|string|max:20',
                'promotion_date' => 'required|date',
                'student_ids'    => 'required|array|min:1',
            ]);

            if ((int) $validated['from_class_id'] === (int) $validated['to_class_id']) {
                return response()->json([
                    'status'  => 422,
                    'message' => 'পূর্ববর্তী শ্রেণি এবং পরবর্তী শ্রেণি একই হতে পারে না।',
                    'errors'  => ['to_class_id' => ['উত্তীর্ণ শ্রেণি অবশ্যই পূর্ববর্তী শ্রেণির চেয়ে ভিন্ন হতে হবে।']],
                ], 422);
            }

            $results = ['promoted' => 0, 'skipped' => 0, 'errors' => []];

            DB::transaction(function () use ($validated, $tenantId, &$results) {
                foreach ($validated['student_ids'] as $studentId) {
                    try {
                        $exists = Promotion::where('student_id', $studentId)
                            ->where('from_class_id', $validated['from_class_id'])
                            ->where('academic_year', $validated['academic_year'])
                            ->exists();

                        if ($exists) {
                            $results['skipped']++;
                            continue;
                        }

                        Promotion::create([
                            'student_id'     => $studentId,
                            'from_class_id'  => $validated['from_class_id'],
                            'to_class_id'    => $validated['to_class_id'],
                            'academic_year'  => $validated['academic_year'],
                            'promotion_date' => $validated['promotion_date'],
                            'status'         => 'approved',
                            'tenant_id'      => $tenantId,
                            'promoted_by'    => auth()->id(),
                        ]);

                        $this->syncStudentEnrollment(
                            (int) $studentId,
                            (int) $validated['from_class_id'],
                            (int) $validated['to_class_id'],
                            (string) $validated['promotion_date'],
                            $tenantId
                        );

                        $results['promoted']++;
                    } catch (\Exception $e) {
                        $results['errors'][] = ['student_id' => $studentId, 'message' => $e->getMessage()];
                    }
                }
            });

            return response()->json([
                'status'  => 200,
                'message' => $results['promoted'] . ' জন শিক্ষার্থী প্রমোশন ও শ্রেণি হালনাগাদ করা হয়েছে',
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

    /**
     * Synchronize student enrollment upon promotion
     */
    protected function syncStudentEnrollment(int $studentId, int $fromClassId, int $toClassId, string $promotionDate, ?int $tenantId): void
    {
        try {
            if (!Schema::hasTable('enrollments')) {
                return;
            }

            $student = Student::find($studentId);
            if (!$student) {
                return;
            }

            $targetStudentId = $student->user_id ?? $student->id;

            // 1. Mark existing active enrollment in from_class_id as completed/promoted
            Enrollment::where('tenant_id', $tenantId)
                ->where(function ($q) use ($student, $targetStudentId) {
                    $q->where('student_id', $student->id)
                      ->orWhere('student_id', $targetStudentId);
                })
                ->where('class_id', $fromClassId)
                ->whereIn('status', ['active', 'enrolled', 'approved'])
                ->update([
                    'status'            => 'completed',
                    'promotion_date'    => $promotionDate,
                    'promoted_to_class' => $toClassId,
                ]);

            // 2. Check if an active enrollment already exists in to_class_id
            $hasNewEnrollment = Enrollment::where('tenant_id', $tenantId)
                ->where(function ($q) use ($student, $targetStudentId) {
                    $q->where('student_id', $student->id)
                      ->orWhere('student_id', $targetStudentId);
                })
                ->where('class_id', $toClassId)
                ->whereIn('status', ['active', 'enrolled', 'approved'])
                ->exists();

            if (!$hasNewEnrollment) {
                $year = date('Y');
                $seq = (Enrollment::withTrashed()->where('tenant_id', $tenantId)->whereYear('enrollment_date', $year)->count()) + 1;
                $enrNumber = "EN-$year-" . str_pad($seq, 4, '0', STR_PAD_LEFT);

                // Get active session if available
                $sessionId = DB::table('academic_sessions')
                    ->where('tenant_id', $tenantId)
                    ->where('is_active', 1)
                    ->value('id') ?? DB::table('academic_sessions')->where('tenant_id', $tenantId)->value('id');

                Enrollment::create([
                    'tenant_id'         => $tenantId,
                    'student_id'        => $targetStudentId,
                    'class_id'          => $toClassId,
                    'session_id'        => $sessionId,
                    'enrollment_number' => $enrNumber,
                    'enrollment_date'   => $promotionDate,
                    'status'            => 'active',
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to sync student enrollment on promotion: ' . $e->getMessage());
        }
    }

    public function classWise(Request $request)
    {
        try {
            $tenantId = $request->user()?->tenant_id ?? auth()->user()?->tenant_id;
            if (!Schema::hasTable('promotions')) {
                $classes = AcademicClass::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                    ->orderBy('name_bn')
                    ->get()
                    ->map(function ($c) {
                        $c->promotions_count = 0;
                        return $c;
                    });
                return response()->json([
                    'status'  => 200,
                    'message' => 'শ্রেণি অনুযায়ী প্রমোশন তথ্য পাওয়া গেছে',
                    'data'    => $classes,
                ]);
            }
            $classes = AcademicClass::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->withCount(['promotions' => fn($q) => $q->where('status', 'approved')])
                ->orderBy('name_bn')
                ->get();
            return response()->json([
                'status'  => 200,
                'message' => 'শ্রেণি অনুযায়ী প্রমোশন তথ্য পাওয়া গেছে',
                'data'    => $classes,
            ]);
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), '1146')) {
                $tenantId = $request->user()?->tenant_id ?? auth()->user()?->tenant_id;
                $classes = AcademicClass::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                    ->orderBy('name_bn')
                    ->get()
                    ->map(function ($c) {
                        $c->promotions_count = 0;
                        return $c;
                    });
                return response()->json([
                    'status'  => 200,
                    'message' => 'শ্রেণি অনুযায়ী প্রমোশন তথ্য পাওয়া গেছে',
                    'data'    => $classes,
                ]);
            }
            return response()->json([
                'status'  => 500,
                'message' => 'শ্রেণি অনুযায়ী প্রমোশন লোড করতে সমস্যা: ' . $e->getMessage(),
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
