<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Orphan;
use App\Models\SponsorshipPayment;
use App\Models\Donor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrphanController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Orphan::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), fn($q) => $q->where('name_bn', 'like', '%' . $request->input('search') . '%'))
            ->when($request->has('status'), fn($q) => $q->where('sponsorship_status', $request->input('status')))
            ->when($request->input('is_sponsored') === 'true', fn($q) => $q->where('sponsorship_status', 'sponsored'))
            ->when($request->input('is_active') !== null, function($q) use ($request) {
                $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
            })
            ->with('sponsor:id,name_bn,name_en')
            ->orderBy('created_at', 'desc');

        $orphans = $query->paginate($perPage);

        return $this->successResponse($orphans);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name_bn' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'guardian_name_bn' => 'nullable|string|max:255',
            'guardian_name_en' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:30',
            'address_bn' => 'nullable|string',
            'address_en' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'admission_year' => 'nullable|string|max:10',
            'class_id' => 'nullable|string|max:50',
            'section_id' => 'nullable|string|max:50',
            'photo_url' => 'nullable|url',
            'story' => 'nullable|string',
            'monthly_amount' => 'nullable|numeric|min:0',
            'sponsor_id' => 'nullable|exists:donors,id',
            'sponsorship_start_date' => 'nullable|date',
            'sponsorship_end_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            'is_orphaned' => 'nullable|boolean',
            'is_needy' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['sponsorship_status'] = $data['sponsor_id'] ? 'sponsored' : 'pending';

        $orphan = Orphan::create($data);

        return $this->successResponse($orphan, 'অর্ফান তৈরি সফল', 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $orphan = Orphan::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $id)
            ->with(['sponsor:id,name_bn,name_en,phone,email', 'payments'])
            ->firstOrFail();

        return $this->successResponse($orphan);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $orphan = Orphan::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $id)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'name_bn' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'guardian_name_bn' => 'nullable|string|max:255',
            'guardian_name_en' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:30',
            'address_bn' => 'nullable|string',
            'address_en' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'admission_year' => 'nullable|string|max:10',
            'class_id' => 'nullable|string|max:50',
            'section_id' => 'nullable|string|max:50',
            'photo_url' => 'nullable|url',
            'story' => 'nullable|string',
            'monthly_amount' => 'nullable|numeric|min:0',
            'sponsor_id' => 'nullable|exists:donors,id',
            'sponsorship_start_date' => 'nullable|date',
            'sponsorship_end_date' => 'nullable|date',
            'sponsorship_status' => 'nullable|in:pending,sponsored,completed,closed',
            'is_active' => 'nullable|boolean',
            'is_orphaned' => 'nullable|boolean',
            'is_needy' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        if (array_key_exists('sponsor_id', $data) && $data['sponsor_id']) {
            $data['sponsorship_status'] = 'sponsored';
        }

        $orphan->update($data);

        return $this->successResponse($orphan->fresh(), 'অর্ফান আপডেট সফল');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $orphan = Orphan::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $id)
            ->firstOrFail();

        $orphan->delete();

        return $this->successResponse(null, 'অর্ফান মুছে ফেলা সফল');
    }

    public function recordPayment(Request $request, int $orphanId): JsonResponse
    {
        $orphan = Orphan::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $orphanId)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'purpose_bn' => 'nullable|string|max:255',
            'purpose_en' => 'nullable|string|max:255',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:100',
            'reference' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        DB::transaction(function () use ($orphan, $request, $validator) {
            $data = $validator->validated();
            $data['tenant_id'] = $request->user()->tenant_id;
            $data['orphan_id'] = $orphan->id;
            $data['sponsor_id'] = $orphan->sponsor_id;
            $data['payment_date'] = $data['payment_date'] ?? now()->toDateString();
            $data['collected_by_user_id'] = $request->user()->id;

            SponsorshipPayment::create($data);

            $orphan->total_sponsored = ($orphan->total_sponsored ?? 0) + $data['amount'];

            if ($orphan->monthly_amount > 0 && $orphan->total_sponsored >= $orphan->monthly_amount * 3) {
                $orphan->sponsorship_status = 'completed';
            }

            $orphan->save();
        });

        return $this->successResponse($orphan->fresh('payments'), 'স্পন্সরশিপ প্রদান সফল');
    }

    public function payments(Request $request, int $orphanId): JsonResponse
    {
        Orphan::where('tenant_id', $request->user()->tenant_id)
            ->where('id', $orphanId)
            ->firstOrFail();

        $payments = SponsorshipPayment::where('orphan_id', $orphanId)
            ->where('tenant_id', $request->user()->tenant_id)
            ->orderBy('payment_date', 'desc')
            ->get();

        return $this->successResponse($payments);
    }

    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $totalOrphans = Orphan::where('tenant_id', $user->tenant_id)->count();
        $totalSponsored = Orphan::where('tenant_id', $user->tenant_id)
            ->where('sponsorship_status', 'sponsored')
            ->count();
        $totalPending = Orphan::where('tenant_id', $user->tenant_id)
            ->where('sponsorship_status', 'pending')
            ->count();
        $totalAmount = Orphan::where('tenant_id', $user->tenant_id)->sum('total_sponsored');

        return $this->successResponse([
            'total_orphans' => $totalOrphans,
            'total_sponsored' => $totalSponsored,
            'total_pending' => $totalPending,
            'total_sponsored_amount' => $totalAmount,
        ]);
    }

    public function donors(Request $request): JsonResponse
    {
        $donors = Donor::where('tenant_id', $request->user()->tenant_id)
            ->select('id', 'name_bn', 'name_en', 'phone', 'email')
            ->orderBy('name_bn')
            ->get();

        return $this->successResponse($donors);
    }
}
