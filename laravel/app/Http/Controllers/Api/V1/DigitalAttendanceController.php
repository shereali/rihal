<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\DigitalAttendanceDevice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Http\Resources\ApiCollection;

class DigitalAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $tenant = $request->get('tenant');
        $query = DigitalAttendanceDevice::where('tenant_id', $tenant?->id)
            ->orderBy('updated_at', 'desc');

        $per_page = min((int) $request->input('per_page', 15), 100);
        $items = $query->paginate($per_page);

        return ApiCollection::make($items, fn($d) => [
            'id' => $d->id,
            'device_name' => $d->device_name,
            'device_type' => $d->device_type,
            'ip_address' => $d->ip_address,
            'port' => $d->port,
            'is_active' => (bool) ($d->is_active ?? true),
            'installed_at' => $d->installed_at?->format('d M, Y'),
            'last_synced_at' => ($d->last_synced_at) ? $d->last_synced_at->format('d M, Y h:i A') : null,
            'created_at' => $d->created_at?->format('d M, Y'),
        ]);
    }

    public function show(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $device = DigitalAttendanceDevice::where('tenant_id', $tenant?->id)->findOrFail($id);
        return ApiResource::make($device, fn($d) => [
            'id' => $d->id,
            'device_name' => $d->device_name,
            'device_type' => $d->device_type,
            'ip_address' => $d->ip_address,
            'port' => $d->port,
            'is_active' => (bool) ($d->is_active ?? true),
            'installed_at' => $d->installed_at?->format('d M, Y'),
            'last_synced_at' => ($d->last_synced_at) ? $d->last_synced_at->format('d M, Y h:i A') : null,
            'created_at' => $d->created_at?->format('d M, Y'),
            'updated_at' => $d->updated_at?->format('d M, Y h:i A'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'device_name' => 'required|string|max:255',
            'device_type' => 'required|string|in:bio,gateway,software',
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'is_active' => 'nullable|boolean',
        ]);
        $data['tenant_id'] = $request->get('tenant')?->id;
        if (!isset($data['is_active'])) $data['is_active'] = true;
        $device = DigitalAttendanceDevice::create($data);
        return ApiResource::make($device, fn($d) => [
            'id' => $d->id,
            'device_name' => $d->device_name,
            'device_type' => $d->device_type,
            'ip_address' => $d->ip_address,
            'port' => $d->port,
            'is_active' => (bool) ($d->is_active ?? true),
            'created_at' => $d->created_at?->format('d M, Y'),
        ]);
    }

    public function update(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $device = DigitalAttendanceDevice::where('tenant_id', $tenant?->id)->findOrFail($id);
        $data = $request->validate([
            'device_name' => 'nullable|string|max:255',
            'device_type' => 'nullable|string|in:bio,gateway,software',
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'is_active' => 'nullable|boolean',
        ]);
        $device->update($data);
        return ApiResource::make($device->fresh(), fn($d) => [
            'id' => $d->id,
            'device_name' => $d->device_name,
            'device_type' => $d->device_type,
            'ip_address' => $d->ip_address,
            'port' => $d->port,
            'is_active' => (bool) ($d->is_active ?? true),
            'updated_at' => $d->updated_at?->format('d M, Y h:i A'),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $device = DigitalAttendanceDevice::where('tenant_id', $tenant?->id)->findOrFail($id);
        $device->delete();
        return response()->json(['message' => 'ডিভাইস মুছে ফেলা হয়েছে।'], 200);
    }
}
