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

class CertificateController extends Controller
{
    public function templates(Request $request)
    {
        try {
            $query = CertificateTemplate::with(['classRelation', 'subjectRelation'])
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
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'টেমপলেট লোড করতে সমস্যা: ' . $e->getMessage(), 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function storeTemplate(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'          => 'required|string|max:150',
                'template_type'  => 'required|in:annual,transfer,sanction,conduct,others',
                'template_data'  => 'nullable|array',
                'class_id'       => 'nullable|integer|exists:academic_classes,id',
                'subject_id'     => 'nullable|integer|exists:academic_subjects,id',
                'is_active'      => 'boolean',
            ]);
            $template = CertificateTemplate::create(array_merge($validated, [
                'tenant_id' => tenant('id'),
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

    public function issueList(Request $request)
    {
        try {
            $query = IssuedCertificate::with(['templateRelation', 'studentRelation', 'classRelation', 'subjectRelation'])
                ->when($request->student_id, fn($q, $s) => $q->where('student_id', $s))
                ->when($request->class_id, fn($q, $c) => $q->where('class_id', $c))
                ->when($request->search, fn($q, $s) => $q->whereHas('studentRelation', fn($sq) => $sq->where('name', 'like', "%{$s}%")))
                ->orderBy('issue_date', 'desc')
                ->paginate($request->per_page ?? 15);

            return response()->json([
                'status'  => 200,
                'message' => 'সার্টিফিকেট প্রকাশনা তালিকা পাওয়া গেছে',
                'data'    => $query,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'প্রকাশনা লোড করতে সমস্যা: ' . $e->getMessage(), 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function issueCertificate(Request $request)
    {
        try {
            $validated = $request->validate([
                'template_id'    => 'required|integer|exists:certificate_templates,id',
                'student_id'     => 'required|integer|exists:students,id,tenant_id,' . tenant('id'),
                'class_id'       => 'nullable|integer|exists:academic_classes,id',
                'subject_id'     => 'nullable|integer|exists:academic_subjects,id',
                'issue_date'     => 'required|date',
                'authorized_by'  => 'nullable|string|max:100',
                'remarks'        => 'nullable|string|max:300',
            ]);

            $cert = IssuedCertificate::create(array_merge($validated, [
                'tenant_id' => tenant('id'),
                'certificate_number' => 'CERT-' . strtoupper(tenant('slug') ?? 'RIHAL') . '-' . date('Y') . '-' . str_pad(IssuedCertificate::max('id') + 1, 4, '0', STR_PAD_LEFT),
            ]));

            return response()->json([
                'status'  => 201,
                'message' => 'সার্টিফিকেট সফলভাবে প্রকাশিত হয়েছে',
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
            $query = CertificateMark::with(['templateRelation', 'studentRelation', 'classRelation', 'subjectRelation'])
                ->when($request->template_id, fn($q, $t) => $q->where('template_id', $t))
                ->when($request->student_id, fn($q, $s) => $q->where('student_id', $s))
                ->when($request->class_id, fn($q, $c) => $q->where('class_id', $c))
                ->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 15);

            return response()->json(['status' => 200, 'message' => 'মার্ক তালিকা পাওয়া গেছে', 'data' => $query], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'মার্ক লোড করতে সমস্যা: ' . $e->getMessage(), 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function storeMark(Request $request)
    {
        try {
            $validated = $request->validate([
                'template_id'    => 'required|integer|exists:certificate_templates,id',
                'student_id'     => 'required|integer|exists:students,id,tenant_id,' . tenant('id'),
                'class_id'       => 'nullable|integer|exists:academic_classes,id',
                'subject_id'     => 'nullable|integer|exists:academic_subjects,id',
                'mark_obtained'  => 'required|integer|min:0',
                'mark_total'     => 'required|integer|min:0',
                'passing_mark'   => 'nullable|integer|min:0',
                'grade'          => 'nullable|string|max:10',
                'remark'         => 'nullable|string|max:200',
            ]);
            $mark = CertificateMark::create(array_merge($validated, ['tenant_id' => tenant('id')]));
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
            $subjects = AcademicSubject::when($request->class_id, fn($q, $c) => $q->whereHas('classes', fn($qc) => $qc->where('academic_classes.id', $c)))
                ->with('classes')
                ->orderBy('name')
                ->get();
            return response()->json(['status' => 200, 'message' => 'পাঠ্যক্রম তালিকা পাওয়া গেছে', 'data' => $subjects], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'পাঠ্যক্রম লোড করতে সমস্যা: ' . $e->getMessage(), 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function bookList(Request $request)
    {
        try {
            $subjects = AcademicSubject::with('books')
                ->when($request->class_id, fn($q, $c) => $q->whereHas('classes', fn($qc) => $qc->where('academic_classes.id', $c)))
                ->orderBy('name')
                ->get();
            return response()->json(['status' => 200, 'message' => 'বই তালিকা পাওয়া গেছে', 'data' => $subjects], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'বই লোড করতে সমস্যা: ' . $e->getMessage(), 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }
}
