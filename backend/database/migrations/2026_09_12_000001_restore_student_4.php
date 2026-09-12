<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Restore student 4 with complete Bengali madrasa profile details
        DB::table('students')->where('id', 4)->update([
            'deleted_at' => null,
            'status' => 'active',
            'name_bn' => 'মুহাম্মদ তানভীর আহমেদ',
            'name_en' => 'Muhammad Tanvir Ahmed',
            'father_name' => 'হাফেজ ক্বারী শফিকুল ইসলাম',
            'father_phone' => '01712345678',
            'mother_name' => 'ফাতেমা বেগম',
            'mother_phone' => '01812345678',
            'guardian_name' => 'হাফেজ ক্বারী শফিকুল ইসলাম',
            'guardian_phone' => '01712345678',
            'guardian_relation' => 'পিতা',
            'blood_group' => 'B+',
            'gender' => 'male',
            'date_of_birth' => '2016-04-15',
            'address_bn' => 'গ্রাম: ফুলতলী, ডাকঘর: ফুলতলী বাজার, উপজেলা: জকিগঞ্জ, জেলা: সিলেট',
            'emergency_contact_name' => 'মাওলানা আব্দুল হাই (চাচা)',
            'emergency_contact_phone' => '01912345678',
            'health_summary' => json_encode(['status' => 'শারীরিক ও মানসিক অবস্থা স্বাভাবিক']),
            'nationality' => 'বাংলাদেশী',
        ]);

        $student = DB::table('students')->where('id', 4)->first();
        if ($student && $student->user_id) {
            DB::table('users')->where('id', $student->user_id)->update([
                'deleted_at' => null,
                'name_bn' => 'মুহাম্মদ তানভীর আহমেদ',
                'name_en' => 'Muhammad Tanvir Ahmed',
                'phone' => '01712345678',
                'is_active' => 1,
            ]);

            // Ensure an active enrollment exists for student 4
            $class = DB::table('classes')->where('tenant_id', $student->tenant_id)->first();
            $session = DB::table('academic_sessions')->where('tenant_id', $student->tenant_id)->first();

            $existingEnrollment = DB::table('enrollments')->where('student_id', $student->user_id)->first();
            if (!$existingEnrollment && $class) {
                DB::table('enrollments')->insert([
                    'tenant_id' => $student->tenant_id,
                    'student_id' => $student->user_id,
                    'class_id' => $class->id,
                    'session_id' => $session?->id,
                    'enrollment_number' => 'ENR-2026-0004',
                    'enrollment_date' => now(),
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else if ($existingEnrollment) {
                DB::table('enrollments')->where('id', $existingEnrollment->id)->update([
                    'deleted_at' => null,
                    'status' => 'active',
                    'class_id' => $class ? $class->id : $existingEnrollment->class_id,
                    'session_id' => $session ? $session->id : $existingEnrollment->session_id,
                ]);
            }
        }
    }

    public function down(): void
    {
    }
};
