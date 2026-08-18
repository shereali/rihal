<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = AttendanceRecord::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->whereHas('student', fn($s) => $s->whereHas('user', fn($u) => $u->where('name_bn', 'like', "%{$search}%")))
                    ->orWhereHas('teacher', fn($t) => $t->whereHas('user', fn($u) => $u->where('name_bn', 'like', "%{$search}%")));
            })
            ->when($request->has('student_id'), fn($q) => $q->where('student_id', $request->input('student_id')))
            ->when($request->has('teacher_id'), fn($q) => $q->where('teacher_id', $request->input('teacher_id')))
            ->when($request->has('date'), fn($q) => $q->where('date', $request->input('date')))
            ->when($request->has('from_date'), fn($q) => $q->where('date', '>=', $request->input('from_date')))
            ->when($request->has('to_date'), fn($q) => $q->where('date', '<=', $request->input('to_date')))
            ->when($request->has('is_present'), fn($q) => $q->where('is_present', filter_var($request->input('is_present'), FILTER_VALIDATE_BOOLEAN)))
            ->when($request->has('is_late'), fn($q) => $q->where('is_late', filter_var($request->input('is_late'), FILTER_VALIDATE_BOOLEAN)))
            ->with('student.user')
            ->with('teacher.user')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        $records = $query->paginate($perPage);

        return $this->successResponse($records);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $record = AttendanceRecord::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->with('student.user')
            ->with('teacher.user')
            ->first();

        if (!$record) {
            return $this->errorResponse('উপস্থিতি রেকর্ড পাওয়া যায়নি', 404);
        }

        return $this->successResponse($record);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'nullable|integer|exists:users,id',
            'teacher_id' => 'nullable|integer|exists:users,id',
            'device_id' => 'nullable|integer|exists:attendance_devices,id',
            'date' => 'required|date',
            'check_in_time' => 'nullable|date',
            'check_out_time' => 'nullable|date',
            'is_present' => 'nullable|boolean',
            'is_late' => 'nullable|boolean',
            'is_half_day' => 'nullable|boolean',
            'parent_notified' => 'nullable|boolean',
            'device_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['is_present'] = $data['is_present'] ?? true;

        $record = AttendanceRecord::create($data);

        $record->load('student.user');

        return $this->successResponse($record, 'উপস্থিতি রেকর্ড তৈরি সফল', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $record = AttendanceRecord::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$record) {
            return $this->errorResponse('উপস্থিতি রেকর্ড পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'nullable|date',
            'check_in_time' => 'nullable|date',
            'check_out_time' => 'nullable|date',
            'is_present' => 'nullable|boolean',
            'is_late' => 'nullable|boolean',
            'is_half_day' => 'nullable|boolean',
            'parent_notified' => 'nullable|boolean',
            'device_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $record->update($validator->validated());

        $record->load('student.user');

        return $this->successResponse($record->fresh(), 'উপস্থিতি রেকর্ড আপডেট সফল');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $record = AttendanceRecord::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$record) {
            return $this->errorResponse('উপস্থিতি রেকর্ড পাওয়া যায়নি', 404);
        }

        $record->delete();

        return $this->successResponse(null, 'উপস্থিতি রেকর্ড মুছে ফেলা সফল');
    }
}
