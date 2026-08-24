<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\AttendanceDevice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;

class DigitalAttendanceController extends Controller
{
    public function devices(Request $request)
    {
        try {
            $devices = AttendanceDevice::when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            })
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

            return response()->json([
                'status'  => 200,
                'message' => 'ডিভাইস তালিকা পাওয়া গেছে',
                'data'    => $devices,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'ডিভাইস তালিকা লোড করতে সমস্যা হয়েছে: ' . $e->getMessage(),
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'             => 'required|string|max:100',
                'serial_number'    => 'required|string|max:50|unique:attendance_devices,serial_number,NULL,id,tenant_id,' . tenant('id'),
                'device_type'      => 'required|in:biometric,rfid,scanner,manual',
                'ip_address'       => 'nullable|ip',
                'port'             => 'nullable|integer',
                'api_key'          => 'nullable|string|max:255',
                'status'           => 'required|in:active,inactive,syncing,error',
                'location'         => 'nullable|string|max:200',
            ]);

            $device = AttendanceDevice::create(array_merge($validated, [
                'tenant_id' => tenant('id'),
            ]));

            return response()->json([
                'status'  => 201,
                'message' => 'ডিভাইস সফলভাবে তৈরি করা হয়েছে',
                'data'    => $device,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 422,
                'message' => 'বৈধতা ত্রুটি',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'ডিভাইস তৈরি করতে সমস্যা হয়েছে: ' . $e->getMessage(),
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $device = AttendanceDevice::findOrFail($id);
            return response()->json([
                'status'  => 200,
                'message' => 'ডিভাইসের তথ্য পাওয়া গেছে',
                'data'    => $device,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'ডিভাইস পাওয়া যায়নি',
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $device = AttendanceDevice::findOrFail($id);

            $validated = $request->validate([
                'name'          => 'sometimes|string|max:100',
                'serial_number' => 'sometimes|string|max:50',
                'device_type'   => 'sometimes|in:biometric,rfid,scanner,manual',
                'ip_address'    => 'nullable|ip',
                'port'          => 'nullable|integer',
                'api_key'       => 'nullable|string|max:255',
                'status'        => 'sometimes|in:active,inactive,syncing,error',
                'location'      => 'nullable|string|max:200',
            ]);

            $device->update($validated);

            return response()->json([
                'status'  => 200,
                'message' => 'ডিভাইস সফলভাবে আপডেট করা হয়েছে',
                'data'    => $device,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 422,
                'message' => 'বৈধতা ত্রুটি',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'ডিভাইস আপডেট করতে সমস্যা হয়েছে: ' . $e->getMessage(),
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $device = AttendanceDevice::findOrFail($id);
            $device->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'ডিভাইস সফলভাবে মুছে ফেলা হয়েছে',
                'data'    => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'ডিভাইস মুছে ফেলতে সমস্যা হয়েছে: ' . $e->getMessage(),
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
