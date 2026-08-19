<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicSubject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AcademicController extends ApiController
{
    public function classes(Request $request): JsonResponse
    {
        $user = $request->user();
        $classes = AcademicClass::where('tenant_id', $user->tenant_id)
            ->orderBy('grade_level')
            ->get(['id', 'name_bn', 'name_en', 'class_type', 'grade_level']);

        return $this->successResponse($classes, 'শ্রেণির তালিকা');
    }

    public function sections(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = AcademicSection::where('tenant_id', $user->tenant_id);

        if ($request->has('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        $sections = $query->orderBy('name_bn')->get(['id', 'class_id', 'name_bn', 'name_en']);

        return $this->successResponse($sections, 'সেকশনের তালিকা');
    }

    public function subjects(Request $request): JsonResponse
    {
        $user = $request->user();
        $subjects = AcademicSubject::where('tenant_id', $user->tenant_id)
            ->orderBy('name_bn')
            ->get(['id', 'name_bn', 'name_en', 'subject_type']);

        return $this->successResponse($subjects, 'বিষয়ের তালিকা');
    }

    public function storeClass(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), ['name_bn'=>'required|string|max:255','name_en'=>'nullable|string|max:255','class_type'=>'nullable|string|max:50','grade_level'=>'nullable|integer','room_name'=>'nullable|string|max:100']);
        if ($v->fails()) return $this->errorResponse('বৈধতা ত্রুটি', 422, $v->errors());
        $data = $v->validated(); $data['tenant_id'] = $request->user()->tenant_id;
        return $this->successResponse(AcademicClass::create($data), 'শ্রেণি তৈরি সফল', 201);
    }

    public function updateClass(Request $request, int $id): JsonResponse
    {
        $class = AcademicClass::where('tenant_id', $request->user()->tenant_id)->find($id);
        if (!$class) return $this->errorResponse('শ্রেণি পাওয়া যায়নি', 404);
        $v = Validator::make($request->all(), ['name_bn'=>'nullable|string|max:255','name_en'=>'nullable|string|max:255','class_type'=>'nullable|string|max:50','grade_level'=>'nullable|integer','room_name'=>'nullable|string|max:100']);
        if ($v->fails()) return $this->errorResponse('বৈধতা ত্রুটি', 422, $v->errors());
        $class->update($v->validated()); return $this->successResponse($class->fresh(), 'শ্রেণি আপডেট সফল');
    }

    public function destroyClass(Request $request, int $id): JsonResponse
    {
        $class = AcademicClass::where('tenant_id', $request->user()->tenant_id)->find($id);
        if (!$class) return $this->errorResponse('শ্রেণি পাওয়া যায়নি', 404);
        $class->delete(); return $this->successResponse(null, 'শ্রেণি মুছে ফেলা সফল');
    }

    public function storeSection(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), ['class_id'=>'nullable|integer|exists:academic_classes,id','name_bn'=>'required|string|max:255','name_en'=>'nullable|string|max:255','section_type'=>'nullable|string|max:50','room_name'=>'nullable|string|max:100']);
        if ($v->fails()) return $this->errorResponse('বৈধতা ত্রুটি', 422, $v->errors());
        $data = $v->validated(); $data['tenant_id'] = $request->user()->tenant_id;
        return $this->successResponse(AcademicSection::create($data), 'সেকশন তৈরি সফল', 201);
    }

    public function updateSection(Request $request, int $id): JsonResponse
    {
        $section = AcademicSection::where('tenant_id', $request->user()->tenant_id)->find($id);
        if (!$section) return $this->errorResponse('সেকশন পাওয়া যায়নি', 404);
        $v = Validator::make($request->all(), ['class_id'=>'nullable|integer|exists:academic_classes,id','name_bn'=>'nullable|string|max:255','name_en'=>'nullable|string|max:255','section_type'=>'nullable|string|max:50','room_name'=>'nullable|string|max:100']);
        if ($v->fails()) return $this->errorResponse('বৈধতা ত্রুটি', 422, $v->errors());
        $section->update($v->validated()); return $this->successResponse($section->fresh(), 'সেকশন আপডেট সফল');
    }

    public function destroySection(Request $request, int $id): JsonResponse
    {
        $section = AcademicSection::where('tenant_id', $request->user()->tenant_id)->find($id);
        if (!$section) return $this->errorResponse('সেকশন পাওয়া যায়নি', 404);
        $section->delete(); return $this->successResponse(null, 'সেকশন মুছে ফেলা সফল');
    }

    public function storeSubject(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), ['name_bn'=>'required|string|max:255','name_en'=>'nullable|string|max:255','code'=>'nullable|string|max:50','subject_type'=>'nullable|string|max:50','education_board'=>'nullable|string|max:100','teaching_hours_per_week'=>'nullable|integer','credit_hours'=>'nullable|integer']);
        if ($v->fails()) return $this->errorResponse('বৈধতা ত্রুটি', 422, $v->errors());
        $data = $v->validated(); $data['tenant_id'] = $request->user()->tenant_id;
        return $this->successResponse(AcademicSubject::create($data), 'বিষয় তৈরি সফল', 201);
    }

    public function updateSubject(Request $request, int $id): JsonResponse
    {
        $subject = AcademicSubject::where('tenant_id', $request->user()->tenant_id)->find($id);
        if (!$subject) return $this->errorResponse('বিষয় পাওয়া যায়নি', 404);
        $v = Validator::make($request->all(), ['name_bn'=>'nullable|string|max:255','name_en'=>'nullable|string|max:255','code'=>'nullable|string|max:50','subject_type'=>'nullable|string|max:50','education_board'=>'nullable|string|max:100','teaching_hours_per_week'=>'nullable|integer','credit_hours'=>'nullable|integer']);
        if ($v->fails()) return $this->errorResponse('বৈধতা ত্রুটি', 422, $v->errors());
        $subject->update($v->validated()); return $this->successResponse($subject->fresh(), 'বিষয় আপডেট সফল');
    }

    public function destroySubject(Request $request, int $id): JsonResponse
    {
        $subject = AcademicSubject::where('tenant_id', $request->user()->tenant_id)->find($id);
        if (!$subject) return $this->errorResponse('বিষয় পাওয়া যায়নি', 404);
        $subject->delete(); return $this->successResponse(null, 'বিষয় মুছে ফেলা সফল');
    }
}