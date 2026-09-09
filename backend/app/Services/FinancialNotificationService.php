<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Loan;
use App\Models\NotificationDelivery;
use App\Models\Orphan;
use App\Models\OrphanSponsorship;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Throwable;

class FinancialNotificationService
{
    public function loanPaymentRecorded(Loan $loan, float $amount): void
    {
        if ($loan->user_id) {
            AppNotification::create([
                'tenant_id' => $loan->tenant_id,
                'recipient_id' => $loan->user_id,
                'type' => 'loan_payment',
                'title_bn' => 'ঋণের কিস্তি গ্রহণ করা হয়েছে',
                'body_bn' => '৳'.number_format($amount, 2).' কিস্তি গ্রহণ হয়েছে। অবশিষ্ট ৳'.number_format((float) $loan->remaining_amount, 2).'।',
                'related_type' => 'loan',
                'related_id' => $loan->id,
                'channel' => 'in_app',
            ]);

            $this->deliver($loan->tenant_id, 'email', (string) $loan->user?->email, 'loan_payment', 'ঋণের কিস্তি গ্রহণ হয়েছে: ৳'.number_format($amount, 2));
            $this->deliver($loan->tenant_id, 'sms', (string) $loan->user?->phone, 'loan_payment', 'Rihal: Loan payment received BDT '.number_format($amount, 2));
        }
    }

    public function orphanPaymentRecorded(Orphan $orphan, OrphanSponsorship $sponsorship, float $amount): void
    {
        $donor = $sponsorship->donor;
        $message = 'রিহাল স্পন্সরশিপ প্রদান গ্রহণ হয়েছে: ৳'.number_format($amount, 2).' — '.$orphan->name_bn;
        $this->deliver($orphan->tenant_id, 'email', (string) $donor?->email, 'sponsorship_payment', $message);
        $this->deliver($orphan->tenant_id, 'sms', (string) $donor?->phone, 'sponsorship_payment', $message);
    }

    public function overdueLoan(Loan $loan): void
    {
        if (!$loan->user_id) return;

        $notificationExists = AppNotification::where('tenant_id', $loan->tenant_id)
            ->where('recipient_id', $loan->user_id)
            ->where('type', 'loan_overdue')
            ->where('related_type', 'loan')
            ->where('related_id', $loan->id)
            ->whereDate('created_at', today())
            ->exists();

        if (!$notificationExists) {
            AppNotification::create([
                'tenant_id' => $loan->tenant_id,
                'recipient_id' => $loan->user_id,
                'type' => 'loan_overdue',
                'title_bn' => 'ঋণের কিস্তি বকেয়া',
                'body_bn' => 'আপনার ঋণে ৳'.number_format((float) $loan->remaining_amount, 2).' বকেয়া রয়েছে।',
                'related_type' => 'loan',
                'related_id' => $loan->id,
                'channel' => 'in_app',
            ]);
        }

        $dedupePrefix = implode('|', ['loan_overdue', $loan->tenant_id, $loan->id, today()->toDateString()]);
        $this->deliver(
            $loan->tenant_id,
            'email',
            (string) $loan->user?->email,
            'loan_overdue',
            'ঋণের কিস্তি বকেয়া রয়েছে।',
            hash('sha256', $dedupePrefix.'|email')
        );
        $this->deliver(
            $loan->tenant_id,
            'sms',
            (string) $loan->user?->phone,
            'loan_overdue',
            'Rihal: Your loan payment is overdue.',
            hash('sha256', $dedupePrefix.'|sms')
        );
    }

    private function deliver(
        ?int $tenantId,
        string $channel,
        string $recipient,
        string $type,
        string $message,
        ?string $dedupeKey = null
    ): void {
        if ($recipient === '') return;

        $delivery = $dedupeKey
            ? NotificationDelivery::firstOrCreate(
                ['dedupe_key' => $dedupeKey],
                [
                    'tenant_id' => $tenantId,
                    'channel' => $channel,
                    'recipient' => $recipient,
                    'type' => $type,
                    'status' => 'pending',
                    'message' => $message,
                ]
            )
            : NotificationDelivery::create([
                'tenant_id' => $tenantId,
                'channel' => $channel,
                'recipient' => $recipient,
                'type' => $type,
                'status' => 'pending',
                'message' => $message,
            ]);

        if ($delivery->status === 'sent') return;

        $claimed = NotificationDelivery::whereKey($delivery->id)
            ->where(function ($query) {
                $query->whereIn('status', ['pending', 'failed', 'skipped'])
                    ->orWhere(function ($stale) {
                        $stale->where('status', 'sending')
                            ->where('last_attempted_at', '<', now()->subMinutes(15));
                    });
            })
            ->update([
                'status' => 'sending',
                'attempts' => DB::raw('attempts + 1'),
                'last_attempted_at' => now(),
                'provider_response' => null,
                'updated_at' => now(),
            ]);
        if ($claimed !== 1) return;
        $delivery->refresh();

        try {
            if ($channel === 'email') {
                Mail::raw($message, fn ($mail) => $mail->to($recipient)->subject('Rihal Notification'));
            } else {
                $url = config('services.sms.url');
                if (!$url) {
                    $delivery->update(['status' => 'skipped', 'provider_response' => 'SMS_URL not configured']);
                    return;
                }
                $response = Http::timeout(10)->withToken((string) config('services.sms.token'))->post($url, [
                    'to' => $recipient,
                    'message' => $message,
                ]);
                $response->throw();
                $delivery->provider_response = $response->body();
            }
            $delivery->status = 'sent';
            $delivery->sent_at = now();
            $delivery->save();
        } catch (Throwable $exception) {
            $delivery->update(['status' => 'failed', 'provider_response' => $exception->getMessage()]);
            report($exception);
        }
    }
}
