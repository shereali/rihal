<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\AcademicTimetable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TimetableController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $q = AcademicTimetable::where('tenant_id', $request->user()->tenant_id)
            ->with(['class:id,name_bn', 'subject:id,name_bn', 'teacher:id,name_bn,name_en'])
            ->when($request->filled('class_id'), fn($x) => $x->where('class_id', $request->class_id))
            ->when($request->filled('day_of_week'), fn($x) => $x->where('day_of_week', $request->day_of_week))
            ->orderByRaw("FIELD(day_of_week, 'শনিবার','রবিবার','সোমবার','মঙ্গলবার','বুধবার','বৃহস্পতিবার','শুক্রবার')")
            ->orderBy('start_time');
        return $this->successResponse($q->get());
    }

    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), ['class_id'=>'nullable|integer|exists:academic_classes,id','section_id'=>'nullable|integer|exists:academic_sections,id','subject_id'=>'nullable|integer|exists:academic_subjects,id','teacher_id'=>'nullable|integer|exists:users,id','day_of_week'=>'required|string|max:30','start_time'=>'required|date_format:H:i','end_time'=>'required|date_format:H:i','is_active'=>'nullable|boolean']);
        if ($v->fails()) return $this->errorResponse('বৈধতা ত্রুটি', 422, $v->errors());
        $data = $v->validated(); $data['tenant_id'] = $request->user()->tenant_id; $data['is_active'] = $data['is_active'] ?? true;
        return $this->successResponse(AcademicTimetable::create($data), 'রুটিন তৈরি সফল', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = AcademicTimetable::where('tenant_id', $request->user()->tenant_id)->find($id);
        if (!$row) return $this->errorResponse('রুটিন পাওয়া যায়নি', 404);
        $v = Validator::make($request->all(), ['class_id'=>'nullable|integer|exists:academic_classes,id','section_id'=>'nullable|integer|exists:academic_sections,id','subject_id'=>'nullable|integer|exists:academic_subjects,id','teacher_id'=>'nullable|integer|exists:users,id','day_of_week'=>'nullable|string|max:30','start_time'=>'nullable|date_format:H:i','end_time'=>'nullable|date_format:H:i','is_active'=>'nullable|boolean']);
        if ($v->fails()) return $this->errorResponse('বৈধতা ত্রুটি', 422, $v->errors());
        $row->update($v->validated()); return $this->successResponse($row->fresh(), 'রুটিন আপডেট সফল');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $row = AcademicTimetable::where('tenant_id', $request->user()->tenant_id)->find($id);
        if (!$row) return $this->errorResponse('রুটিন পাওয়া যায়নি', 404);
        $row->delete(); return $this->successResponse(null, 'রুটিন মুছে ফেলা সফল');
    }
}
