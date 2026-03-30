<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\InsurancePolicy;
use App\Models\Lead;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardStatsService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $activePolicies = InsurancePolicy::query()
            ->where('starts_at', '<=', $now->toDateString())
            ->where('ends_at', '>=', $now->toDateString())
            ->count();

        $renewalsDue30 = InsurancePolicy::query()
            ->whereBetween('ends_at', [$now->toDateString(), $now->copy()->addDays(30)->toDateString()])
            ->count();

        $renewalsDue31to60 = InsurancePolicy::query()
            ->whereBetween('ends_at', [
                $now->copy()->addDays(31)->toDateString(),
                $now->copy()->addDays(60)->toDateString(),
            ])
            ->count();

        $leadsByStage = Lead::query()
            ->select('stage', DB::raw('count(*) as c'))
            ->groupBy('stage')
            ->pluck('c', 'stage')
            ->all();

        $revenueMonth = (float) Payment::query()
            ->whereBetween('paid_at', [$monthStart, $monthEnd])
            ->sum('amount');

        $revenueToday = (float) Payment::query()
            ->whereDate('paid_at', $now->toDateString())
            ->sum('amount');

        $conversion = $this->leadConversionRate();

        $topPolicyTypes = InsurancePolicy::query()
            ->select('policy_types.name', DB::raw('count(*) as c'))
            ->join('policy_types', 'policy_types.id', '=', 'policies.policy_type_id')
            ->groupBy('policy_types.name')
            ->orderByDesc('c')
            ->limit(5)
            ->pluck('c', 'name')
            ->all();

        $totalCustomers = Customer::query()->count();
        $leadsOpen = Lead::query()->whereNotIn('stage', ['won', 'lost'])->count();

        $customersNew30 = Customer::query()->where('created_at', '>=', $now->copy()->subDays(30))->count();
        $customersNewPrev30 = Customer::query()
            ->whereBetween('created_at', [$now->copy()->subDays(60), $now->copy()->subDays(30)])
            ->count();

        $leadsNew30 = Lead::query()->where('created_at', '>=', $now->copy()->subDays(30))->count();
        $leadsNewPrev30 = Lead::query()
            ->whereBetween('created_at', [$now->copy()->subDays(60), $now->copy()->subDays(30)])
            ->count();

        $policiesNew30 = InsurancePolicy::query()->where('created_at', '>=', $now->copy()->subDays(30))->count();
        $policiesNewPrev30 = InsurancePolicy::query()
            ->whereBetween('created_at', [$now->copy()->subDays(60), $now->copy()->subDays(30)])
            ->count();

        return [
            'counts' => [
                'customers' => $totalCustomers,
                'leads_open' => $leadsOpen,
                'policies_active' => $activePolicies,
                'renewals_due_30' => $renewalsDue30,
            ],
            'kpi' => [
                'customers' => [
                    'value' => $totalCustomers,
                    ...$this->trendMeta($customersNew30, $customersNewPrev30),
                    'sparkline' => $this->dailyCreatedSeries(Customer::class, 7),
                    'hint' => 'Son 30 günde yeni müşteri',
                ],
                'leads_open' => [
                    'value' => $leadsOpen,
                    ...$this->trendMeta($leadsNew30, $leadsNewPrev30),
                    'sparkline' => $this->dailyCreatedSeries(Lead::class, 7),
                    'hint' => 'Açık lead · giriş son 30 gün',
                ],
                'policies_active' => [
                    'value' => $activePolicies,
                    ...$this->trendMeta($policiesNew30, $policiesNewPrev30),
                    'sparkline' => $this->dailyCreatedSeries(InsurancePolicy::class, 7),
                    'hint' => 'Aktif poliçe · yeni kayıt 30 gün',
                ],
                'renewals_due_30' => [
                    'value' => $renewalsDue30,
                    ...$this->trendMeta($renewalsDue30, max($renewalsDue31to60, 1)),
                    'sparkline' => $this->renewalSparkline(7),
                    'hint' => '30 gün içinde bitiş · kıyas: 31–60 gün',
                ],
            ],
            'revenue' => [
                'today' => $revenueToday,
                'month' => $revenueMonth,
            ],
            'leads_by_stage' => $leadsByStage,
            'lead_conversion_rate' => $conversion,
            'top_policy_types' => $topPolicyTypes,
            'revenue_trend' => $this->revenueTrend(),
        ];
    }

    /**
     * @return array{change_pct: float, direction: string}
     */
    private function trendMeta(int|float $current, int|float $previous): array
    {
        $previous = max((float) $previous, 0.0);
        if ($previous <= 0) {
            return [
                'change_pct' => $current > 0 ? 100.0 : 0.0,
                'direction' => $current > 0 ? 'up' : 'flat',
            ];
        }

        $raw = (($current - $previous) / $previous) * 100;
        $pct = round(abs($raw), 1);
        if (abs($raw) < 0.05) {
            return ['change_pct' => 0.0, 'direction' => 'flat'];
        }

        return [
            'change_pct' => $pct,
            'direction' => $raw > 0 ? 'up' : 'down',
        ];
    }

    /**
     * @return list<int>
     */
    private function dailyCreatedSeries(string $modelClass, int $days): array
    {
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $out[] = (int) $modelClass::query()->whereDate('created_at', $d)->count();
        }

        return $out;
    }

    /**
     * @return list<int>
     */
    private function renewalSparkline(int $days): array
    {
        $out = [];
        for ($i = 0; $i < $days; $i++) {
            $d = Carbon::today()->addDays($i);
            $out[] = (int) InsurancePolicy::query()->whereDate('ends_at', $d)->count();
        }

        return $out;
    }

    private function leadConversionRate(): float
    {
        $total = Lead::query()->count();
        if ($total === 0) {
            return 0.0;
        }
        $won = Lead::query()->where('stage', 'won')->count();

        return round(($won / $total) * 100, 1);
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    private function revenueTrend(): array
    {
        $labels = [];
        $values = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = Carbon::now()->subMonths($i)->startOfMonth();
            $labels[] = $d->translatedFormat('M Y');
            $values[] = (float) Payment::query()
                ->whereBetween('paid_at', [$d->copy()->startOfMonth(), $d->copy()->endOfMonth()])
                ->sum('amount');
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
