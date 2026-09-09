<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Role;
use App\Models\AcademicSession;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicSubject;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    // ─── Admin Users & Roles ──────────────────────────────────────────────────

    public function adminUsers(Request $request)
    {
        $tenant = $request->get('tenant');
        $query = User::where('tenant_id', $tenant?->id)
            ->with('roles', 'role')
            ->when($request->filled('role'), fn($q) => $q->whereHas('roles', fn($q2) => $q2->where('name', $request->role)))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->orderByDesc('id');

        $perPage = min((int) $request->query('per_page', 15), 100);
        $items = $query->paginate($perPage);

        return ApiResource::collection($items, function ($user) {
            return [
                'id' => $user->id,
                'name_bn' => $user->name_bn,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role?->name ?? 'রিধি নেই',
                'roles' => $user->whenLoaded('roles', fn() => $user->roles->pluck('name')->toArray()),
                'avatar_url' => $user->avatar_url,
                'status' => $user->status,
                'last_login' => $user->last_login?->format('d M, Y h:i A'),
                'created_at' => $user->created_at?->format('d M, Y'),
            ];
        });
    }

    public function storeAdminUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'name_bn' => 'nullable|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|in:admin,manager,accountant,teacher,guardian,student,superadmin',
            'password' => 'required|string|min:6',
            'status' => 'nullable|string|in:active,inactive,invited',
            'avatar_url' => 'nullable|string|max:500',
            'nid' => 'nullable|string|max:50',
        ]);

        $validated['tenant_id'] = $request->get('tenant')?->id;
        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = $request->status ?? 'active';

        $user = User::create($validated);

        // Assign role
        $role = Role::firstOrCreate(['name' => $validated['role']], ['display_name' => ucfirst($validated['role'])]);
        $user->roles()->attach($role->id);

        return ApiResource::success([
            'message' => 'ব্যবহারকারী তৈরি হয়েছে।',
            'data' => [
                'id' => $user->id,
                'name_bn' => $user->name_bn,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $role->name,
                'status' => $user->status,
                'created_at' => $user->created_at?->format('d M, Y'),
            ],
        ], 201);
    }

    public function showAdminUser(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $user = User::where('tenant_id', $tenant?->id)
            ->with('roles', 'role')
            ->findOrFail($id);

        return ApiResource::success([
            'id' => $user->id,
            'name_bn' => $user->name_bn,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role?->name ?? 'রিধি নেই',
            'roles' => $user->roles->pluck('name')->toArray(),
            'avatar_url' => $user->avatar_url,
            'status' => $user->status,
            'nid' => $user->nid,
            'last_login' => $user->last_login?->format('d M, Y h:i A'),
            'created_at' => $user->created_at?->format('d M, Y'),
            'updated_at' => $user->updated_at?->format('d M, Y'),
        ]);
    }

    public function updateAdminUser(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $user = User::where('tenant_id', $tenant?->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'name_bn' => 'nullable|string|max:100',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'role' => 'sometimes|string|in:admin,manager,accountant,teacher,guardian,student,superadmin',
            'password' => 'nullable|string|min:6',
            'status' => 'nullable|string|in:active,inactive,invited',
            'avatar_url' => 'nullable|string|max:500',
            'nid' => 'nullable|string|max:50',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        // Update role if changed
        if (isset($validated['role'])) {
            $role = Role::firstOrCreate(['name' => $validated['role']], ['display_name' => ucfirst($validated['role'])]);
            $user->roles()->sync([$role->id]);
        }

        $user->load('roles', 'role');

        return ApiResource::success([
            'message' => 'ব্যবহারকারী আপডেট হয়েছে।',
            'data' => [
                'id' => $user->id,
                'name_bn' => $user->name_bn,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role?->name ?? 'রিধি নেই',
                'roles' => $user->roles->pluck('name')->toArray(),
                'status' => $user->status,
                'updated_at' => $user->updated_at?->format('d M, Y'),
            ],
        ]);
    }

    public function destroyAdminUser(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $user = User::where('tenant_id', $tenant?->id)->findOrFail($id);
        $user->delete();

        return ApiResource::success(['message' => 'ব্যবহারকারী ডিলিট হয়েছে।']);
    }

    // ─── Academic Sessions ────────────────────────────────────────────────────

    public function sessions(Request $request)
    {
        $tenant = $request->get('tenant');
        $query = AcademicSession::where('tenant_id', $tenant?->id)
            ->orderByDesc('id');

        $perPage = min((int) $request->query('per_page', 15), 100);
        $items = $query->paginate($perPage);

        return ApiResource::collection($items, function ($s) {
            return [
                'id' => $s->id,
                'session_name' => $s->session_name,
                'session_bn' => $s->session_bn,
                'start_date' => $s->start_date?->format('d M, Y'),
                'end_date' => $s->end_date?->format('d M, Y'),
                'status' => $s->status,
                'is_active' => (bool) $s->is_active,
                'created_at' => $s->created_at?->format('d M, Y'),
            ];
        });
    }

    public function storeSession(Request $request)
    {
        $validated = $request->validate([
            'session_name' => 'required|string|max:50',
            'session_bn' => 'nullable|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'nullable|string|in:active,inactive,upcoming,completed',
        ]);

        $validated['tenant_id'] = $request->get('tenant')?->id;
        $validated['start_date'] = \Carbon\Carbon::parse($validated['start_date']);
        $validated['end_date'] = \Carbon\Carbon::parse($validated['end_date']);
        $validated['status'] = $request->status ?? 'active';

        $session = AcademicSession::create($validated);

        return ApiResource::success([
            'message' => 'সেশন তৈরি হয়েছে।',
            'data' => [
                'id' => $session->id,
                'session_name' => $session->session_name,
                'session_bn' => $session->session_bn,
                'start_date' => $session->start_date?->format('d M, Y'),
                'end_date' => $session->end_date?->format('d M, Y'),
                'status' => $session->status,
                'is_active' => (bool) $session->is_active,
                'created_at' => $session->created_at?->format('d M, Y'),
            ],
        ], 201);
    }

    public function showSession(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $session = AcademicSession::where('tenant_id', $tenant?->id)->findOrFail($id);

        return ApiResource::success([
            'id' => $session->id,
            'session_name' => $session->session_name,
            'session_bn' => $session->session_bn,
            'start_date' => $session->start_date?->format('d M, Y'),
            'end_date' => $session->end_date?->format('d M, Y'),
            'status' => $session->status,
            'is_active' => (bool) $session->is_active,
            'created_at' => $session->created_at?->format('d M, Y'),
            'updated_at' => $session->updated_at?->format('d M, Y'),
        ]);
    }

    public function updateSession(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $session = AcademicSession::where('tenant_id', $tenant?->id)->findOrFail($id);

        $validated = $request->validate([
            'session_name' => 'sometimes|string|max:50',
            'session_bn' => 'nullable|string|max:50',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'status' => 'nullable|string|in:active,inactive,upcoming,completed',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['start_date'])) {
            $validated['start_date'] = \Carbon\Carbon::parse($validated['start_date']);
        }
        if (isset($validated['end_date'])) {
            $validated['end_date'] = \Carbon\Carbon::parse($validated['end_date']);
        }

        $session->update($validated);

        return ApiResource::success([
            'message' => 'সেশন আপডেট হয়েছে।',
            'data' => [
                'id' => $session->id,
                'session_name' => $session->session_name,
                'session_bn' => $session->session_bn,
                'start_date' => $session->start_date?->format('d M, Y'),
                'end_date' => $session->end_date?->format('d M, Y'),
                'status' => $session->status,
                'is_active' => (bool) $session->is_active,
                'updated_at' => $session->updated_at?->format('d M, Y'),
            ],
        ]);
    }

    public function destroySession(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $session = AcademicSession::where('tenant_id', $tenant?->id)->findOrFail($id);
        $session->delete();

        return ApiResource::success(['message' => 'সেশন ডিলিট হয়েছে।']);
    }

    // ─── Classes ──────────────────────────────────────────────────────────────

    public function classes(Request $request)
    {
        $tenant = $request->get('tenant');
        $query = AcademicClass::where('tenant_id', $tenant?->id)
            ->with('sections')
            ->when($request->filled('academic_session_id'), fn($q) => $q->where('academic_session_id', $request->academic_session_id))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->orderBy('class_order', 'asc');

        $perPage = min((int) $request->query('per_page', 15), 100);
        $items = $query->paginate($perPage);

        return ApiResource::collection($items, function ($c) {
            return [
                'id' => $c->id,
                'class_name' => $c->class_name,
                'class_bn' => $c->class_bn ?? $c->class_name,
                'class_name_en' => $c->class_name_en,
                'class_order' => $c->class_order,
                'status' => $c->status,
                'academic_session_id' => $c->academic_session_id,
                'session' => $c->whenLoaded('session', fn() => [
                    'id' => $c->session?->id,
                    'session_name' => $c->session?->session_name,
                    'session_bn' => $c->session?->session_bn,
                ]),
                'sections_count' => $c->sections?->count() ?? 0,
                'sections' => $c->whenLoaded('sections', fn() => $c->sections->map(fn($s) => [
                    'id' => $s->id,
                    'section_name' => $s->section_name,
                    'section_bn' => $s->section_bn,
                ])),
                'created_at' => $c->created_at?->format('d M, Y'),
            ];
        });
    }

    public function storeClass(Request $request)
    {
        $validated = $request->validate([
            'class_name' => 'required|string|max:50',
            'class_bn' => 'nullable|string|max:50',
            'class_name_en' => 'nullable|string|max:50',
            'class_order' => 'nullable|integer|min:0',
            'academic_session_id' => 'nullable|integer|exists:academic_sessions,id',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $validated['tenant_id'] = $request->get('tenant')?->id;
        $validated['class_order'] = $request->class_order ?? AcademicClass::max('class_order') + 1;
        $validated['status'] = $request->status ?? 'active';

        $class = AcademicClass::create($validated);

        return ApiResource::success([
            'message' => 'শ্রেণি তৈরি হয়েছে।',
            'data' => [
                'id' => $class->id,
                'class_name' => $class->class_name,
                'class_bn' => $class->class_bn,
                'class_name_en' => $class->class_name_en,
                'class_order' => $class->class_order,
                'status' => $class->status,
                'academic_session_id' => $class->academic_session_id,
                'created_at' => $class->created_at?->format('d M, Y'),
            ],
        ], 201);
    }

    public function showClass(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $class = AcademicClass::where('tenant_id', $tenant?->id)
            ->with('sections', 'session')
            ->findOrFail($id);

        return ApiResource::success([
            'id' => $class->id,
            'class_name' => $class->class_name,
            'class_bn' => $class->class_bn,
            'class_name_en' => $class->class_name_en,
            'class_order' => $class->class_order,
            'status' => $class->status,
            'academic_session_id' => $class->academic_session_id,
            'session' => $class->session?->map(fn($s) => [
                'id' => $s->id,
                'session_name' => $s->session_name,
                'session_bn' => $s->session_bn,
            ]),
            'sections' => $class->sections->map(fn($s) => [
                'id' => $s->id,
                'section_name' => $s->section_name,
                'section_bn' => $s->section_bn,
            ]),
            'created_at' => $class->created_at?->format('d M, Y'),
            'updated_at' => $class->updated_at?->format('d M, Y'),
        ]);
    }

    public function updateClass(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $class = AcademicClass::where('tenant_id', $tenant?->id)->findOrFail($id);

        $validated = $request->validate([
            'class_name' => 'sometimes|string|max:50',
            'class_bn' => 'nullable|string|max:50',
            'class_name_en' => 'nullable|string|max:50',
            'class_order' => 'nullable|integer|min:0',
            'academic_session_id' => 'nullable|integer|exists:academic_sessions,id',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $class->update($validated);

        return ApiResource::success([
            'message' => 'শ্রেণি আপডেট হয়েছে।',
            'data' => [
                'id' => $class->id,
                'class_name' => $class->class_name,
                'class_bn' => $class->class_bn,
                'class_name_en' => $class->class_name_en,
                'class_order' => $class->class_order,
                'status' => $class->status,
                'updated_at' => $class->updated_at?->format('d M, Y'),
            ],
        ]);
    }

    public function destroyClass(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $class = AcademicClass::where('tenant_id', $tenant?->id)->findOrFail($id);
        $class->delete();

        return ApiResource::success(['message' => 'শ্রেণি ডিলিট হয়েছে।']);
    }

    // ─── Sections ──────────────────────────────────────────────────────────────

    public function sections(Request $request)
    {
        $tenant = $request->get('tenant');
        $query = AcademicSection::where('tenant_id', $tenant?->id)
            ->with('class')
            ->when($request->filled('class_id'), fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->filled('academic_session_id'), fn($q) => $q->where('academic_session_id', $request->academic_session_id))
            ->orderBy('section_order', 'asc');

        $perPage = min((int) $request->query('per_page', 15), 100);
        $items = $query->paginate($perPage);

        return ApiResource::collection($items, function ($s) {
            return [
                'id' => $s->id,
                'section_name' => $s->section_name,
                'section_bn' => $s->section_bn ?? $s->section_name,
                'class_id' => $s->class_id,
                'class' => $s->whenLoaded('class', fn() => [
                    'id' => $s->class?->id,
                    'class_name' => $s->class?->class_name,
                    'class_bn' => $s->class?->class_bn,
                ]),
                'academic_session_id' => $s->academic_session_id,
                'section_order' => $s->section_order,
                'status' => $s->status,
                'capacity' => $s->capacity,
                'created_at' => $s->created_at?->format('d M, Y'),
            ];
        });
    }

    public function storeSection(Request $request)
    {
        $validated = $request->validate([
            'section_name' => 'required|string|max:50',
            'section_bn' => 'nullable|string|max:50',
            'class_id' => 'required|integer|exists:academic_classes,id',
            'academic_session_id' => 'nullable|integer|exists:academic_sessions,id',
            'section_order' => 'nullable|integer|min:0',
            'capacity' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $validated['tenant_id'] = $request->get('tenant')?->id;
        $validated['section_order'] = $request->section_order ?? AcademicSection::max('section_order') + 1;
        $validated['status'] = $request->status ?? 'active';

        $section = AcademicSection::create($validated);

        return ApiResource::success([
            'message' => 'বিভাগ তৈরি হয়েছে।',
            'data' => [
                'id' => $section->id,
                'section_name' => $section->section_name,
                'section_bn' => $section->section_bn,
                'class_id' => $section->class_id,
                'class' => [
                    'id' => $section->class?->id,
                    'class_name' => $section->class?->class_name,
                    'class_bn' => $section->class?->class_bn,
                ],
                'academic_session_id' => $section->academic_session_id,
                'section_order' => $section->section_order,
                'capacity' => $section->capacity,
                'status' => $section->status,
                'created_at' => $section->created_at?->format('d M, Y'),
            ],
        ], 201);
    }

    public function showSection(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $section = AcademicSection::where('tenant_id', $tenant?->id)
            ->with('class', 'session')
            ->findOrFail($id);

        return ApiResource::success([
            'id' => $section->id,
            'section_name' => $section->section_name,
            'section_bn' => $section->section_bn,
            'class_id' => $section->class_id,
            'class' => [
                'id' => $section->class?->id,
                'class_name' => $section->class?->class_name,
                'class_bn' => $section->class?->class_bn,
            ],
            'academic_session_id' => $section->academic_session_id,
            'section_order' => $section->section_order,
            'capacity' => $section->capacity,
            'status' => $section->status,
            'created_at' => $section->created_at?->format('d M, Y'),
            'updated_at' => $section->updated_at?->format('d M, Y'),
        ]);
    }

    public function updateSection(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $section = AcademicSection::where('tenant_id', $tenant?->id)->findOrFail($id);

        $validated = $request->validate([
            'section_name' => 'sometimes|string|max:50',
            'section_bn' => 'nullable|string|max:50',
            'class_id' => 'sometimes|integer|exists:academic_classes,id',
            'academic_session_id' => 'nullable|integer|exists:academic_sessions,id',
            'section_order' => 'nullable|integer|min:0',
            'capacity' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $section->update($validated);

        return ApiResource::success([
            'message' => 'বিভাগ আপডেট হয়েছে।',
            'data' => [
                'id' => $section->id,
                'section_name' => $section->section_name,
                'section_bn' => $section->section_bn,
                'class_id' => $section->class_id,
                'section_order' => $section->section_order,
                'capacity' => $section->capacity,
                'status' => $section->status,
                'updated_at' => $section->updated_at?->format('d M, Y'),
            ],
        ]);
    }

    public function destroySection(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $section = AcademicSection::where('tenant_id', $tenant?->id)->findOrFail($id);
        $section->delete();

        return ApiResource::success(['message' => 'বিভাগ ডিলিট হয়েছে।']);
    }

    // ─── Subjects ──────────────────────────────────────────────────────────────

    public function subjects(Request $request)
    {
        $tenant = $request->get('tenant');
        $query = AcademicSubject::where('tenant_id', $tenant?->id)
            ->when($request->filled('class_id'), fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->orderBy('subject_order', 'asc');

        $perPage = min((int) $request->query('per_page', 15), 100);
        $items = $query->paginate($perPage);

        return ApiResource::collection($items, function ($s) {
            return [
                'id' => $s->id,
                'subject_name' => $s->subject_name,
                'subject_bn' => $s->subject_bn ?? $s->subject_name,
                'class_id' => $s->class_id,
                'class' => $s->whenLoaded('class', fn() => [
                    'id' => $s->class?->id,
                    'class_name' => $s->class?->class_name,
                    'class_bn' => $s->class?->class_bn,
                ]),
                'subject_code' => $s->subject_code,
                'subject_order' => $s->subject_order,
                'status' => $s->status,
                'total_marks' => $s->total_marks,
                'passing_marks' => $s->passing_marks,
                'created_at' => $s->created_at?->format('d M, Y'),
            ];
        });
    }

    public function storeSubject(Request $request)
    {
        $validated = $request->validate([
            'subject_name' => 'required|string|max:100',
            'subject_bn' => 'nullable|string|max:100',
            'class_id' => 'required|integer|exists:academic_classes,id',
            'subject_code' => 'nullable|string|max:20',
            'subject_order' => 'nullable|integer|min:0',
            'total_marks' => 'nullable|integer|min:0',
            'passing_marks' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $validated['tenant_id'] = $request->get('tenant')?->id;
        $validated['subject_order'] = $request->subject_order ?? AcademicSubject::max('subject_order') + 1;
        $validated['status'] = $request->status ?? 'active';

        $subject = AcademicSubject::create($validated);

        return ApiResource::success([
            'message' => 'বিষয় তৈরি হয়েছে।',
            'data' => [
                'id' => $subject->id,
                'subject_name' => $subject->subject_name,
                'subject_bn' => $subject->subject_bn,
                'class_id' => $subject->class_id,
                'subject_code' => $subject->subject_code,
                'subject_order' => $subject->subject_order,
                'total_marks' => $subject->total_marks,
                'passing_marks' => $subject->passing_marks,
                'status' => $subject->status,
                'created_at' => $subject->created_at?->format('d M, Y'),
            ],
        ], 201);
    }

    public function showSubject(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $subject = AcademicSubject::where('tenant_id', $tenant?->id)
            ->with('class')
            ->findOrFail($id);

        return ApiResource::success([
            'id' => $subject->id,
            'subject_name' => $subject->subject_name,
            'subject_bn' => $subject->subject_bn,
            'class_id' => $subject->class_id,
            'class' => [
                'id' => $subject->class?->id,
                'class_name' => $subject->class?->class_name,
                'class_bn' => $subject->class?->class_bn,
            ],
            'subject_code' => $subject->subject_code,
            'subject_order' => $subject->subject_order,
            'total_marks' => $subject->total_marks,
            'passing_marks' => $subject->passing_marks,
            'status' => $subject->status,
            'created_at' => $subject->created_at?->format('d M, Y'),
            'updated_at' => $subject->updated_at?->format('d M, Y'),
        ]);
    }

    public function updateSubject(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $subject = AcademicSubject::where('tenant_id', $tenant?->id)->findOrFail($id);

        $validated = $request->validate([
            'subject_name' => 'sometimes|string|max:100',
            'subject_bn' => 'nullable|string|max:100',
            'class_id' => 'sometimes|integer|exists:academic_classes,id',
            'subject_code' => 'nullable|string|max:20',
            'subject_order' => 'nullable|integer|min:0',
            'total_marks' => 'nullable|integer|min:0',
            'passing_marks' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $subject->update($validated);

        return ApiResource::success([
            'message' => 'বিষয় আপডেট হয়েছে।',
            'data' => [
                'id' => $subject->id,
                'subject_name' => $subject->subject_name,
                'subject_bn' => $subject->subject_bn,
                'class_id' => $subject->class_id,
                'subject_code' => $subject->subject_code,
                'subject_order' => $subject->subject_order,
                'total_marks' => $subject->total_marks,
                'passing_marks' => $subject->passing_marks,
                'status' => $subject->status,
                'updated_at' => $subject->updated_at?->format('d M, Y'),
            ],
        ]);
    }

    public function destroySubject(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $subject = AcademicSubject::where('tenant_id', $tenant?->id)->findOrFail($id);
        $subject->delete();

        return ApiResource::success(['message' => 'বিষয় ডিলিট হয়েছে।']);
    }

    // ─── Subject Assignment (Teacher-Subject-Class-Section) ───────────────────

    public function subjectAssignment(Request $request)
    {
        $tenant = $request->get('tenant');
        $query = TeacherAssignment::where('tenant_id', $tenant?->id)
            ->with(['teacher', 'subject', 'academicClass', 'academicSection'])
            ->when($request->filled('teacher_id'), fn($q) => $q->where('teacher_id', $request->teacher_id))
            ->when($request->filled('subject_id'), fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->filled('class_id'), fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->filled('section_id'), fn($q) => $q->where('section_id', $request->section_id))
            ->orderByDesc('id');

        $perPage = min((int) $request->query('per_page', 15), 100);
        $items = $query->paginate($perPage);

        return ApiResource::collection($items, function ($a) {
            return [
                'id' => $a->id,
                'teacher_id' => $a->teacher_id,
                'teacher' => $a->whenLoaded('teacher', fn() => [
                    'id' => $a->teacher?->id,
                    'name_bn' => $a->teacher?->name_bn ?? $a->teacher?->name,
                    'name' => $a->teacher?->name,
                    'designation' => $a->teacher?->designation,
                ]),
                'subject_id' => $a->subject_id,
                'subject' => $a->whenLoaded('subject', fn() => [
                    'id' => $a->subject?->id,
                    'subject_name' => $a->subject?->subject_name,
                    'subject_bn' => $a->subject?->subject_bn,
                ]),
                'class_id' => $a->class_id,
                'academicClass' => $a->whenLoaded('academicClass', fn() => [
                    'id' => $a->academicClass?->id,
                    'class_name' => $a->academicClass?->class_name,
                    'class_bn' => $a->academicClass?->class_bn,
                ]),
                'section_id' => $a->section_id,
                'academicSection' => $a->whenLoaded('academicSection', fn() => [
                    'id' => $a->academicSection?->id,
                    'section_name' => $a->academicSection?->section_name,
                    'section_bn' => $a->academicSection?->section_bn,
                ]),
                'academic_session_id' => $a->academic_session_id,
                'assigned_date' => $a->assigned_date?->format('d M, Y'),
                'status' => $a->status,
                'created_at' => $a->created_at?->format('d M, Y'),
            ];
        });
    }

    public function storeSubjectAssignment(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|integer|exists:users,id',
            'subject_id' => 'required|integer|exists:academic_subjects,id',
            'class_id' => 'required|integer|exists:academic_classes,id',
            'section_id' => 'nullable|integer|exists:academic_sections,id',
            'academic_session_id' => 'nullable|integer|exists:academic_sessions,id',
            'assigned_date' => 'nullable|date',
            'status' => 'nullable|string|in:active,inactive,pending',
        ]);

        $validated['tenant_id'] = $request->get('tenant')?->id;
        $validated['assigned_date'] = $validated['assigned_date'] ? \Carbon\Carbon::parse($validated['assigned_date']) : now();
        $validated['status'] = $request->status ?? 'active';

        // Check if assignment already exists
        $exists = TeacherAssignment::where('tenant_id', $validated['tenant_id'])
            ->where('teacher_id', $validated['teacher_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('class_id', $validated['class_id'])
            ->where('section_id', $validated['section_id'] ?? null)
            ->exists();

        if ($exists) {
            return ApiResource::error('এই শিক্ষক-বিষয়-শ্রেণি বরাদ্দটি ইতিমধ্যে আছে।', 422);
        }

        $assignment = TeacherAssignment::create($validated);

        return ApiResource::success([
            'message' => 'বিষয় বরাদ্দ তৈরি হয়েছে।',
            'data' => [
                'id' => $assignment->id,
                'teacher_id' => $assignment->teacher_id,
                'subject_id' => $assignment->subject_id,
                'class_id' => $assignment->class_id,
                'section_id' => $assignment->section_id,
                'academic_session_id' => $assignment->academic_session_id,
                'assigned_date' => $assignment->assigned_date?->format('d M, Y'),
                'status' => $assignment->status,
                'created_at' => $assignment->created_at?->format('d M, Y'),
            ],
        ], 201);
    }

    public function showSubjectAssignment(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $assignment = TeacherAssignment::where('tenant_id', $tenant?->id)
            ->with(['teacher', 'subject', 'academicClass', 'academicSection'])
            ->findOrFail($id);

        return ApiResource::success([
            'id' => $assignment->id,
            'teacher_id' => $assignment->teacher_id,
            'teacher' => [
                'id' => $assignment->teacher?->id,
                'name_bn' => $assignment->teacher?->name_bn ?? $assignment->teacher?->name,
                'name' => $assignment->teacher?->name,
                'designation' => $assignment->teacher?->designation,
            ],
            'subject_id' => $assignment->subject_id,
            'subject' => [
                'id' => $assignment->subject?->id,
                'subject_name' => $assignment->subject?->subject_name,
                'subject_bn' => $assignment->subject?->subject_bn,
            ],
            'class_id' => $assignment->class_id,
            'academicClass' => [
                'id' => $assignment->academicClass?->id,
                'class_name' => $assignment->academicClass?->class_name,
                'class_bn' => $assignment->academicClass?->class_bn,
            ],
            'section_id' => $assignment->section_id,
            'academicSection' => $assignment->academicSection?->map(fn($s) => [
                'id' => $s->id,
                'section_name' => $s->section_name,
                'section_bn' => $s->section_bn,
            ]),
            'academic_session_id' => $assignment->academic_session_id,
            'assigned_date' => $assignment->assigned_date?->format('d M, Y'),
            'status' => $assignment->status,
            'created_at' => $assignment->created_at?->format('d M, Y'),
            'updated_at' => $assignment->updated_at?->format('d M, Y'),
        ]);
    }

    public function updateSubjectAssignment(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $assignment = TeacherAssignment::where('tenant_id', $tenant?->id)->findOrFail($id);

        $validated = $request->validate([
            'teacher_id' => 'sometimes|integer|exists:users,id',
            'subject_id' => 'sometimes|integer|exists:academic_subjects,id',
            'class_id' => 'sometimes|integer|exists:academic_classes,id',
            'section_id' => 'nullable|integer|exists:academic_sections,id',
            'academic_session_id' => 'nullable|integer|exists:academic_sessions,id',
            'assigned_date' => 'nullable|date',
            'status' => 'nullable|string|in:active,inactive,pending',
        ]);

        if (isset($validated['assigned_date'])) {
            $validated['assigned_date'] = \Carbon\Carbon::parse($validated['assigned_date']);
        }

        $assignment->update($validated);

        return ApiResource::success([
            'message' => 'বিষয় বরাদ্দ আপডেট হয়েছে।',
            'data' => [
                'id' => $assignment->id,
                'teacher_id' => $assignment->teacher_id,
                'subject_id' => $assignment->subject_id,
                'class_id' => $assignment->class_id,
                'section_id' => $assignment->section_id,
                'academic_session_id' => $assignment->academic_session_id,
                'assigned_date' => $assignment->assigned_date?->format('d M, Y'),
                'status' => $assignment->status,
                'updated_at' => $assignment->updated_at?->format('d M, Y'),
            ],
        ]);
    }

    public function destroySubjectAssignment(Request $request, $id)
    {
        $tenant = $request->get('tenant');
        $assignment = TeacherAssignment::where('tenant_id', $tenant?->id)->findOrFail($id);
        $assignment->delete();

        return ApiResource::success(['message' => 'বিষয় বরাদ্দ ডিলিট হয়েছে।']);
    }
}