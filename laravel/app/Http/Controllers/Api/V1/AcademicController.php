<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicSubject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
