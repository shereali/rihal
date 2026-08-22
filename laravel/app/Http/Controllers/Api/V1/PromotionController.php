<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Promotion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Http\Resources\ApiCollection;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $tenant = $request->get('tenant');
        $query = Promotion::where('tenant_id', $tenant?->id)
            ->with('student', 'toClass', 'session')
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('session_id'), fn($q) => $q->where('session_id', $request->session_id))
            ->when($request->filled('student_number_contains'), fn($q) => $q->where('student_number', 'like', '%' . $request->student_number_contains . '%'))
            ->orderBy('academic_year', 'desc')
            ->orderBy('status');

        $per_page = min((int) $request->input('per_page', 15), 100);
        $items = $query->paginate($per_page);

        return ApiCollection::make($items, fn($p) => [
            'id' => $p->id,
            'student_id' => $p->student_id,
            'student_number' => $p->student_number,
            'student_name_bn' => $p->student?->name_bn ?? '',
            'student_name' => $p->student?->name ?? '',
            'to_class_id' => $p->to_class_id,
            'to_class_name' => $p->toClass?->name_bn ?? '',
            'to_class_name_en' => $p->toClass?->name_en ?? '',
            'session_id' => $p->session_id,
            'session_name' => $p->session?->name_bn ?? '',
            'session_name_en' => $p->session?->name_en ?? '',
            'academic_year' => $p->academic_year,
            'status' => $p->status,
            'approved_by' => $p->approved_by_user?->name_bn ?? ($p->approved_by_user?->name ?? ''),
            'approved_at' => $p->approved_at?->format('d M, Y h:i A'),
            'notes' => $p->notes,
            'created_at' => $p->created_at?->format('d M, Y h:i A'),
        ]);
    }

    public function show(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $promotion = Promotion::where('tenant_id', $tenant?->id)->with('student', 'toClass', 'session')->findOrFail($id);
        return ApiResource::make($promotion, fn($p) => [
            'id' => $p->id,
            'student_id' => $p->student_id,
            'student_number' => $p->student_number,
            'student_name_bn' => $p->student?->name_bn ?? '',
            'student_name' => $p->student?->name ?? '',
            'to_class_id' => $p->to_class_id,
            'to_class_name' => $p->toClass?->name_bn ?? '',
            'to_class_name_en' => $p->toClass?->name_en ?? '',
            'session_id' => $p->session_id,
            'session_name' => $p->session?->name_bn ?? '',
            'session_name_en' => $p->session?->name_en ?? '',
            'academic_year' => $p->academic_year,
            'status' => $p->status,
            'approved_by' => $p->approved_by_user?->name_bn ?? ($p->approved_by_user?->name ?? ''),
            'approved_at' => $p->approved_at?->format('d M, Y h:i A'),
            'notes' => $p->notes,
            'created_at' => $p->created_at?->format('d M, Y h:i A'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|integer',
            'to_class_id' => 'required|integer',
            'session_id' => 'required|integer',
            'academic_year' => 'required|string|max:10',
            'notes' => 'nullable|string',
        ]);
        $tenant = $request->get('tenant');
        $data['tenant_id'] = $tenant?->id;
        $data['status'] = 'pending';

        $promotion = Promotion::create($data);
        return ApiResource::make($promotion->load('student', 'toClass', 'session'), fn($p) => [
            'id' => $p->id,
            'student_id' => $p->student_id,
            'student_number' => $p->student_number,
            'student_name_bn' => $p->student?->name_bn ?? '',
            'student_name' => $p->student?->name ?? '',
            'to_class_id' => $p->to_class_id,
            'to_class_name' => $p->toClass?->name_bn ?? '',
            'session_id' => $p->session_id,
            'session_name' => $p->session?->name_bn ?? '',
            'academic_year' => $p->academic_year,
            'status' => $p->status,
            'created_at' => $p->created_at?->format('d M, Y h:i A'),
        ]);
    }

    public function approve(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $promotion = Promotion::where('tenant_id', $tenant?->id)->findOrFail($id);
        $validated = $request->validate([
            'approved_by' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);
        $promotion->update(array_merge($validated, [
            'status' => 'approved',
            'approved_by' => $request->user()?->id,
        ]));
        return ApiResource::make($promotion->fresh()->load('student', 'toClass', 'session'), fn($p) => [
            'id' => $p->id,
            'student_id' => $p->student_id,
            'student_number' => $p->student_number,
            'student_name_bn' => $p->student?->name_bn ?? '',
            'student_name' => $p->student?->name ?? '',
            'to_class_id' => $p->to_class_id,
            'to_class_name' => $p->toClass?->name_bn ?? '',
            'session_id' => $p->session_id,
            'session_name' => $p->session?->name_bn ?? '',
            'academic_year' => $p->academic_year,
            'status' => $p->status,
            'approved_by' => $p->approved_by_user?->name_bn ?? ($p->approved_by_user?->name ?? ''),
            'approved_at' => $p->approved_at?->format('d M, Y h:i A'),
            'notes' => $p->notes,
            'created_at' => $p->created_at?->format('d M, Y h:i A'),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $promotion = Promotion::where('tenant_id', $tenant?->id)->findOrFail($id);
        $promotion->delete();
        return response()->json(['message' => 'প্রমোশন রেকর্ড মুছে ফেলা হয়েছে।'], 200);
    }
}
