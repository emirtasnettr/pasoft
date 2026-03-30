<?php

namespace App\Services;

use App\Models\InsurancePolicy;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportAnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function build(Request $request): array
    {
        [$start, $end, $periodKey, $label] = $this->resolveRange($request);

        [$prevStart, $prevEnd] = $this->previousRange($start, $end);

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        $prevMonthStart = $monthStart->copy()->subMonth()->startOfMonth();
        $prevMonthEnd = $monthStart->copy()->subMonth()->endOfMonth();

        $periodRevenue = $this->paymentSumBetween($start, $end);
        $prevPeriodRevenue = $this->paymentSumBetween($prevStart, $prevEnd);

        $thisMonthRevenue = $this->paymentSumBetween($monthStart, $monthEnd);
        $lastMonthRevenue = $this->paymentSumBetween($prevMonthStart, $prevMonthEnd);

        $periodCommission = $this->commissionWrittenBetween($start, $end);
        $prevPeriodCommission = $this->commissionWrittenBetween($prevStart, $prevEnd);

        $avgPolicy = $this->avgPremiumCreatedBetween($start, $end);
        $prevAvgPolicy = $this->avgPremiumCreatedBetween($prevStart, $prevEnd);

        $renewalRate = $this->renewalRateBetween($start, $end);
        $prevRenewalRate = $this->renewalRateBetween($prevStart, $prevEnd);

        $leadConversion = $this->leadConversionBetween($start, $end);
        $prevLeadConversion = $this->leadConversionBetween($prevStart, $prevEnd);

        $kpis = [
            $this->kpiMeta('Dönem tahsilatı', $periodRevenue, $prevPeriodRevenue, true, '₺'),
            $this->kpiMeta('Bu ay tahsilat', $thisMonthRevenue, $lastMonthRevenue, true, '₺'),
            $this->kpiMeta('Komisyon (yeni iş)', $periodCommission, $prevPeriodCommission, true, '₺'),
            $this->kpiMeta('Ortalama poliçe primi', $avgPolicy, $prevAvgPolicy, true, '₺'),
            $this->kpiMeta('Yenileme oranı', $renewalRate, $prevRenewalRate, true, '%', true),
            $this->kpiMeta('Lead dönüşüm (won / kapanan)', $leadConversion, $prevLeadConversion, true, '%', true),
        ];

        $trends = $this->monthlyTrends(12);

        $repPerformance = $this->repPerformance($start, $end);

        $renewalReport = [
            'renewed_in_period' => InsurancePolicy::query()
                ->where('renewal_status', 'renewed')
                ->whereBetween('updated_at', [$start, $end])
                ->count(),
            'not_renewed_open' => InsurancePolicy::query()
                ->where('renewal_status', 'not_renewed')
                ->whereNull('deleted_at')
                ->count(),
            'upcoming_30d' => InsurancePolicy::query()
                ->whereNotNull('ends_at')
                ->whereDate('ends_at', '>=', Carbon::today()->toDateString())
                ->whereDate('ends_at', '<=', Carbon::today()->addDays(30)->toDateString())
                ->where('renewal_status', '!=', 'renewed')
                ->count(),
        ];

        $revenueByType = $this->revenueByPolicyType($start, $end);

        $funnel = $this->leadFunnel($start, $end);

        $topRep = $repPerformance[0] ?? null;

        $insights = $this->insights(
            $periodRevenue,
            $prevPeriodRevenue,
            $renewalRate,
            $prevRenewalRate,
            $leadConversion,
            $prevLeadConversion,
            $topRep,
            $renewalReport['upcoming_30d']
        );

        return [
            'period' => $periodKey,
            'period_label' => $label,
            'range' => [
                'from' => $start->toIso8601String(),
                'to' => $end->toIso8601String(),
            ],
            'filters' => [
                'period' => $periodKey,
                'from' => $request->input('from'),
                'to' => $request->input('to'),
            ],
            'kpis' => $kpis,
            'trends' => $trends,
            'rep_performance' => $repPerformance,
            'renewal_report' => $renewalReport,
            'revenue_by_type' => $revenueByType,
            'funnel' => $funnel,
            'insights' => $insights,
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string, 3: string}
     */
    private function resolveRange(Request $request): array
    {
        $period = $request->string('period', 'month')->toString();

        if ($period === 'today') {
            $s = Carbon::today()->startOfDay();
            $e = Carbon::today()->endOfDay();

            return [$s, $e, 'today', 'Bugün'];
        }

        if ($period === 'week') {
            $s = Carbon::now()->startOfWeek();
            $e = Carbon::now()->endOfWeek();

            return [$s, $e, 'week', 'Bu hafta'];
        }

        if ($period === 'custom' && $request->filled('from') && $request->filled('to')) {
            try {
                $s = Carbon::parse($request->input('from'))->startOfDay();
                $e = Carbon::parse($request->input('to'))->endOfDay();
                if ($e->lt($s)) {
                    [$s, $e] = [$e->copy()->startOfDay(), $s->copy()->endOfDay()];
                }

                return [$s, $e, 'custom', $s->format('d.m.Y').' – '.$e->format('d.m.Y')];
            } catch (\Throwable) {
                // fallthrough
            }
        }

        $s = Carbon::now()->startOfMonth();
        $e = Carbon::now()->endOfMonth();

        return [$s, $e, 'month', 'Bu ay'];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function previousRange(Carbon $start, Carbon $end): array
    {
        $days = max(1, (int) $start->diffInDays($end) + 1);
        $prevEnd = $start->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($days - 1)->startOfDay();

        return [$prevStart, $prevEnd];
    }

    private function paymentSumBetween(Carbon $start, Carbon $end): float
    {
        return (float) Payment::query()
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount');
    }

    private function commissionWrittenBetween(Carbon $start, Carbon $end): float
    {
        return (float) InsurancePolicy::query()
            ->whereBetween('created_at', [$start, $end])
            ->sum('commission_amount');
    }

    private function avgPremiumCreatedBetween(Carbon $start, Carbon $end): float
    {
        $avg = InsurancePolicy::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('premium_amount', '>', 0)
            ->avg('premium_amount');

        return round((float) ($avg ?? 0), 2);
    }

    private function renewalRateBetween(Carbon $start, Carbon $end): float
    {
        $due = InsurancePolicy::query()
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [$start->toDateString(), $end->toDateString()])
            ->count();

        if ($due === 0) {
            return 0.0;
        }

        $renewed = InsurancePolicy::query()
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [$start->toDateString(), $end->toDateString()])
            ->where('renewal_status', 'renewed')
            ->count();

        return round(100 * $renewed / $due, 1);
    }

    private function leadConversionBetween(Carbon $start, Carbon $end): float
    {
        $closed = Lead::query()
            ->whereBetween('updated_at', [$start, $end])
            ->whereIn('stage', ['won', 'lost'])
            ->count();

        if ($closed === 0) {
            return 0.0;
        }

        $won = Lead::query()
            ->whereBetween('updated_at', [$start, $end])
            ->where('stage', 'won')
            ->count();

        return round(100 * $won / $closed, 1);
    }

    /**
     * @return array<string, mixed>
     */
    private function kpiMeta(
        string $title,
        float $current,
        float $previous,
        bool $higherIsBetter,
        string $suffix,
        bool $isPct = false,
    ): array {
        $meta = $this->trendMeta($current, $previous, $higherIsBetter);

        return [
            'title' => $title,
            'value' => $isPct ? $current : round($current, 2),
            'suffix' => $suffix,
            'change_pct' => $meta['change_pct'],
            'direction' => $meta['direction'],
        ];
    }

    /**
     * @return array{change_pct: float, direction: string}
     */
    private function trendMeta(float $current, float $previous, bool $higherIsBetter): array
    {
        $previous = max($previous, 0.0);
        if ($previous <= 0.0001) {
            $dir = $current > 0 ? 'up' : 'flat';
            if (! $higherIsBetter && $current > 0) {
                $dir = 'down';
            }

            return [
                'change_pct' => $current > 0 ? 100.0 : 0.0,
                'direction' => $dir,
            ];
        }

        $raw = (($current - $previous) / $previous) * 100;
        $pct = round(abs($raw), 1);
        if (abs($raw) < 0.05) {
            return ['change_pct' => 0.0, 'direction' => 'flat'];
        }

        $up = $raw > 0;
        if (! $higherIsBetter) {
            $up = ! $up;
        }

        return [
            'change_pct' => $pct,
            'direction' => $up ? 'up' : 'down',
        ];
    }

    /**
     * @return array{revenue: array{labels: list<string>, values: list<float>}, commission: array{labels: list<string>, values: list<float>}, policies: array{labels: list<string>, values: list<int>}}
     */
    private function monthlyTrends(int $months): array
    {
        $labels = [];
        $revenue = [];
        $commission = [];
        $policies = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $d = Carbon::now()->subMonths($i)->startOfMonth();
            $labels[] = $d->translatedFormat('M Y');
            $revenue[] = (float) Payment::query()
                ->whereBetween('paid_at', [$d->copy()->startOfMonth(), $d->copy()->endOfMonth()])
                ->sum('amount');
            $commission[] = (float) InsurancePolicy::query()
                ->whereBetween('created_at', [$d->copy()->startOfMonth(), $d->copy()->endOfMonth()])
                ->sum('commission_amount');
            $policies[] = (int) InsurancePolicy::query()
                ->whereBetween('created_at', [$d->copy()->startOfMonth(), $d->copy()->endOfMonth()])
                ->count();
        }

        return [
            'revenue' => ['labels' => $labels, 'values' => $revenue],
            'commission' => ['labels' => $labels, 'values' => $commission],
            'policies' => ['labels' => $labels, 'values' => $policies],
        ];
    }

    /**
     * @return list<array{user_id: int|null, name: string, revenue: float}>
     */
    private function repPerformance(Carbon $start, Carbon $end): array
    {
        $rows = Payment::query()
            ->join('policies', 'payments.policy_id', '=', 'policies.id')
            ->whereBetween('payments.paid_at', [$start, $end])
            ->whereNull('policies.deleted_at')
            ->whereNotNull('payments.policy_id')
            ->selectRaw('policies.owner_user_id as uid, sum(payments.amount) as total')
            ->groupBy('policies.owner_user_id')
            ->orderByDesc('total')
            ->get();

        $users = User::query()
            ->whereIn('id', $rows->pluck('uid')->filter()->all())
            ->pluck('name', 'id');

        $out = [];
        foreach ($rows as $r) {
            $uid = $r->uid ? (int) $r->uid : null;
            $out[] = [
                'user_id' => $uid,
                'name' => $uid ? ($users[$uid] ?? 'Temsilci #'.$uid) : 'Atanmamış',
                'revenue' => round((float) $r->total, 2),
            ];
        }

        return $out;
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    private function revenueByPolicyType(Carbon $start, Carbon $end): array
    {
        $q = Payment::query()
            ->join('policies', 'payments.policy_id', '=', 'policies.id')
            ->join('policy_types', 'policies.policy_type_id', '=', 'policy_types.id')
            ->whereBetween('payments.paid_at', [$start, $end])
            ->whereNull('policies.deleted_at')
            ->whereNotNull('payments.policy_id')
            ->selectRaw('policy_types.name as n, sum(payments.amount) as t')
            ->groupBy('policy_types.name')
            ->orderByDesc('t');

        $map = $q->pluck('t', 'n')->all();

        return [
            'labels' => array_keys($map),
            'values' => array_map(fn ($v) => round((float) $v, 2), array_values($map)),
        ];
    }

    /**
     * @return array{stages: list<array{key: string, label: string, count: int}>}
     */
    private function leadFunnel(Carbon $start, Carbon $end): array
    {
        $base = Lead::query()->whereBetween('created_at', [$start, $end]);

        $entered = (clone $base)->count();
        $reachedQuote = (clone $base)->whereIn('stage', ['proposal', 'meeting', 'won'])->count();
        $sale = (clone $base)->where('stage', 'won')->count();

        return [
            'stages' => [
                ['key' => 'lead', 'label' => 'Dönemde giren lead', 'count' => $entered],
                ['key' => 'quote', 'label' => 'Teklif veya sonrası', 'count' => $reachedQuote],
                ['key' => 'sale', 'label' => 'Satış (kazanıldı)', 'count' => $sale],
            ],
        ];
    }

    /**
     * @return list<array{type: string, text: string}>
     */
    private function insights(
        float $rev,
        float $prevRev,
        float $renewRate,
        float $prevRenew,
        float $conv,
        float $prevConv,
        ?array $topRep,
        int $upcoming,
    ): array {
        $out = [];

        if ($prevRev > 0 && $rev > $prevRev * 1.02) {
            $out[] = ['type' => 'positive', 'text' => 'Dönem tahsilatları önceki döneme göre güçlü.'];
        } elseif ($prevRev > 0 && $rev < $prevRev * 0.98) {
            $out[] = ['type' => 'negative', 'text' => 'Tahsilatlar önceki döneme göre geriledi; pipeline ve tahsilatı gözden geçirin.'];
        }

        if ($prevRenew > 0 && $renewRate < $prevRenew - 1) {
            $out[] = ['type' => 'warning', 'text' => 'Yenileme oranı düşüş eğiliminde; yaklaşan bitişleri önceliklendirin.'];
        } elseif ($renewRate > $prevRenew + 1) {
            $out[] = ['type' => 'positive', 'text' => 'Yenileme performansı iyileşiyor.'];
        }

        if ($prevConv > 0 && $conv > $prevConv + 2) {
            $out[] = ['type' => 'positive', 'text' => 'Lead dönüşüm oranı yükseldi.'];
        } elseif ($prevConv > 0 && $conv < $prevConv - 2) {
            $out[] = ['type' => 'negative', 'text' => 'Lead dönüşümü zayıfladı; teklif ve takip süreçlerini kontrol edin.'];
        }

        if ($topRep) {
            $out[] = [
                'type' => 'info',
                'text' => 'En yüksek tahsilat: '.$topRep['name'].' (₺'.number_format($topRep['revenue'], 2, ',', '.').').',
            ];
        }

        if ($upcoming > 0) {
            $out[] = [
                'type' => 'warning',
                'text' => 'Önümüzdeki 30 günde '.$upcoming.' poliçe bitiyor — yenileme fırsatı.',
            ];
        }

        if ($out === []) {
            $out[] = ['type' => 'info', 'text' => 'Seçili dönem için metrikler stabil görünüyor.'];
        }

        return $out;
    }
}
