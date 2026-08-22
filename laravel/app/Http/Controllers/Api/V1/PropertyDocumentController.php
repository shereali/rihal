<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\PropertyDocument;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use Illuminate\Support\Facades\Storage;

class PropertyDocumentController extends Controller
{
    public function index(Request $request, $propertyId)
    {
        $tenant = $request->get('tenant');
        $property = Property::where('tenant_id', $tenant?->id)->findOrFail($propertyId);

        $documents = PropertyDocument::where('property_id', $propertyId)
            ->orderByDesc('id')
            ->paginate((int) $request->query('per_page', 15));

        return ApiResource::collection($documents, function ($doc) {
            return [
                'id' => $doc->id,
                'property_id' => $doc->property_id,
                'document_type' => $doc->document_type,
                'document_name' => $doc->document_name,
                'file_url' => $doc->file_url,
                'file_size' => $doc->file_size,
                'mime_type' => $doc->mime_type,
                'uploaded_by' => $doc->uploaded_by,
                'uploaded_by_name' => $doc->uploaded_by_user?->name_bn ?? $doc->uploaded_by_user?->name ?? $doc->uploaded_by,
                'remarks' => $doc->remarks,
                'created_at' => $doc->created_at?->format('d M, Y h:i A'),
            ];
        });
    }

    public function store(Request $request, $propertyId)
    {
        $tenant = $request->get('tenant');
        $property = Property::where('tenant_id', $tenant?->id)->findOrFail($propertyId);

        $validated = $request->validate([
            'document_type' => 'required|string|max:50',
            'document_name' => 'nullable|string|max:255',
            'file' => 'required|file|max:5120', // 5MB
            'remarks' => 'nullable|string|max:500',
        ]);

        $file = $request->file('file');
        $fileName = 'properties/' . $propertyId . '/' . time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('public', $fileName);

        $document = PropertyDocument::create([
            'property_id' => $propertyId,
            'tenant_id' => $tenant?->id,
            'document_type' => $validated['document_type'],
            'document_name' => $validated['document_name'] ?? $file->getClientOriginalName(),
            'file_url' => Storage::url($path),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => $request->user()?->id,
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return ApiResource::success([
            'message' => 'নথি আপলোড সফল হয়েছে।',
            'data' => [
                'id' => $document->id,
                'document_type' => $document->document_type,
                'document_name' => $document->document_name,
                'file_url' => $document->file_url,
                'file_size' => $document->file_size,
                'mime_type' => $document->mime_type,
                'remarks' => $document->remarks,
                'created_at' => $document->created_at?->format('d M, Y h:i A'),
            ],
        ], 201);
    }

    public function show(Request $request, $propertyId, $id)
    {
        $tenant = $request->get('tenant');
        $property = Property::where('tenant_id', $tenant?->id)->findOrFail($propertyId);

        $document = PropertyDocument::where('property_id', $propertyId)
            ->where('tenant_id', $tenant?->id)
            ->findOrFail($id);

        return ApiResource::success([
            'id' => $document->id,
            'property_id' => $document->property_id,
            'document_type' => $document->document_type,
            'document_name' => $document->document_name,
            'file_url' => $document->file_url,
            'file_size' => $document->file_size,
            'mime_type' => $document->mime_type,
            'uploaded_by' => $document->uploaded_by,
            'uploaded_by_name' => $document->uploaded_by_user?->name_bn ?? $document->uploaded_by_user?->name ?? $document->uploaded_by,
            'remarks' => $document->remarks,
            'created_at' => $document->created_at?->format('d M, Y h:i A'),
        ]);
    }

    public function destroy(Request $request, $propertyId, $id)
    {
        $tenant = $request->get('tenant');
        $property = Property::where('tenant_id', $tenant?->id)->findOrFail($propertyId);

        $document = PropertyDocument::where('property_id', $propertyId)
            ->where('tenant_id', $tenant?->id)
            ->findOrFail($id);

        // Delete file if exists
        if ($document->file_url && Storage::exists(str_replace('/storage/', 'public/', $document->file_url))) {
            Storage::delete(str_replace('/storage/', 'public/', $document->file_url));
        }

        $document->delete();

        return ApiResource::success(['message' => 'নথি ডিলিট হয়েছে।']);
    }
}