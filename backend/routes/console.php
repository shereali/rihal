<?php

use App\Models\Loan;
use App\Services\FinancialNotificationService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment('Rihal Madrasha Management Platform');
})->purpose('Display an inspiring quote');

Artisan::command('financial:send-overdue-notifications', function (FinancialNotificationService $notifications) {
    $count = 0;
    Loan::with(['user', 'installments'])
        ->where('status', 'active')
        ->where('remaining_amount', '>', 0)
        ->where(function ($query) {
            $query->whereDate('due_date', '<', today())
                ->orWhereHas('installments', fn ($installments) => $installments
                    ->whereIn('status', ['pending', 'partial'])
                    ->whereDate('due_date', '<', today()));
        })
        ->chunkById(100, function ($loans) use ($notifications, &$count) {
            foreach ($loans as $loan) {
                $loan->installments()->whereIn('status', ['pending', 'partial'])->whereDate('due_date', '<', today())->update(['status' => 'overdue']);
                $notifications->overdueLoan($loan);
                $count++;
            }
        });
    $this->info("{$count} overdue loan notification(s) dispatched.");
})->purpose('Mark overdue installments and send in-app/email/SMS notifications');

Schedule::command('financial:send-overdue-notifications')->dailyAt('08:00')->withoutOverlapping();
