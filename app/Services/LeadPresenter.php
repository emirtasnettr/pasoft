<?php

namespace App\Services;

use App\Models\Lead;
use Carbon\CarbonInterface;

class LeadPresenter
{
    public static function score(Lead $lead): int
    {
        if (in_array($lead->stage, ['won', 'lost'], true)) {
            return $lead->stage === 'won' ? 100 : 15;
        }

        $score = 45;
        $last = $lead->last_activity_at ?? $lead->updated_at ?? $lead->created_at;

        if ($last instanceof CarbonInterface) {
            $days = $last->diffInDays(now());
            if ($days === 0) {
                $score += 35;
            } elseif ($days <= 2) {
                $score += 22;
            } elseif ($days <= 7) {
                $score += 8;
            } elseif ($days <= 14) {
                $score -= 12;
            } else {
                $score -= 28;
            }
        } else {
            $score -= 15;
        }

        $score += match ($lead->stage) {
            'meeting' => 12,
            'proposal' => 10,
            'contacted' => 5,
            'new' => -5,
            default => 0,
        };

        if ($lead->estimated_value && (float) $lead->estimated_value >= 50000) {
            $score += 5;
        }

        return max(5, min(100, (int) round($score)));
    }

    /**
     * @return 'hot'|'warm'|'cold'
     */
    public static function temperature(Lead $lead): string
    {
        $s = self::score($lead);
        if ($s >= 72) {
            return 'hot';
        }
        if ($s >= 42) {
            return 'warm';
        }

        return 'cold';
    }

    /**
     * @return list<array{type: string, text: string}>
     */
    public static function insights(Lead $lead): array
    {
        $out = [];
        if (in_array($lead->stage, ['won', 'lost'], true)) {
            return $out;
        }

        $s = self::score($lead);
        if ($s >= 75) {
            $out[] = ['type' => 'hot', 'text' => 'Sıcak lead — öncelikli iletişim önerilir.'];
        }

        $last = $lead->last_activity_at ?? $lead->created_at;
        if ($last instanceof CarbonInterface) {
            $days = $last->diffInDays(now());
            if ($days >= 7) {
                $out[] = ['type' => 'stale', 'text' => 'Bu lead uzun süredir hareket görmüyor; kaybedilme riski yüksek.'];
            } elseif ($days >= 3) {
                $out[] = ['type' => 'stale', 'text' => '3+ gündür işlem yok — arama zamanı.'];
            }
        } else {
            $out[] = ['type' => 'stale', 'text' => 'Henüz aktivite kaydı yok.'];
        }

        if (self::temperature($lead) === 'cold' && $lead->stage === 'new') {
            $out[] = ['type' => 'cold', 'text' => 'Soğuk / yeni — ilk temas planlayın.'];
        }

        return $out;
    }
}
