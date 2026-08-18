<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\AcademicSession;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicSubject;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\FeeStructure;
use App\Models\Donor;
use App\Models\Fund;
use App\Models\Notice;
use App\Models\AttendanceRecord;
use App\Models\AttendanceDevice;
use App\Models\TeacherQualification;
use App\Models\TeacherContract;
use App\Models\Plugin;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        // Find platform admin
        $platformAdmin = User::where('email', 'admin@rihal.app')->first();

        // Find or create demo tenant
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo-madrasa'],
            [
                'name_bn' => 'দাখিল মাদ্রাসা',
                'name_en' => 'Dakhil Madrasa',
                'type' => 'madrasa',
                'registration_number' => 'MDR-2026-001',
                'established_year' => 1985,
                'address_bn' => 'বাংলাদেশ, ঢাকা জেলা, মিরপুর',
                'city' => 'ঢাকা',
                'district' => 'ঢাকা',
                'contact_email' => 'info@demomadrasa.bd',
                'contact_phone' => '+880****5678',
                'principal_name' => 'মোঃ আব্দুল ওয়াহেদ',
                'principal_email' => 'principal@demomadrasa.bd',
                'subscription_tier' => 'free',
                'subscription_status' => 'active',
                'modules_enabled' => ['student', 'academic', 'finance', 'attendance', 'notice', 'hostel', 'transport', 'inventory', 'hr', 'property', 'report', 'ai'],
                'settings' => [],
                'activated_at' => now(),
            ]
        );

        // Academic session
        $session = AcademicSession::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name_bn' => '২০২৬-২০২৭'],
            [
                'tenant_id' => $tenant->id,
                'name_bn' => '২০২৬-২০২৭',
                'name_en' => '2026-2027',
                'start_date' => '2026-01-01',
                'end_date' => '2027-12-31',
                'status' => 'active',
                'terms' => [
                    ['name' => 'প্রথম পরীক্ষা', 'start' => '2026-09-01', 'end' => '2026-09-15'],
                    ['name' => 'মধ্যম পরীক্ষা', 'start' => '2027-01-01', 'end' => '2027-01-15'],
                    ['name' => 'বার্ষিক পরীক্ষা', 'start' => '2027-06-01', 'end' => '2027-06-30'],
                ],
            ]
        );

        // Classes
        $classNames = [
            ['bn' => 'ক্লাস ১', 'en' => 'Class 1', 'type' => 'regular'],
            ['bn' => 'ক্লাস ২', 'en' => 'Class 2', 'type' => 'regular'],
            ['bn' => 'ক্লাস ৩', 'en' => 'Class 3', 'type' => 'regular'],
            ['bn' => 'ক্লাস ৪', 'en' => 'Class 4', 'type' => 'regular'],
            ['bn' => 'ক্লাস ৫', 'en' => 'Class 5', 'type' => 'regular'],
        ];

        foreach ($classNames as $class) {
            AcademicClass::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name_en' => $class['en']],
                [
                    'tenant_id' => $tenant->id,
                    'session_id' => $session->session_id,
                    'name_bn' => $class['bn'],
                    'name_en' => $class['en'],
                    'class_type' => $class['type'],
                    'grade_level' => (int)str_replace('Class ', '', $class['en']),
                    'room_name' => $class['bn'] . ' ঘর',
                    'student_count' => rand(20, 40),
                ]
            );
        }

        // Sections (one per class: ক, খ, গ)
        $sectionNames = [
            ['bn' => 'ক', 'en' => 'A'],
            ['bn' => 'খ', 'en' => 'B'],
            ['bn' => 'গ', 'en' => 'C'],
        ];
        foreach (AcademicClass::where('tenant_id', $tenant->id)->get() as $class) {
            foreach ($sectionNames as $sec) {
                AcademicSection::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'class_id' => $class->class_id, 'name_en' => $sec['en']],
                    [
                        'tenant_id' => $tenant->id,
                        'class_id' => $class->class_id,
                        'name_bn' => $sec['bn'],
                        'name_en' => $sec['en'],
                        'section_type' => 'regular',
                        'student_count' => 0,
                        'room_name' => $class->name_bn . ' ' . $sec['bn'] . ' ঘর',
                    ]
                );
            }
        }

        // Subjects
        $subjects = [
            ['bn' => 'ধর্মতত্ত্ব', 'en' => 'Islamic Studies'],
            ['bn' => 'বাংলা', 'en' => 'Bengali'],
            ['bn' => 'ইংরেজি', 'en' => 'English'],
            ['bn' => 'গণিত', 'en' => 'Mathematics'],
            ['bn' => 'বিজ্ঞান', 'en' => 'Science'],
            ['bn' => 'সামাজিক বিজ্ঞান', 'en' => 'Social Science'],
            ['bn' => 'হাফিজ', 'en' => 'Hifz'],
            ['bn' => 'আরবি', 'en' => 'Arabic'],
        ];

        foreach ($subjects as $subject) {
            AcademicSubject::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name_en' => $subject['en']],
                [
                    'tenant_id' => $tenant->id,
                    'name_bn' => $subject['bn'],
                    'name_en' => $subject['en'],
                    'code' => strtoupper(substr($subject['en'], 0, 3)),
                    'subject_type' => in_array($subject['en'], ['Islamic Studies', 'Hifz', 'Arabic']) ? 'islamic' : 'regular',
                    'education_board' => 'মাদ্রাসা শিক্ষা বোর্ড',
                    'teaching_hours_per_week' => rand(2, 6),
                    'credit_hours' => rand(1, 3),
                ]
            );
        }

        // Fee structure
        FeeStructure::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name_en' => 'Annual Fee 2026-2027'],
            [
                'tenant_id' => $tenant->id,
                'class_id' => json_encode([1, 2, 3, 4, 5]),
                'session_id' => $session->session_id,
                'name_bn' => 'বার্ষিক ফি ২০২৬-২০২৭',
                'name_en' => 'Annual Fee 2026-2027',
                'admission_fee' => 500,
                'monthly_fee' => 300,
                'exam_fee' => 100,
                'library_fee' => 50,
                'sports_fee' => 50,
                'grace_period_days' => 14,
                'late_fee_rate' => 2.5,
                'is_online_payment_enabled' => true,
                'status' => 'active',
            ]
        );

        // Admin user
        $dadmin = User::firstOrCreate(
            ['email' => 'admin@demo.bd'],
            [
                'tenant_id' => $tenant->id,
                'name_bn' => 'মাদ্রাসা প্রশাসক',
                'name_en' => 'Madrasa Administrator',
                'phone' => '+880****5432',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
                'is_platform_admin' => false,
            ]
        );

        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        if ($adminRole && !$dadmin->roles()->where('role_id', $adminRole->id)->exists()) {
            $dadmin->roles()->attach($adminRole->id, ['tenant_id' => $tenant->id]);
        }

        // Teachers
        $teacherData = [
            ['bn' => 'আলী আহমেদ', 'en' => 'Ali Ahmed', 'desig' => 'ধর্ম বিষয় শিক্ষক'],
            ['bn' => 'সুমিয়া আক্তার', 'en' => 'Sumiya Akter', 'desig' => 'বাংলা বিষয় শিক্ষিকা'],
            ['bn' => 'রাসেল রহমান', 'en' => 'Rasel Rahman', 'desig' => 'গণিত বিষয় শিক্ষক'],
            ['bn' => 'নুরুন্নাহার বেগম', 'en' => 'Nurunnahar Begum', 'desig' => 'ইংরেজি বিষয় শিক্ষিকা'],
        ];

        foreach ($teacherData as $tData) {
            $email = strtolower(str_replace(' ', '.', $tData['en'])) . '@demo.bd';
            $teacherUser = User::firstOrCreate(
                ['email' => $email],
                [
                    'tenant_id' => $tenant->id,
                    'name_bn' => $tData['bn'],
                    'name_en' => $tData['en'],
                    'phone' => '+88017' . rand(10000000, 99999999),
                    'password' => Hash::make('teacher123'),
                    'role' => 'teacher',
                    'title' => $tData['desig'],
                    'is_active' => true,
                ]
            );

            $teacher = Teacher::firstOrCreate(
                ['tenant_id' => $tenant->id, 'user_id' => $teacherUser->id],
                [
                    'tenant_id' => $tenant->id,
                    'user_id' => $teacherUser->id,
                    'employee_id' => 'TCH-' . rand(1000, 9999),
                    'designation' => $tData['desig'],
                    'department' => 'ধর্মতত্ত্ব বিভাগ',
                    'join_date' => Carbon::now()->subMonths(rand(1, 24)),
                    'status' => 'active',
                    'qualifications' => ['আলিম', 'বিএডি'],
                    'experience' => [['year' => 2018, 'institution' => 'ঢাকা মাদ্রাসা'], ['year' => 2020, 'institution' => 'রিহাল মাদ্রাসা']],
                ]
            );

            TeacherQualification::firstOrCreate(
                ['teacher_id' => $teacher->id, 'certificate' => 'আলিম'],
                [
                    'teacher_id' => $teacher->id,
                    'certificate' => 'আলিম',
                    'institution' => 'ঢাকা মাদ্রাসা',
                    'board' => 'বাংলাদেশ মাদ্রাসা শিক্ষা বোর্ড',
                    'year' => 2018,
                    'grade' => 'বি',
                ]
            );

            TeacherContract::firstOrCreate(
                ['teacher_id' => $teacher->id, 'contract_type' => 'পূর্ণকালীন'],
                [
                    'teacher_id' => $teacher->id,
                    'contract_type' => 'পূর্ণকালীন',
                    'salary' => 25000,
                    'start_date' => Carbon::now()->subMonths(rand(1, 24)),
                    'end_date' => null,
                    'status' => 'active',
                ]
            );

            $teacherRole = \App\Models\Role::where('name', 'teacher')->first();
            if ($teacherRole && !$teacherUser->roles()->where('role_id', $teacherRole->id)->exists()) {
                $teacherUser->roles()->attach($teacherRole->id, ['tenant_id' => $tenant->id]);
            }
        }

        // Students
        for ($i = 1; $i <= 15; $i++) {
            $email = 'student' . $i . '@demo.bd';
            $studentUser = User::firstOrCreate(
                ['email' => $email],
                [
                    'tenant_id' => $tenant->id,
                    'name_bn' => 'ছাত্র ' . $i,
                    'name_en' => 'Student ' . $i,
                    'password' => Hash::make('student123'),
                    'role' => 'student',
                    'is_active' => true,
                ]
            );

            $student = Student::firstOrCreate(
                ['tenant_id' => $tenant->id, 'user_id' => $studentUser->id],
                [
                    'tenant_id' => $tenant->id,
                    'user_id' => $studentUser->id,
                    'admission_number' => 'ADM-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'name_bn' => 'ছাত্র ' . $i,
                    'name_en' => 'Student ' . $i,
                    'date_of_birth' => Carbon::now()->subYears(rand(6, 12))->subDays(rand(0, 365)),
                    'gender' => rand(0, 1) ? 'ছেলে' : 'মেয়ে',
                    'father_name' => 'পিতা ' . $i,
                    'father_phone' => '+88017' . rand(10000000, 99999999),
                    'mother_name' => 'মাতা ' . $i,
                    'address_bn' => 'বাংলাদেশ, ঢাকা জেলা, ' . rand(1, 10) . ' নম্বর রোড',
                    'status' => 'active',
                    'admission_date' => Carbon::now()->subMonths(rand(0, 12)),
                ]
            );

            StudentGuardian::firstOrCreate(
                ['student_id' => $student->id, 'relationship' => 'পিতা'],
                [
                    'student_id' => $student->id,
                    'user_id' => null,
                    'relationship' => 'পিতা',
                    'is_primary' => true,
                    'phone' => '+88017' . rand(10000000, 99999999),
                    'has_app' => true,
                ]
            );
        }

        // Guardian (demo) — linked to student 1 so the parent portal is demoable
        $guardianUser = User::firstOrCreate(
            ['email' => 'guardian@demo.bd'],
            [
                'tenant_id' => $tenant->id,
                'name_bn' => 'অভিভাবক ১',
                'name_en' => 'Guardian 1',
                'password' => Hash::make('guardian123'),
                'role' => 'guardian',
                'is_active' => true,
            ]
        );
        $guardianRole = \App\Models\Role::where('name', 'guardian')->first();
        if ($guardianRole && !$guardianUser->roles()->where('role_id', $guardianRole->id)->exists()) {
            $guardianUser->roles()->attach($guardianRole->id, ['tenant_id' => $tenant->id]);
        }
        $firstStudent = Student::where('tenant_id', $tenant->id)->orderBy('id')->first();
        if ($firstStudent) {
            StudentGuardian::updateOrCreate(
                ['student_id' => $firstStudent->id, 'user_id' => $guardianUser->id],
                [
                    'student_id' => $firstStudent->id,
                    'user_id' => $guardianUser->id,
                    'relationship' => 'পিতা',
                    'is_primary' => true,
                    'phone' => '+8801700000001',
                    'has_app' => true,
                ]
            );
        }

        // Donor
        Donor::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name_en' => 'HM Dar'],
            [
                'tenant_id' => $tenant->id,
                'name_bn' => 'এইচ এম দার',
                'name_en' => 'HM Dar',
                'type' => 'individual',
                'phone' => '+8801711111111',
                'email' => 'hm.dar@example.com',
                'total_donated' => 500000,
                'last_donation_date' => Carbon::now()->subMonths(2),
                'donor_tier' => 'মূলধন',
                'recognition_level' => 'স্বীকৃত',
                'is_recurring_donor' => true,
            ]
        );

        // Fund
        Fund::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name_en' => 'General Fund'],
            [
                'tenant_id' => $tenant->id,
                'name_bn' => 'সাধারণ তহবিল',
                'name_en' => 'General Fund',
                'fund_type' => 'general',
                'currency' => 'BDT',
                'balance' => 500000,
                'opening_balance' => 500000,
            ]
        );

        // Notice
        Notice::firstOrCreate(
            ['tenant_id' => $tenant->id, 'title_bn' => 'ইসলামি বর্ষপর্ব উপলক্ষে বিশেষ প্রোগ্রাম'],
            [
                'tenant_id' => $tenant->id,
                'created_by_user_id' => $platformAdmin?->id,
                'title_bn' => 'ইসলামি বর্ষপর্ব উপলক্ষে বিশেষ প্রোগ্রাম',
                'title_en' => 'Special Program on Islamic New Year',
                'type' => 'notice',
                'content_bn' => 'আগামী ১৫ আগস্টের দিন মাদ্রাসায় ইসলামি বর্ষপর্ব উপলক্ষে বিশেষ অনুষ্ঠান অনুষ্ঠিত হবে। সকল ছাত্র-ছাত্রী এবং অভিভাবকগণ উপস্থিত থাকবেন।',
                'content_en' => 'A special program will be held on August 15th to celebrate Islamic New Year. All students and guardians are requested to attend.',
                'channels' => ['app', 'sms'],
                'read_by_count' => 0,
                'published_at' => now(),
            ]
        );

        // Attendance device
        AttendanceDevice::firstOrCreate(
            ['tenant_id' => $tenant->id, 'device_name' => 'ফিঙ্গারপ্রিন্ট মেশিন ১'],
            [
                'tenant_id' => $tenant->id,
                'device_name' => 'ফিঙ্গারপ্রিন্ট মেশিন ১',
                'device_type' => 'fingerprint',
                'model' => 'BioEnable',
                'serial_number' => 'BIO-001',
                'status' => 'active',
                'assigned_to_class_id' => 1,
            ]
        );

        // Attendance records
        $existingCount = AttendanceRecord::where('tenant_id', $tenant->id)->count();
        if ($existingCount < 10) {
            $toCreate = 10 - $existingCount;
            for ($j = 0; $j < $toCreate; $j++) {
                AttendanceRecord::create([
                    'tenant_id' => $tenant->id,
                    'student_id' => $j + 1,
                    'date' => Carbon::now()->subDays($j),
                    'status' => (rand(0, 10) > 2) ? 'present' : 'absent',
                    'method' => 'fingerprint',
                    'device_id' => 1,
                    'check_in_time' => Carbon::now()->subDays($j)->setTime(8, 0, 0),
                    'check_out_time' => Carbon::now()->subDays($j)->setTime(14, 0, 0),
                    'parent_notified' => rand(0, 1) == 1,
                ]);
            }
        }

        // Plugin
        Plugin::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Rihal AI'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Rihal AI',
                'vendor' => 'Rihal',
                'version' => '1.0.0',
                'type' => 'built_in',
                'description' => 'সবক AI — প্রাকৃতিক ভাষা প্রক্রিয়াকরণ, আন্তর্জাতিক প্রশ্নোত্তর এবং স্মার্ট রিপোর্ট তৈরি',
                'enabled' => true,
                'is_active' => true,
            ]
        );
    }
}
