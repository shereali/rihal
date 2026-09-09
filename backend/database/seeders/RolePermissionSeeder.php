<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Models\Tenant;
use App\Models\TenantBranch;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            ['name' => 'platform.view', 'description' => 'View platform dashboard', 'category' => 'platform'],
            ['name' => 'platform.manage_tenants', 'description' => 'Create, update, delete tenants', 'category' => 'platform'],
            ['name' => 'platform.view_analytics', 'description' => 'View cross-tenant analytics', 'category' => 'platform'],

            // Tenant-level
            ['name' => 'tenant.view', 'description' => 'View tenant dashboard', 'category' => 'tenant'],
            ['name' => 'tenant.manage_users', 'description' => 'Create, update, delete users within tenant', 'category' => 'tenant'],
            ['name' => 'tenant.manage_settings', 'description' => 'Update tenant settings', 'category' => 'tenant'],

            // Student module
            ['name' => 'student.view', 'description' => 'View students', 'category' => 'student'],
            ['name' => 'student.create', 'description' => 'Add new students', 'category' => 'student'],
            ['name' => 'student.update', 'description' => 'Update student information', 'category' => 'student'],
            ['name' => 'student.delete', 'description' => 'Remove students (soft delete)', 'category' => 'student'],
            ['name' => 'student.export', 'description' => 'Export student data', 'category' => 'student'],

            // Academic module
            ['name' => 'academic.view', 'description' => 'View academic data', 'category' => 'academic'],
            ['name' => 'academic.manage_sessions', 'description' => 'Create, update academic sessions', 'category' => 'academic'],
            ['name' => 'academic.manage_classes', 'description' => 'Create, update classes and sections', 'category' => 'academic'],
            ['name' => 'academic.manage_subjects', 'description' => 'Create, update subjects', 'category' => 'academic'],
            ['name' => 'academic.manage_timetable', 'description' => 'Create, update timetables', 'category' => 'academic'],

            // Exam module
            ['name' => 'exam.view', 'description' => 'View exams and results', 'category' => 'exam'],
            ['name' => 'exam.create', 'description' => 'Create exams', 'category' => 'exam'],
            ['name' => 'exam.update', 'description' => 'Update exam information', 'category' => 'exam'],
            ['name' => 'exam.publish_results', 'description' => 'Publish exam results', 'category' => 'exam'],
            ['name' => 'exam.entry_marks', 'description' => 'Enter marks for exams', 'category' => 'exam'],

            // Finance module
            ['name' => 'finance.view', 'description' => 'View financial data', 'category' => 'finance'],
            ['name' => 'finance.manage_fee_structures', 'description' => 'Create, update fee structures', 'category' => 'finance'],
            ['name' => 'finance.record_payment', 'description' => 'Record fee payments', 'category' => 'finance'],
            ['name' => 'finance.manage_funds', 'description' => 'Create, update funds', 'category' => 'finance'],
            ['name' => 'finance.record_donation', 'description' => 'Record donations', 'category' => 'finance'],
            ['name' => 'finance.manage_expenses', 'description' => 'Create, approve expenses', 'category' => 'finance'],
            ['name' => 'finance.view_reports', 'description' => 'View financial reports', 'category' => 'finance'],
            ['name' => 'finance.approve_entries', 'description' => 'Approve journal entries', 'category' => 'finance'],
            ['name' => 'finance.manage_budget', 'description' => 'Create, manage budgets', 'category' => 'finance'],

            // Attendance module
            ['name' => 'attendance.view', 'description' => 'View attendance records', 'category' => 'attendance'],
            ['name' => 'attendance.mark', 'description' => 'Mark attendance', 'category' => 'attendance'],
            ['name' => 'attendance.manage_devices', 'description' => 'Manage attendance devices', 'category' => 'attendance'],
            ['name' => 'attendance.view_reports', 'description' => 'View attendance reports', 'category' => 'attendance'],
            ['name' => 'attendance.manage_patterns', 'description' => 'Manage attendance patterns', 'category' => 'attendance'],

            // Notice module
            ['name' => 'notice.view', 'description' => 'View notices', 'category' => 'notice'],
            ['name' => 'notice.create', 'description' => 'Create notices', 'category' => 'notice'],
            ['name' => 'notice.update', 'description' => 'Update notices', 'category' => 'notice'],
            ['name' => 'notice.delete', 'description' => 'Delete notices', 'category' => 'notice'],
            ['name' => 'notice.manage_templates', 'description' => 'Manage communication templates', 'category' => 'notice'],
            ['name' => 'notice.send', 'description' => 'Send notices through channels', 'category' => 'notice'],

            // Hostel module
            ['name' => 'hostel.view', 'description' => 'View hostel data', 'category' => 'hostel'],
            ['name' => 'hostel.manage_rooms', 'description' => 'Create, update hostel rooms', 'category' => 'hostel'],
            ['name' => 'hostel.manage_visitors', 'description' => 'Manage hostel visitors', 'category' => 'hostel'],

            // Transport module
            ['name' => 'transport.view', 'description' => 'View transport data', 'category' => 'transport'],
            ['name' => 'transport.manage_buses', 'description' => 'Create, update transport buses', 'category' => 'transport'],
            ['name' => 'transport.manage_routes', 'description' => 'Create, update transport routes', 'category' => 'transport'],
            ['name' => 'transport.manage_assignments', 'description' => 'Assign students to buses', 'category' => 'transport'],

            // HR module
            ['name' => 'hr.view', 'description' => 'View HR data', 'category' => 'hr'],
            ['name' => 'hr.manage_teacher_qualifications', 'description' => 'Manage teacher qualifications', 'category' => 'hr'],
            ['name' => 'hr.manage_contracts', 'description' => 'Manage teacher contracts', 'category' => 'hr'],
            ['name' => 'hr.manage_recruitment', 'description' => 'Post jobs, manage applications', 'category' => 'hr'],
            ['name' => 'hr.manage_staff', 'description' => 'Create, update staff', 'category' => 'hr'],

            // Inventory module
            ['name' => 'inventory.view', 'description' => 'View inventory', 'category' => 'inventory'],
            ['name' => 'inventory.manage_stocks', 'description' => 'Create, update stock items', 'category' => 'inventory'],
            ['name' => 'inventory.manage_transactions', 'description' => 'Record stock transactions', 'category' => 'inventory'],
            ['name' => 'inventory.manage_vendors', 'description' => 'Create, update vendors', 'category' => 'inventory'],

            // Property module
            ['name' => 'property.view', 'description' => 'View property data', 'category' => 'property'],
            ['name' => 'property.manage_properties', 'description' => 'Create, update properties', 'category' => 'property'],
            ['name' => 'property.manage_documents', 'description' => 'Upload property documents', 'category' => 'property'],

            // AI module
            ['name' => 'ai.view_dashboard', 'description' => 'View AI dashboard', 'category' => 'ai'],
            ['name' => 'ai.use_chat', 'description' => 'Use সবক AI chat', 'category' => 'ai'],
            ['name' => 'ai.view_predictions', 'description' => 'View AI predictions', 'category' => 'ai'],

            // Report module
            ['name' => 'report.view', 'description' => 'View reports', 'category' => 'report'],
            ['name' => 'report.create', 'description' => 'Create custom reports', 'category' => 'report'],
            ['name' => 'report.export', 'description' => 'Export reports', 'category' => 'report'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name']],
                ['description' => $perm['description'], 'category' => $perm['category']]
            );
        }

        // Create roles
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super_admin'],
            ['display_name' => 'Super Admin', 'description' => 'Platform administrator with full access to all tenants']
        );

        $admin = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Admin', 'description' => 'Tenant administrator with full access within the tenant']
        );

        $teacher = Role::firstOrCreate(
            ['name' => 'teacher'],
            ['display_name' => 'Teacher', 'description' => 'Teacher with access to academic, exam, attendance features']
        );

        $staff = Role::firstOrCreate(
            ['name' => 'staff'],
            ['display_name' => 'Staff', 'description' => 'Staff member with limited access']
        );

        $student = Role::firstOrCreate(
            ['name' => 'student'],
            ['display_name' => 'Student', 'description' => 'Student with access to personal data and homework']
        );

        $user = Role::firstOrCreate(
            ['name' => 'user'],
            ['display_name' => 'User', 'description' => 'General user with basic access']
        );

        // Assign permissions to roles
        $allPermissions = Permission::all();

        // super_admin gets everything
        $superAdmin->permissions()->sync($allPermissions->pluck('id'));

        // admin gets all tenant-scoped permissions
        $tenantPermissions = $allPermissions->whereNotIn('name', [
            'platform.view', 'platform.manage_tenants', 'platform.view_analytics'
        ]);
        $admin->permissions()->sync($tenantPermissions->pluck('id'));

        // teacher gets academic + exam + attendance + notice + homework
        $teacherPermissionNames = [
            'tenant.view', 'student.view', 'academic.view', 'academic.manage_sessions',
            'academic.manage_classes', 'academic.manage_subjects', 'academic.manage_timetable',
            'exam.view', 'exam.create', 'exam.update', 'exam.publish_results', 'exam.entry_marks',
            'attendance.view', 'attendance.mark',
            'notice.view', 'notice.create', 'notice.update',
            'hr.view',
        ];
        $teacher->permissions()->sync(
            Permission::whereIn('name', $teacherPermissionNames)->pluck('id')
        );

        // staff gets basic access
        $staffPermissionNames = [
            'tenant.view', 'attendance.view', 'attendance.mark',
            'notice.view',
        ];
        $staff->permissions()->sync(
            Permission::whereIn('name', $staffPermissionNames)->pluck('id')
        );

        // student gets minimal access
        $studentPermissionNames = [
            'student.view',
        ];
        $student->permissions()->sync(
            Permission::whereIn('name', $studentPermissionNames)->pluck('id')
        );

        // user gets basic read access
        $userPermissionNames = [
            'tenant.view',
        ];
        $user->permissions()->sync(
            Permission::whereIn('name', $userPermissionNames)->pluck('id')
        );
    }
}
