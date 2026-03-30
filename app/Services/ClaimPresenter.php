<?php

namespace App\Services;

use App\Models\Claim;
use Carbon\CarbonInterface;

class ClaimPresenter
{
    public const STATUSES = [
        'opened',
        'documents_pending',
        'under_review',
        'approved',
        'paid',
        'closed',
    ];

    /**
     * @return array<string, string>
     */
    public static function statusLabels(): array
    {
        return [
            'opened' => 'Açıldı',
            'documents_pending' => 'Evrak bekleniyor',
            'under_review' => 'İncelemede',
            'approved' => 'Onaylandı',
            'paid' => 'Ödeme yapıldı',
            'closed' => 'Kapandı',
        ];
    }

    public static function statusLabel(string $status): string
    {
        return self::statusLabels()[$status] ?? $status;
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'opened' => 'bg-sky-100 text-sky-800 ring-sky-600/15 dark:bg-sky-950/50 dark:text-sky-200',
            'documents_pending' => 'bg-amber-100 text-amber-900 ring-amber-600/15 dark:bg-amber-950/50 dark:text-amber-200',
            'under_review' => 'bg-violet-100 text-violet-800 ring-violet-600/15 dark:bg-violet-950/50 dark:text-violet-200',
            'approved' => 'bg-emerald-100 text-emerald-800 ring-emerald-600/15 dark:bg-emerald-950/50 dark:text-emerald-200',
            'paid' => 'bg-indigo-100 text-indigo-800 ring-indigo-600/15 dark:bg-indigo-950/50 dark:text-indigo-200',
            'closed' => 'bg-slate-100 text-slate-700 ring-slate-500/15 dark:bg-slate-800 dark:text-slate-200',
            default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
        };
    }

    public static function activityLabel(string $action): string
    {
        return match ($action) {
            'created' => 'Hasar açıldı',
            'updated' => 'Kayıt güncellendi',
            'status_changed' => 'Durum değişti',
            'note_added' => 'Not eklendi',
            'document_added' => 'Evrak yüklendi',
            'document_removed' => 'Evrak kaldırıldı',
            default => $action,
        };
    }

    /**
     * Liste satırı için (documents_count ile, N+1 olmadan).
     *
     * @return list<array{type: string, text: string}>
     */
    public static function insightBadgesForList(Claim $claim, int $documentsCount): array
    {
        $out = [];

        if ($claim->status === 'documents_pending' && $documentsCount === 0) {
            $out[] = ['type' => 'warning', 'text' => 'Evrak eksik'];
        }

        $last = $claim->last_activity_at ?? $claim->updated_at;
        if ($claim->status !== 'closed' && $last instanceof CarbonInterface && $last->lt(now()->subDays(3))) {
            $out[] = ['type' => 'stale', 'text' => '3+ gündür işlem yok'];
        }

        if ($claim->status === 'approved') {
            $out[] = ['type' => 'payment', 'text' => 'Ödeme bekleniyor'];
        }

        $amount = (float) ($claim->amount ?? 0);
        $paid = (float) ($claim->paid_amount ?? 0);
        if ($amount > 0 && $paid < $amount && in_array($claim->status, ['approved', 'paid'], true)) {
            $out[] = ['type' => 'finance', 'text' => 'Tutarın bir kısmı ödenmedi'];
        }

        return $out;
    }

    /**
     * @return list<array{type: string, text: string}>
     */
    public static function insightBadges(Claim $claim): array
    {
        return self::insightBadgesForList($claim, (int) $claim->documents()->count());
    }
}
