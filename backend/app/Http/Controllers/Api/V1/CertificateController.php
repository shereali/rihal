<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\CertificateTemplate;
use App\Models\IssuedCertificate;
use App\Models\CertificateMark;
use App\Models\Student;
use App\Models\AcademicClass;
use App\Models\AcademicSubject;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CertificateController extends Controller
{
    public function templates(Request $request)
    {
        try {
            if (!Schema::hasTable('certificate_templates')) {
                return response()->json([
                    'status'  => 200,
                    'message' => 'সার্টিফিকেট টেমপলেট তালিকা পাওয়া গেছে',
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
            $query = CertificateTemplate::with(['classRelation', 'subjectRelation'])
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%{$s}%"))
                ->when($request->type, fn($q, $t) => $q->where('template_type', $t))
                ->when($request->class_id, fn($q, $c) => $q->where('class_id', $c))
                ->when($request->active_only, fn($q) => $q->where('is_active', true))
                ->orderBy('title')
                ->paginate($request->per_page ?? 15);

            return response()->json([
                'status'  => 200,
                'message' => 'সার্টিফিকেট টেমপলেট তালিকা পাওয়া গেছে',
                'data'    => $query,
            ]);
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), '1146')) {
                return response()->json([
                    'status'  => 200,
                    'message' => 'সার্টিফিকেট টেমপলেট তালিকা পাওয়া গেছে',
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
            return response()->json(['status' => 500, 'message' => 'টেমপলেট লোড করতে সমস্যা: ' . $e->getMessage(), 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function storeTemplate(Request $request)
    {
        try {
            $tenantId = $request->user()?->tenant_id ?? auth()->user()?->tenant_id;
            $validated = $request->validate([
                'title'          => 'required|string|max:150',
                'template_type'  => 'required|in:annual,transfer,sanction,conduct,others',
                'template_data'  => 'nullable|array',
                'class_id'       => 'nullable|integer|exists:academic_classes,id',
                'subject_id'     => 'nullable|integer|exists:academic_subjects,id',
                'is_active'      => 'boolean',
            ]);
            $template = CertificateTemplate::create(array_merge($validated, [
                'tenant_id' => $tenantId,
                'issued_by' => auth()->id(),
            ]));
            return response()->json(['status' => 201, 'message' => 'টেমপলেট তৈরি করা হয়েছে', 'data' => $template], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 422, 'message' => 'বৈধতা ত্রুটি', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'টেমপলেট তৈরি করতে সমস্যা: ' . $e->getMessage(), 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function updateTemplate(Request $request, $id)
    {
        try {
            $template = CertificateTemplate::findOrFail($id);
            $validated = $request->validate([
                'title'          => 'sometimes|string|max:150',
                'template_type'  => 'sometimes|in:annual,transfer,sanction,conduct,others',
                'template_data'  => 'nullable|array',
                'class_id'       => 'nullable|integer|exists:academic_classes,id',
                'subject_id'     => 'nullable|integer|exists:academic_subjects,id',
                'is_active'      => 'boolean',
            ]);
            $template->update($validated);
            return response()->json(['status' => 200, 'message' => 'টেমপলেট আপডেট করা হয়েছে', 'data' => $template], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 422, 'message' => 'বৈধতা ত্রুটি', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'টেমপলেট আপডেট করতে সমস্যা: ' . $e->getMessage(), 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function destroyTemplate($id)
    {
        try {
            CertificateTemplate::findOrFail($id)->delete();
            return response()->json(['status' => 200, 'message' => 'টেমপলেট মুছে ফেলা হয়েছে', 'data' => null], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'টেমপলেট মুছে ফেলতে সমস্যা: ' . $e->getMessage(), 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function store(Request $request)
    {
        return $this->storeTemplate($request);
    }

    public function update(Request $request, $id)
    {
        return $this->updateTemplate($request, $id);
    }

    public function destroy($id)
    {
        return $this->destroyTemplate($id);
    }

    public function issueList(Request $request)
    {
        try {
            if (!Schema::hasTable('issued_certificates')) {
                return response()->json([
                    'status'  => 200,
                    'message' => 'সার্টিফিকেট প্রকাশনা তালিকা পাওয়া গেছে',
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
            $query = IssuedCertificate::with(['templateRelation', 'studentRelation', 'classRelation', 'subjectRelation'])
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->when($request->student_id, fn($q, $s) => $q->where('student_id', $s))
                ->when($request->class_id, fn($q, $c) => $q->where('class_id', $c))
                ->when($request->search, fn($q, $s) => $q->whereHas('studentRelation', fn($sq) => $sq->where('name_bn', 'like', "%{$s}%")->orWhere('name_en', 'like', "%{$s}%")->orWhere('admission_number', 'like', "%{$s}%")))
                ->orderBy('issue_date', 'desc')
                ->paginate($request->per_page ?? 15);

            return response()->json([
                'status'  => 200,
                'message' => 'সার্টিফিকেট প্রকাশনা তালিকা পাওয়া গেছে',
                'data'    => $query,
            ]);
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), '1146')) {
                return response()->json([
                    'status'  => 200,
                    'message' => 'সার্টিফিকেট প্রকাশনা তালিকা পাওয়া গেছে',
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
            return response()->json(['status' => 500, 'message' => 'প্রকাশনা লোড করতে সমস্যা: ' . $e->getMessage(), 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function issueCertificate(Request $request)
    {
        try {
            $user = $request->user();
            $tenantId = $user?->tenant_id ?? auth()->user()?->tenant_id;
            $validated = $request->validate([
                'template_id'    => 'required|integer|exists:certificate_templates,id',
                'student_id'     => 'required|integer|exists:students,id' . ($tenantId ? ',tenant_id,' . $tenantId : ''),
                'class_id'       => 'nullable|integer|exists:academic_classes,id',
                'subject_id'     => 'nullable|integer|exists:academic_subjects,id',
                'issue_date'     => 'required|date',
                'authorized_by'  => 'nullable|string|max:100',
                'remarks'        => 'nullable|string|max:300',
            ]);

            // Auto-detect class_id from active enrollment if not explicitly supplied
            if (empty($validated['class_id'])) {
                $student = Student::find($validated['student_id']);
                $targetId = $student?->user_id ?? $student?->id;
                if ($targetId && Schema::hasTable('enrollments')) {
                    $validated['class_id'] = DB::table('enrollments')
                        ->where('tenant_id', $tenantId)
                        ->where(function ($q) use ($student, $targetId) {
                            $q->where('student_id', $student->id)
                              ->orWhere('student_id', $targetId);
                        })
                        ->whereIn('status', ['active', 'enrolled', 'approved'])
                        ->value('class_id');
                }
            }

            // Duplicate issuance check: prevent duplicate certificate for same student and template on the same date
            $alreadyIssued = IssuedCertificate::where('tenant_id', $tenantId)
                ->where('template_id', $validated['template_id'])
                ->where('student_id', $validated['student_id'])
                ->whereDate('issue_date', $validated['issue_date'])
                ->exists();

            if ($alreadyIssued) {
                return response()->json([
                    'status'  => 422,
                    'message' => 'এই শিক্ষার্থীর জন্য এই তারিখে ইতিমধ্যে একই সার্টিফিকেট ইস্যু করা হয়েছে।',
                    'errors'  => ['student_id' => ['ইতিমধ্যে এই তারিখে সার্টিফিকেটটি ইস্যু করা হয়েছে।']],
                ], 422);
            }

            $cert = DB::transaction(function () use ($validated, $tenantId, $user) {
                $tenantSlug = $user?->tenant?->slug ?? 'AT';
                $seq = (IssuedCertificate::withTrashed()->where('tenant_id', $tenantId)->count()) + 1;
                do {
                    $certNumber = 'CERT-' . strtoupper(substr($tenantSlug, 0, 8)) . '-' . date('Y') . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
                    $seq++;
                } while (IssuedCertificate::withTrashed()->where('certificate_number', $certNumber)->exists());

                return IssuedCertificate::create(array_merge($validated, [
                    'tenant_id'          => $tenantId,
                    'certificate_number' => $certNumber,
                ]));
            });

            return response()->json([
                'status'  => 201,
                'message' => 'সার্টিফিকেট সফলভাবে প্রকাশিত হয়েছে (নম্বর: ' . $cert->certificate_number . ')',
                'data'    => $cert->load(['templateRelation', 'studentRelation', 'classRelation', 'subjectRelation']),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 422, 'message' => 'বৈধতা ত্রুটি', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'সার্টিফিকেট প্রকাশ করতে সমস্যা: ' . $e->getMessage(), 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function issueDetails($id)
    {
        try {
            $cert = IssuedCertificate::with(['templateRelation', 'studentRelation', 'classRelation', 'subjectRelation'])->findOrFail($id);
            return response()->json(['status' => 200, 'message' => 'সার্টিফিকেটের বিবরণ পাওয়া গেছে', 'data' => $cert], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 404, 'message' => 'সার্টিফিকেট পাওয়া যায়নি'], 404);
        }
    }

    public function destroyIssue($id)
    {
        try {
            IssuedCertificate::findOrFail($id)->delete();
            return response()->json(['status' => 200, 'message' => 'সার্টিফিকেট মুছে ফেলা হয়েছে', 'data' => null], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'সার্টিফিকেট মুছে ফেলতে সমস্যা: ' . $e->getMessage(), 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function markList(Request $request)
    {
        try {
            if (!Schema::hasTable('certificate_marks')) {
                return response()->json([
                    'status'  => 200,
                    'message' => 'মার্ক তালিকা পাওয়া গেছে',
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
            $query = CertificateMark::with(['templateRelation', 'studentRelation', 'classRelation', 'subjectRelation'])
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->when($request->template_id, fn($q, $t) => $q->where('template_id', $t))
                ->when($request->student_id, fn($q, $s) => $q->where('student_id', $s))
                ->when($request->class_id, fn($q, $c) => $q->where('class_id', $c))
                ->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 15);

            return response()->json(['status' => 200, 'message' => 'মার্ক তালিকা পাওয়া গেছে', 'data' => $query], 200);
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), '1146')) {
                return response()->json([
                    'status'  => 200,
                    'message' => 'মার্ক তালিকা পাওয়া গেছে',
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
            return response()->json(['status' => 500, 'message' => 'মার্ক লোড করতে সমস্যা: ' . $e->getMessage(), 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function storeMark(Request $request)
    {
        try {
            $tenantId = $request->user()?->tenant_id ?? auth()->user()?->tenant_id;
            $validated = $request->validate([
                'template_id'    => 'required|integer|exists:certificate_templates,id',
                'student_id'     => 'required|integer|exists:students,id' . ($tenantId ? ',tenant_id,' . $tenantId : ''),
                'class_id'       => 'nullable|integer|exists:academic_classes,id',
                'subject_id'     => 'nullable|integer|exists:academic_subjects,id',
                'mark_obtained'  => 'required|integer|min:0',
                'mark_total'     => 'required|integer|min:0',
                'passing_mark'   => 'nullable|integer|min:0',
                'grade'          => 'nullable|string|max:10',
                'remark'         => 'nullable|string|max:200',
            ]);
            $mark = CertificateMark::create(array_merge($validated, ['tenant_id' => $tenantId]));
            return response()->json(['status' => 201, 'message' => 'মার্ক যোগ করা হয়েছে', 'data' => $mark], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 422, 'message' => 'বৈধতা ত্রুটি', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'মার্ক যোগ করতে সমস্যা: ' . $e->getMessage(), 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function destroyMark($id)
    {
        try {
            CertificateMark::findOrFail($id)->delete();
            return response()->json(['status' => 200, 'message' => 'মার্ক মুছে ফেলা হয়েছে', 'data' => null], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'মার্ক মুছে ফেলতে সমস্যা: ' . $e->getMessage(), 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function syllabusList(Request $request)
    {
        try {
            $tenantId = $request->user()?->tenant_id ?? auth()->user()?->tenant_id;
            $subjects = AcademicSubject::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->when($request->class_id, fn($q, $c) => $q->where('class_id', $c)->orWhereHas('classes', fn($qc) => $qc->where('academic_classes.id', $c)))
                ->with('classes')
                ->orderBy('name_bn')
                ->get();
            return response()->json(['status' => 200, 'message' => 'পাঠ্যক্রম তালিকা পাওয়া গেছে', 'data' => $subjects], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'পাঠ্যক্রম লোড করতে সমস্যা: ' . $e->getMessage(), 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function bookList(Request $request)
    {
        try {
            $tenantId = $request->user()?->tenant_id ?? auth()->user()?->tenant_id;
            $subjects = AcademicSubject::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->when($request->class_id, fn($q, $c) => $q->where('class_id', $c)->orWhereHas('classes', fn($qc) => $qc->where('academic_classes.id', $c)))
                ->with('classes')
                ->orderBy('name_bn')
                ->get();
            return response()->json(['status' => 200, 'message' => 'বই তালিকা পাওয়া গেছে', 'data' => $subjects], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'বই লোড করতে সমস্যা: ' . $e->getMessage(), 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }
}
