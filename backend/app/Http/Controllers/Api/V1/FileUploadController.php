<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Orphan;

class FileUploadController extends ApiController
{
    public function photo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $tenantId = $request->user()->tenant_id;
        $path = $validated['file']->store("tenants/{$tenantId}/photos", 'public');

        return $this->successResponse([
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'mime_type' => $validated['file']->getMimeType(),
            'size' => $validated['file']->getSize(),
        ], 'ছবি আপলোড সফল', 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $validated = $request->validate([
            'path' => [
                'required',
                'string',
                "starts_with:tenants/{$tenantId}/photos/",
                'regex:/^tenants\/\d+\/photos\/[A-Za-z0-9._-]+$/',
            ],
        ]);
        $path = $validated['path'];
        $url = Storage::disk('public')->url($path);
        $referenced = Orphan::where('tenant_id', $tenantId)
            ->where(fn ($query) => $query->where('photo_url', $path)
                ->orWhere('photo_url', $url)
                ->orWhere('photo_url', 'like', '%/storage/'.$path))
            ->exists();
        if ($referenced) {
            return $this->errorResponse('ব্যবহৃত ছবি মুছে ফেলা যাবে না', 409);
        }

        Storage::disk('public')->delete($path);
        return $this->successResponse(null, 'ছবি মুছে ফেলা হয়েছে');
    }
}
