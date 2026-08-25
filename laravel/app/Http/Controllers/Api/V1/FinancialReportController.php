<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Loan;
use App\Models\Orphan;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialReportController
{
    public function loans(Request $request): StreamedResponse
    {
        $rows = Loan::where('tenant_id', $request->user()->tenant_id)
            ->with('user:id,name_bn,name_en')->orderBy('id')->get();

        return $this->csv('loans-'.today()->format('Y-m-d').'.csv', [
            'ID', 'শিরোনাম', 'ঋণগ্রহীতা', 'মূলধন', 'সুদ', 'মোট প্রদেয়', 'পরিশোধ', 'অবশিষ্ট', 'অবস্থা', 'শেষ তারিখ'
        ], $rows->map(fn ($loan) => [
            $loan->id, $loan->title_bn, $loan->user?->name_bn ?? $loan->user?->name_en,
            $loan->principal_amount, $loan->interest_rate, $loan->total_due,
            $loan->total_paid, $loan->remaining_amount, $loan->status,
            optional($loan->due_date)->format('Y-m-d'),
        ]));
    }

    public function orphans(Request $request): StreamedResponse
    {
        $rows = Orphan::where('tenant_id', $request->user()->tenant_id)
            ->with(['sponsors:id,name_bn,name_en'])->orderBy('id')->get();

        return $this->csv('orphans-'.today()->format('Y-m-d').'.csv', [
            'ID', 'নাম', 'অভিভাবক', 'ফোন', 'শ্রেণি', 'মাসিক অঙ্গীকার', 'মোট সহায়তা', 'স্পন্সর', 'অবস্থা'
        ], $rows->map(fn ($orphan) => [
            $orphan->id, $orphan->name_bn, $orphan->guardian_name_bn, $orphan->guardian_phone,
            $orphan->class_id, $orphan->monthly_amount, $orphan->total_sponsored,
            $orphan->sponsors->pluck('name_bn')->filter()->join(' | '), $orphan->sponsorship_status,
        ]));
    }

    private function csv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, array_map([$this, 'safeCell'], $headers));
            foreach ($rows as $row) fputcsv($output, array_map([$this, 'safeCell'], $row));
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function safeCell(mixed $value): mixed
    {
        if (is_string($value) && preg_match('/^[=+\-@\t\r]/u', $value)) {
            return "'".$value;
        }
        return $value;
    }
}
