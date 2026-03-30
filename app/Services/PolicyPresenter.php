<?php

namespace App\Services;

use App\Models\InsurancePolicy;
use Carbon\CarbonInterface;

class PolicyPresenter
{
    public const URGENCY_ACTIVE = 'active';

    public const URGENCY_WARN_7 = 'warn_7';

    public const URGENCY_WARN_3 = 'warn_3';

    public const URGENCY_CRITICAL_1 = 'critical_1';

    public const URGENCY_EXPIRED = 'expired';

    public const URGENCY_UNKNOWN = 'unknown';

    public static function daysLeft(?CarbonInterface $endsAt): ?int
    {
        if (! $endsAt) {
            return null;
        }

        $today = now()->startOfDay();
        $end = $endsAt->copy()->startOfDay();

        if ($end->lt($today)) {
            return null;
        }

        return (int) $today->diffInDays($end);
    }

    /**
     * Kalan güne göre aciliyet anahtarı (UI renkleri ile eşleşir).
     */
    public static function urgency(?CarbonInterface $endsAt): string
    {
        if (! $endsAt) {
            return self::URGENCY_UNKNOWN;
        }

        $today = now()->startOfDay();
        $end = $endsAt->copy()->startOfDay();

        if ($end->lt($today)) {
            return self::URGENCY_EXPIRED;
        }

        $daysLeft = (int) $today->diffInDays($end);

        if ($daysLeft > 7) {
            return self::URGENCY_ACTIVE;
        }
        if ($daysLeft >= 4) {
            return self::URGENCY_WARN_7;
        }
        if ($daysLeft >= 2) {
            return self::URGENCY_WARN_3;
        }

        return self::URGENCY_CRITICAL_1;
    }

    public static function renewalLabelTr(string $status): string
    {
        return match ($status) {
            'renewed' => 'Yenilendi',
            'pending_renewal' => 'Bekliyor',
            'not_renewed' => 'Yenilenmedi',
            'active' => 'Devam ediyor',
            default => $status,
        };
    }

    /**
     * @return list<array{type: string, text: string}>
     */
    public static function insightBadges(InsurancePolicy $policy): array
    {
        $out = [];
        $urgency = self::urgency($policy->ends_at);
        $days = self::daysLeft($policy->ends_at);

        if ($urgency === self::URGENCY_EXPIRED) {
            $out[] = ['type' => 'danger', 'text' => 'Süresi dolmuş — acil yenileme veya kapatma.'];
        } elseif ($urgency === self::URGENCY_CRITICAL_1) {
            $out[] = ['type' => 'danger', 'text' => 'Bu poliçe 1 gün içinde bitiyor veya bugün bitiyor.'];
        } elseif ($urgency === self::URGENCY_WARN_3) {
            $out[] = ['type' => 'warning', 'text' => '3 gün içinde bitiş — yenileme başlatın.'];
        }

        if ($policy->renewal_status === 'not_renewed') {
            $out[] = ['type' => 'stale', 'text' => 'Yenilenmedi olarak işaretli.'];
        }

        if ($policy->premium_payment_status === 'pending' || $policy->premium_payment_status === 'partial') {
            $out[] = ['type' => 'finance', 'text' => 'Prim tahsilatı tamamlanmadı.'];
        }

        if ($days !== null && $days <= 14 && $days >= 0 && $policy->renewal_status === 'active' && $urgency !== self::URGENCY_EXPIRED) {
            $out[] = ['type' => 'info', 'text' => 'Müşteri kaybı riski — iletişim önerilir.'];
        }

        return $out;
    }

    /**
     * @return array<string, string>
     */
    public static function urgencyTailwind(string $urgency): array
    {
        return match ($urgency) {
            self::URGENCY_ACTIVE => [
                'badge' => 'bg-green-100 text-green-700 dark:bg-green-950/50 dark:text-green-300',
                'row' => '',
            ],
            self::URGENCY_WARN_7 => [
                'badge' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950/50 dark:text-yellow-200',
                'row' => '',
            ],
            self::URGENCY_WARN_3 => [
                'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-950/50 dark:text-orange-200',
                'row' => '',
            ],
            self::URGENCY_CRITICAL_1 => [
                'badge' => 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-200',
                'row' => 'ring-1 ring-red-200 dark:ring-red-900/50',
            ],
            self::URGENCY_EXPIRED => [
                'badge' => 'bg-red-200 text-red-900 dark:bg-red-950/60 dark:text-red-100',
                'row' => 'ring-2 ring-red-300 dark:ring-red-800',
            ],
            default => [
                'badge' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
                'row' => '',
            ],
        };
    }
}
