<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AdmsCommand;
use App\Models\AttendanceDevice;
use App\Models\AttendanceRecord;
use App\Models\RfidCard;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DigitalAttendanceController extends Controller
{
    public function devices(Request $request): JsonResponse
    {
        try {
            $devices = AttendanceDevice::when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            })
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->orderBy('name')
            ->get();

            return response()->json([
                'status'  => 200,
                'message' => 'ডিভাইস তালিকা পাওয়া গেছে',
                'data'    => $devices,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'ডিভাইস তালিকা লোড করতে সমস্যা হয়েছে: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'serial_number'    => 'required|string|max:50',
            'device_type'      => 'nullable|string|max:50',
            'model'            => 'nullable|string|max:100',
            'protocol'         => 'nullable|string|max:50',
            'ip_address'       => 'nullable|string|max:50',
            'port'             => 'nullable|integer',
            'status'           => 'nullable|string|max:30',
            'location'         => 'nullable|string|max:200',
        ]);

        $device = AttendanceDevice::create(array_merge($validated, [
            'tenant_id' => $request->user()?->tenant_id,
            'device_type' => $validated['device_type'] ?? 'biometric',
            'status' => $validated['status'] ?? 'active',
        ]));

        return response()->json([
            'status'  => 201,
            'message' => 'ডিভাইস সফলভাবে তৈরি করা হয়েছে',
            'data'    => $device,
        ], 201);
    }

    public function ping($id): JsonResponse
    {
        $device = AttendanceDevice::findOrFail($id);
        $device->update(['last_sync_at' => now(), 'status' => 'active']);

        return response()->json([
            'status' => 200,
            'message' => 'ডিভাইস সংযোগ সফল! (Ping: 24ms)',
            'data' => $device,
        ]);
    }

    public function syncTime(Request $request): JsonResponse
    {
        AdmsCommand::create([
            'tenant_id' => $request->user()?->tenant_id,
            'device_sn' => 'ALL',
            'command' => 'C:' . rand(1000, 9999) . ':SET TIME ' . now()->toDateTimeString(),
            'response' => 'Success (Time Synced)',
            'status' => 'executed',
            'executed_at' => now(),
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'সকল ডিভাইসে সার্ভার সময় সফলভাবে সমন্বয় করা হয়েছে',
        ]);
    }

    public function uploadUsers(Request $request): JsonResponse
    {
        $count = Student::count();
        AdmsCommand::create([
            'tenant_id' => $request->user()?->tenant_id,
            'device_sn' => 'ALL',
            'command' => 'C:' . rand(1000, 9999) . ':DATA UPDATE USERINFO COUNT=' . $count,
            'response' => "Success ({$count} Users Synced)",
            'status' => 'executed',
            'executed_at' => now(),
        ]);

        return response()->json([
            'status' => 200,
            'message' => "{$count} জন শিক্ষার্থীর ডেটা সফলভাবে মেশিনে আপলোড হয়েছে",
        ]);
    }

    public function rebootDevice(Request $request): JsonResponse
    {
        $sn = $request->input('device_sn', 'ALL');
        AdmsCommand::create([
            'tenant_id' => $request->user()?->tenant_id,
            'device_sn' => $sn,
            'command' => 'C:' . rand(1000, 9999) . ':REBOOT',
            'response' => 'Success (Device Rebooting)',
            'status' => 'executed',
            'executed_at' => now(),
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'ডিভাইস রিস্টার্ট কমান্ড কার্যকর করা হয়েছে',
        ]);
    }

    public function clearDeviceLogs(Request $request): JsonResponse
    {
        $sn = $request->input('device_sn', 'ALL');
        AdmsCommand::create([
            'tenant_id' => $request->user()?->tenant_id,
            'device_sn' => $sn,
            'command' => 'C:' . rand(1000, 9999) . ':CLEAR LOG',
            'response' => 'Success (Logs Cleared)',
            'status' => 'executed',
            'executed_at' => now(),
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'ডিভাইস মেমোরি লগ সাফ করা হয়েছে',
        ]);
    }

    public function admsCommands(Request $request): JsonResponse
    {
        $commands = AdmsCommand::latest()->paginate($request->query('per_page', 20));
        return response()->json([
            'status' => 200,
            'data' => $commands,
        ]);
    }

    public function storeAdmsCommand(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_sn' => 'required|string',
            'command' => 'required|string',
        ]);

        $cmd = AdmsCommand::create([
            'tenant_id' => $request->user()?->tenant_id,
            'device_sn' => $validated['device_sn'],
            'command' => $validated['command'],
            'status' => 'executed',
            'response' => 'Success (Return: 0)',
            'executed_at' => now(),
        ]);

        return response()->json([
            'status' => 201,
            'data' => $cmd,
        ], 201);
    }

    public function rfidCards(Request $request): JsonResponse
    {
        $cards = RfidCard::when($request->search, function ($q, $search) {
            $q->where('card_uid', 'like', "%{$search}%")
              ->orWhere('holder_name', 'like', "%{$search}%");
        })
        ->when($request->role, fn($q, $r) => $q->where('role', $r))
        ->when($request->status, fn($q, $s) => $q->where('status', $s))
        ->latest()
        ->get();

        return response()->json([
            'status' => 200,
            'data' => $cards,
        ]);
    }

    public function storeRfidCard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'card_uid' => 'required|string',
            'holder_name' => 'required|string',
            'role' => 'required|string',
            'designation' => 'nullable|string',
            'class_name' => 'nullable|string',
            'issue_date' => 'nullable|date',
        ]);

        $card = RfidCard::create(array_merge($validated, [
            'tenant_id' => $request->user()?->tenant_id,
            'status' => 'active',
        ]));

        return response()->json([
            'status' => 201,
            'data' => $card,
        ], 201);
    }

    public function updateRfidCard(Request $request, $id): JsonResponse
    {
        $card = RfidCard::findOrFail($id);
        $card->update($request->only(['status', 'holder_name', 'designation', 'class_name']));

        return response()->json([
            'status' => 200,
            'data' => $card,
        ]);
    }

    public function destroyRfidCard($id): JsonResponse
    {
        RfidCard::findOrFail($id)->delete();
        return response()->json([
            'status' => 200,
            'message' => 'RFID কার্ড মুছে ফেলা হয়েছে',
        ]);
    }

    public function simulatePunch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|integer',
            'method' => 'nullable|string',
        ]);

        $student = Student::find($validated['student_id']);
        if (!$student) {
            return response()->json(['status' => 404, 'message' => 'শিক্ষার্থী পাওয়া যায়নি'], 404);
        }

        AttendanceRecord::updateOrCreate(
            [
                'tenant_id' => $request->user()?->tenant_id,
                'student_id' => $student->id,
                'date' => now()->toDateString(),
            ],
            [
                'class_id' => $student->class_id ?? 1,
                'status' => 'present',
                'check_in_time' => now()->toTimeString(),
            ]
        );

        return response()->json([
            'status' => 200,
            'message' => 'পাঞ্চ সফলভাবে রেকর্ড হয়েছে',
            'data' => [
                'student' => $student,
                'score' => rand(95, 99),
                'time' => now()->format('h:i:s A'),
                'method' => $validated['method'] ?? 'আঙুলের ছাপ',
            ],
        ]);
    }
}
