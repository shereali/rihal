<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
}
