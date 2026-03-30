<?php

namespace App\Services;

use App\Models\Installment;
use Carbon\CarbonInterface;

class FinancePresenter
{
    /**
     * Ödendi / Bekliyor / Gecikti — veritabanındaki status’a göre türetilir.
     *
     * @return 'paid'|'pending'|'overdue'
     */
    public static function installmentBucket(Installment $i): string
    {
        if ($i->status === 'paid') {
            return 'paid';
        }

        $due = $i->due_date;
        if ($due instanceof CarbonInterface && $due->lt(now()->startOfDay())) {
            return 'overdue';
        }

        return 'pending';
    }

    public static function installmentBucketLabel(string $bucket): string
    {
        return match ($bucket) {
            'paid' => 'Ödendi',
            'pending' => 'Bekliyor',
            'overdue' => 'Gecikti',
            default => $bucket,
        };
    }

    public static function installmentBucketBadgeClass(string $bucket): string
    {
        return match ($bucket) {
            'paid' => 'bg-emerald-100 text-emerald-800 ring-emerald-600/15 dark:bg-emerald-950/50 dark:text-emerald-200',
            'pending' => 'bg-sky-100 text-sky-900 ring-sky-600/15 dark:bg-sky-950/50 dark:text-sky-200',
            'overdue' => 'bg-rose-100 text-rose-900 ring-rose-600/20 dark:bg-rose-950/50 dark:text-rose-200',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
        };
    }

    /**
     * Gecikmiş taksit için takvim günü sayısı (vade günü dahil değil, bugünden sonra sayılır).
     */
    public static function daysOverdue(Installment $i): int
    {
        if (self::installmentBucket($i) !== 'overdue') {
            return 0;
        }

        $due = $i->due_date;
        if (! $due instanceof CarbonInterface) {
            return 0;
        }

        return max(0, (int) $due->copy()->startOfDay()->diffInDays(now()->startOfDay()));
    }

    /**
     * @return list<array{text: string, severity: '3'|'7'}>
     */
    public static function overdueAlerts(Installment $i): array
    {
        $out = [];
        if (self::installmentBucket($i) !== 'overdue') {
            return $out;
        }

        $d = self::daysOverdue($i);
        if ($d >= 7) {
            $out[] = ['text' => '7 gün gecikti', 'severity' => '7'];
        } elseif ($d >= 3) {
            $out[] = ['text' => '3 gün gecikti', 'severity' => '3'];
        }

        return $out;
    }

    public static function overdueAlertPanelClass(string $severity): string
    {
        return match ($severity) {
            '7' => 'border-rose-300 bg-rose-50 text-rose-950 dark:border-rose-500/40 dark:bg-rose-950/40 dark:text-rose-100',
            '3' => 'border-rose-200 bg-rose-50/90 text-rose-900 dark:border-rose-500/30 dark:bg-rose-950/30 dark:text-rose-100',
            default => 'border-amber-200 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-950/30',
        };
    }
}
