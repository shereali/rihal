<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Orphan;
use App\Models\OrphanSponsorship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrphanSponsorshipController extends ApiController
{
    public function index(Request $request, int $orphanId): JsonResponse
    {
        $orphan = $this->orphan($request, $orphanId);
        return $this->successResponse($orphan->sponsorships()->with('donor:id,name_bn,name_en,phone,email')->latest()->get());
    }

    public function store(Request $request, int $orphanId): JsonResponse
    {
        $orphan = $this->orphan($request, $orphanId);
        $validated = $request->validate([
            'donor_id' => ['required', 'integer', 'exists:donors,id'],
            'monthly_commitment' => ['required', 'numeric', 'min:0'],
            'share_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'notes' => ['nullable', 'string'],
        ]);

        abort_unless(
            \App\Models\Donor::where('tenant_id', $request->user()->tenant_id)->whereKey($validated['donor_id'])->exists(),
            422,
            'দাতা এই প্রতিষ্ঠানের নয়'
        );

        $sponsorship = $orphan->sponsorships()->create([
            'tenant_id' => $request->user()->tenant_id,
            ...$validated,
            'status' => 'active',
        ]);
        $orphan->update([
            'sponsorship_status' => 'sponsored',
            'monthly_amount' => $orphan->sponsorships()->where('status', 'active')->sum('monthly_commitment'),
        ]);

        return $this->successResponse($sponsorship->load('donor'), 'স্পন্সর যুক্ত হয়েছে', 201);
    }

    public function update(Request $request, int $orphanId, int $sponsorshipId): JsonResponse
    {
        $orphan = $this->orphan($request, $orphanId);
        $sponsorship = $orphan->sponsorships()->findOrFail($sponsorshipId);
        $sponsorship->update($request->validate([
            'monthly_commitment' => ['sometimes', 'numeric', 'min:0'],
            'share_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ends_at' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:active,paused,ended'],
            'notes' => ['nullable', 'string'],
        ]));
        $orphan->update([
            'monthly_amount' => $orphan->sponsorships()->where('status', 'active')->sum('monthly_commitment'),
            'sponsorship_status' => $orphan->sponsorships()->where('status', 'active')->exists() ? 'sponsored' : 'pending',
        ]);
        return $this->successResponse($sponsorship->fresh('donor'), 'স্পন্সরশিপ আপডেট হয়েছে');
    }

    public function destroy(Request $request, int $orphanId, int $sponsorshipId): JsonResponse
    {
        $orphan = $this->orphan($request, $orphanId);
        $orphan->sponsorships()->findOrFail($sponsorshipId)->update(['status' => 'ended', 'ends_at' => today()]);
        $orphan->update([
            'monthly_amount' => $orphan->sponsorships()->where('status', 'active')->sum('monthly_commitment'),
            'sponsorship_status' => $orphan->sponsorships()->where('status', 'active')->exists() ? 'sponsored' : 'pending',
        ]);
        return $this->successResponse(null, 'স্পন্সরশিপ সমাপ্ত হয়েছে');
    }

    private function orphan(Request $request, int $id): Orphan
    {
        return Orphan::where('tenant_id', $request->user()->tenant_id)->findOrFail($id);
    }
}
