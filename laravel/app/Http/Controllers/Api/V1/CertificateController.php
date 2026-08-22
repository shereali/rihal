<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\CertificateTemplate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Http\Resources\ApiCollection;

class CertificateController extends Controller
{
    public function templates(Request $request)
    {
        $tenant = $request->get('tenant');
        $query = CertificateTemplate::where('tenant_id', $tenant?->id)
            ->orderBy('is_active', 'desc')
            ->orderBy('updated_at', 'desc');

        $per_page = min((int) $request->input('per_page', 10), 100);
        $items = $query->paginate($per_page);

        return ApiCollection::make($items, fn($t) => [
            'id' => $t->id,
            'name_bn' => $t->name_bn,
            'name_en' => $t->name_en,
            'type' => $t->type ?? 'general',
            'is_active' => (bool) ($t->is_active ?? true),
            'created_at' => $t->created_at?->format('d M, Y'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_bn' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);
        $tenant = $request->get('tenant');
        $data['tenant_id'] = $tenant?->id;
        if (!isset($data['is_active'])) $data['is_active'] = true;
        $template = CertificateTemplate::create($data);
        return ApiResource::make($template, fn($t) => [
            'id' => $t->id,
            'name_bn' => $t->name_bn,
            'name_en' => $t->name_en,
            'type' => $t->type ?? 'general',
            'is_active' => (bool) ($t->is_active ?? true),
            'created_at' => $t->created_at?->format('d M, Y'),
        ]);
    }

    public function update(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $template = CertificateTemplate::where('tenant_id', $tenant?->id)->findOrFail($id);
        $data = $request->validate([
            'name_bn' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);
        $template->update($data);
        return ApiResource::make($template->fresh(), fn($t) => [
            'id' => $t->id,
            'name_bn' => $t->name_bn,
            'name_en' => $t->name_en,
            'type' => $t->type ?? 'general',
            'is_active' => (bool) ($t->is_active ?? true),
            'updated_at' => $t->updated_at?->format('d M, Y h:i A'),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $template = CertificateTemplate::where('tenant_id', $tenant?->id)->findOrFail($id);
        $template->delete();
        return response()->json(['message' => 'টেমপলেট মুছে ফেলা হয়েছে।'], 200);
    }
}
