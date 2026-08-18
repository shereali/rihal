<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\AttendanceRecord;
use App\Models\FeePayment;
use App\Models\StudentGuardian;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Notification dispatch service.
 *
 * Resolves each student's guardian (a User with role=guardian linked via
 * student_guardians) and records an in-app notification. The SMS / email
 * "send" is a seam: when a gateway (Twilio, SMTP, a BD SMS provider) is
 * configured, replace the Log call in dispatchExternal() with the real send.
 * No external gateway is configured in this environment, so dispatch is
 * logged and the source record's parent_notified flags are set.
 */
class NotificationService
{
    /**
     * Notify guardians of absent students.
     *
     * @param int[] $attendanceRecordIds AttendanceRecord ids with status=absent
     */
    public function sendAbsence(array $attendanceRecordIds, string $channel = 'in_app'): int
    {
        $records = AttendanceRecord::whereIn('id', $attendanceRecordIds)->get();
        $count = 0;

        foreach ($records as $record) {
            if ($record->status !== 'absent') {
                continue;
            }
            $guardians = $this->guardiansForStudentUserId($record->student_id);
            foreach ($guardians as $guardian) {
                AppNotification::create([
                    'tenant_id' => $record->tenant_id,
                    'recipient_id' => $guardian->id,
                    'type' => 'absence',
                    'title_bn' => 'আপনার সন্তান অনুপস্থিত',
                    'body_bn' => 'আপনার সন্তান ' . $record->date->toDateString() . ' তারিখে অনুপস্থিত ছিলেন।',
                    'related_type' => 'attendance_record',
                    'related_id' => $record->id,
                    'channel' => $channel,
                ]);
                $this->dispatchExternal($guardian, 'absence', $record->date->toDateString());
                $count++;
            }
            $record->update([
                'parent_notified' => true,
                'parent_notified_at' => now(),
                'parent_notified_method' => $channel,
            ]);
        }

        return $count;
    }

    /**
     * Notify guardians of students with unpaid fees.
     *
     * @param int[] $feePaymentIds FeePayment ids
     */
    public function sendFeeDue(array $feePaymentIds, string $channel = 'in_app'): int
    {
        $payments = FeePayment::whereIn('id', $feePaymentIds)->get();
        $count = 0;

        foreach ($payments as $payment) {
            if ((bool) $payment->is_fully_paid) {
                continue;
            }
            $guardians = $this->guardiansForStudentUserId($payment->student_id);
            foreach ($guardians as $guardian) {
                AppNotification::create([
                    'tenant_id' => $payment->tenant_id,
                    'recipient_id' => $guardian->id,
                    'type' => 'fee_due',
                    'title_bn' => 'ফি বকেয়া রয়েছে',
                    'body_bn' => 'আপনার সন্তানের ফি বকেয়া: ৳' . number_format($payment->balance, 2) . '। দয়া করে পরিশোধ করুন।',
                    'related_type' => 'fee_payment',
                    'related_id' => $payment->id,
                    'channel' => $channel,
                ]);
                $this->dispatchExternal($guardian, 'fee_due', (string) $payment->balance);
                $count++;
            }
            $payment->update(['parent_notified' => true]);
        }

        return $count;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<User>
     */
    protected function guardiansForStudentUserId(int $studentUserId)
    {
        // attendance_records / fee_payments store student_id as users.id,
        // but student_guardians.student_id references students.id.
        $studentModelId = \App\Models\Student::where('user_id', $studentUserId)->value('id');
        if (!$studentModelId) {
            return collect();
        }

        $guardianIds = StudentGuardian::where('student_id', $studentModelId)
            ->whereNotNull('user_id')
            ->pluck('user_id');

        return User::whereIn('id', $guardianIds)->get();
    }

    /**
     * External dispatch seam. Replace with a real SMS/email gateway call.
     */
    protected function dispatchExternal(User $guardian, string $type, string $detail): void
    {
        Log::info('[NotificationService] dispatch', [
            'recipient' => $guardian->email,
            'type' => $type,
            'detail' => $detail,
            'note' => 'No SMS/email gateway configured; in-app notification recorded.',
        ]);
    }
}
