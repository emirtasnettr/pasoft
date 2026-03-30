<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Customer;
use App\Models\Installment;
use App\Models\InsurancePolicy;
use App\Models\Payment;
use App\Services\FinancePresenter;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $today = now()->toDateString();

        $customerQuery = Customer::query()
            ->where(function ($w): void {
                $w->whereHas('payments')
                    ->orWhereHas('policies');
            })
            ->when($request->filled('q'), function ($q) use ($request): void {
                $s = '%'.$request->string('q')->toString().'%';
                $q->where(function ($w) use ($s): void {
                    $w->where('name', 'like', $s)
                        ->orWhere('company_name', 'like', $s)
                        ->orWhere('email', 'like', $s)
                        ->orWhere('phone', 'like', $s);
                });
            })
            ->when($request->string('status')->toString() === 'overdue', function ($q) use ($today): void {
                $q->whereHas('policies.installments', function ($iq) use ($today): void {
                    $iq->where('installments.status', '!=', 'paid')
                        ->whereDate('installments.due_date', '<', $today);
                });
            })
            ->when($request->string('status')->toString() === 'pending', function ($q) use ($today): void {
                $q->whereHas('policies.installments', fn ($iq) => $iq->where('installments.status', '!=', 'paid'))
                    ->whereDoesntHave('policies.installments', function ($iq) use ($today): void {
                        $iq->where('installments.status', '!=', 'paid')
                            ->whereDate('installments.due_date', '<', $today);
                    });
            })
            ->orderByRaw('COALESCE(NULLIF(company_name, ""), name)');

        $customerSummaries = $customerQuery->paginate(15)->withQueryString();

        $pageIds = $customerSummaries->getCollection()->pluck('id');
        $bulk = $this->aggregateForCustomerIds($pageIds, $today);
        $customerSummaries->setCollection(
            $customerSummaries->getCollection()->map(
                fn (Customer $c) => $this->mapCustomerFinanceRow($c, $bulk[$c->id] ?? $this->emptyAggregateRow()),
            ),
        );

        $customerDetail = null;
        if ($request->filled('customer')) {
            $cid = $request->integer('customer');
            $cust = Customer::query()->whereKey($cid)->first();
            if ($cust) {
                $customerDetail = $this->formatCustomerFinanceDetail($cust);
            }
        }

        $payments = Payment::query()
            ->with(['customer:id,name,company_name', 'policy:id,policy_number', 'recordedBy:id,name'])
            ->when($request->filled('from'), fn ($q) => $q->whereDate('paid_at', '>=', $request->date('from')->format('Y-m-d')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('paid_at', '<=', $request->date('to')->format('Y-m-d')))
            ->when($request->filled('q'), function ($q) use ($request): void {
                $s = '%'.$request->string('q')->toString().'%';
                $q->whereHas('customer', function ($c) use ($s): void {
                    $c->where('name', 'like', $s)
                        ->orWhere('company_name', 'like', $s);
                });
            })
            ->orderByDesc('paid_at')
            ->paginate(12, ['*'], 'payments_page')
            ->withQueryString();

        return Inertia::render('Payments/Index', [
            'customerSummaries' => $customerSummaries,
            'customerDetail' => $customerDetail,
            'payments' => $payments,
            'kpi' => $this->financeKpi($today),
            'cashflow' => $this->cashflowDualSeries($request),
            'filters' => [
                'q' => $request->string('q')->toString(),
                'status' => $request->string('status')->toString(),
                'from' => $request->input('from'),
                'to' => $request->input('to'),
                'customer' => $request->input('customer'),
            ],
        ]);
    }

    /**
     * @return array<string, float|int>
     */
    private function financeKpi(string $today): array
    {
        $pending = (float) Installment::query()
            ->where('status', '!=', 'paid')
            ->sum('amount');

        $overdue = (float) Installment::query()
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '<', $today)
            ->sum('amount');

        return [
            'total_collected' => (float) Payment::query()->sum('amount'),
            'this_month' => (float) Payment::query()
                ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount'),
            'pending_payment' => $pending,
            'overdue_payment' => $overdue,
        ];
    }

    /**
     * @return array{daily: array{labels: list<string>, values: list<float>}, monthly: array{labels: list<string>, values: list<float>}}
     */
    private function cashflowDualSeries(Request $request): array
    {
        $from = $request->date('from');
        $to = $request->date('to');

        $dailyLabels = [];
        $dailyValues = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = now()->subDays($i)->startOfDay();
            $dailyLabels[] = $d->format('d.m');
            $dailyValues[] = $this->paymentSumForDay($d, $from, $to);
        }

        $monthlyLabels = [];
        $monthlyValues = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->copy()->subMonths($i)->startOfMonth();
            $monthlyLabels[] = $m->translatedFormat('M Y');
            $monthlyValues[] = $this->paymentSumForMonth($m, $from, $to);
        }

        return [
            'daily' => ['labels' => $dailyLabels, 'values' => $dailyValues],
            'monthly' => ['labels' => $monthlyLabels, 'values' => $monthlyValues],
        ];
    }

    private function paymentSumForDay(Carbon $day, ?Carbon $from, ?Carbon $to): float
    {
        if ($from && $day->lt($from->copy()->startOfDay())) {
            return 0.0;
        }
        if ($to && $day->gt($to->copy()->endOfDay())) {
            return 0.0;
        }

        return (float) Payment::query()->whereDate('paid_at', $day->toDateString())->sum('amount');
    }

    private function paymentSumForMonth(Carbon $monthStart, ?Carbon $from, ?Carbon $to): float
    {
        $rangeStart = $monthStart->copy()->startOfMonth();
        $rangeEnd = $monthStart->copy()->endOfMonth();

        if ($from) {
            $fs = $from->copy()->startOfDay();
            if ($rangeEnd->lt($fs)) {
                return 0.0;
            }
            if ($rangeStart->lt($fs)) {
                $rangeStart = $fs;
            }
        }

        if ($to) {
            $te = $to->copy()->endOfDay();
            if ($rangeStart->gt($te)) {
                return 0.0;
            }
            if ($rangeEnd->gt($te)) {
                $rangeEnd = $te;
            }
        }

        return (float) Payment::query()
            ->whereBetween('paid_at', [$rangeStart, $rangeEnd])
            ->sum('amount');
    }

    /**
     * @return array<int, array<string, float|int>>
     */
    private function aggregateForCustomerIds(Collection $ids, string $today): array
    {
        if ($ids->isEmpty()) {
            return [];
        }

        $unpaid = Installment::query()
            ->join('policies', 'policies.id', '=', 'installments.policy_id')
            ->whereIn('policies.customer_id', $ids)
            ->where('installments.status', '!=', 'paid')
            ->groupBy('policies.customer_id')
            ->selectRaw(
                'policies.customer_id as customer_id, SUM(installments.amount) as balance_due, SUM(CASE WHEN installments.due_date < ? THEN installments.amount ELSE 0 END) as overdue_amount, SUM(CASE WHEN installments.due_date < ? THEN 1 ELSE 0 END) as overdue_count, MIN(CASE WHEN installments.due_date < ? THEN installments.due_date END) as oldest_overdue_due',
                [$today, $today, $today],
            )
            ->get()
            ->keyBy('customer_id');

        $scheduled = Installment::query()
            ->join('policies', 'policies.id', '=', 'installments.policy_id')
            ->whereIn('policies.customer_id', $ids)
            ->groupBy('policies.customer_id')
            ->selectRaw('policies.customer_id as customer_id, SUM(installments.amount) as total_scheduled')
            ->get()
            ->keyBy('customer_id');

        $paidSums = Payment::query()
            ->whereIn('customer_id', $ids)
            ->groupBy('customer_id')
            ->selectRaw('customer_id, SUM(amount) as total_paid')
            ->pluck('total_paid', 'customer_id');

        $premiumByCustomer = InsurancePolicy::query()
            ->whereIn('customer_id', $ids)
            ->groupBy('customer_id')
            ->selectRaw('customer_id, COALESCE(SUM(premium_amount), 0) as total_premium')
            ->pluck('total_premium', 'customer_id');

        $out = [];
        foreach ($ids as $id) {
            $u = $unpaid->get($id);
            $s = $scheduled->get($id);
            $out[(int) $id] = [
                'balance_due' => (float) ($u->balance_due ?? 0),
                'overdue_amount' => (float) ($u->overdue_amount ?? 0),
                'overdue_count' => (int) ($u->overdue_count ?? 0),
                'oldest_overdue_due' => $u && $u->oldest_overdue_due ? (string) $u->oldest_overdue_due : null,
                'total_paid' => (float) ($paidSums[$id] ?? 0),
                'total_scheduled' => (float) ($s->total_scheduled ?? 0),
                'total_premium' => (float) ($premiumByCustomer[$id] ?? 0),
            ];
        }

        return $out;
    }

    /**
     * @return array<string, float|int>
     */
    private function emptyAggregateRow(): array
    {
        return [
            'balance_due' => 0.0,
            'overdue_amount' => 0.0,
            'overdue_count' => 0,
            'oldest_overdue_due' => null,
            'total_paid' => 0.0,
            'total_scheduled' => 0.0,
            'total_premium' => 0.0,
        ];
    }

    /**
     * @param  array<string, float|int>  $a
     * @return array<string, mixed>
     */
    private function mapCustomerFinanceRow(Customer $c, array $a): array
    {
        $premium = (float) $a['total_premium'];
        $scheduled = (float) $a['total_scheduled'];
        $paid = (float) $a['total_paid'];
        $balanceDue = (float) $a['balance_due'];
        $overdueAmount = (float) $a['overdue_amount'];
        $overdueCount = (int) $a['overdue_count'];

        $totalDebt = $scheduled > 0 ? $scheduled : $premium;
        $remaining = $scheduled > 0 ? $balanceDue : max(0, $premium - $paid);

        $rowAlerts = [];
        if ($overdueAmount > 0) {
            $oldest = $a['oldest_overdue_due'] ?? null;
            if ($oldest) {
                $days = Carbon::parse($oldest)->startOfDay()->diffInDays(now()->startOfDay());
                if ($days >= 7) {
                    $rowAlerts[] = ['text' => '7 gün gecikti', 'severity' => '7'];
                } elseif ($days >= 3) {
                    $rowAlerts[] = ['text' => '3 gün gecikti', 'severity' => '3'];
                } else {
                    $rowAlerts[] = ['text' => 'Vadesi geçmiş taksit', 'severity' => '3'];
                }
            } else {
                $rowAlerts[] = ['text' => 'Geciken ödeme', 'severity' => '7'];
            }
        }

        return [
            'id' => $c->id,
            'display_name' => $c->displayName(),
            'phone' => $c->phone,
            'email' => $c->email,
            'total_debt' => $totalDebt,
            'paid' => $paid,
            'remaining' => $remaining,
            'overdue_amount' => $overdueAmount,
            'overdue_count' => $overdueCount,
            'row_alerts' => $rowAlerts,
            'has_installments' => $scheduled > 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatCustomerFinanceDetail(Customer $customer): array
    {
        $customer->load([
            'policies' => fn ($q) => $q->orderByDesc('id')->with([
                'installments' => fn ($iq) => $iq->orderBy('sequence'),
            ]),
        ]);

        $today = now()->toDateString();
        $bulk = $this->aggregateForCustomerIds(collect([$customer->id]), $today);
        $a = $bulk[$customer->id] ?? $this->emptyAggregateRow();

        $scheduled = (float) $a['total_scheduled'];
        $premium = (float) $a['total_premium'];
        $paid = (float) $a['total_paid'];
        $balanceDue = (float) $a['balance_due'];
        $totalDebt = $scheduled > 0 ? $scheduled : $premium;
        $remaining = $scheduled > 0 ? $balanceDue : max(0, $premium - $paid);

        $installments = collect();
        foreach ($customer->policies as $pol) {
            foreach ($pol->installments as $inst) {
                $bucket = FinancePresenter::installmentBucket($inst);
                $installments->push([
                    'id' => $inst->id,
                    'policy_id' => $pol->id,
                    'policy_number' => $pol->policy_number,
                    'sequence' => $inst->sequence,
                    'due_date' => $inst->due_date->toDateString(),
                    'amount' => $inst->amount,
                    'status' => $inst->status,
                    'bucket' => $bucket,
                    'bucket_label' => FinancePresenter::installmentBucketLabel($bucket),
                    'bucket_badge_class' => FinancePresenter::installmentBucketBadgeClass($bucket),
                    'days_overdue' => FinancePresenter::daysOverdue($inst),
                    'alerts' => FinancePresenter::overdueAlerts($inst),
                ]);
            }
        }

        $installments = $installments->sortBy('due_date')->values()->all();

        $notes = $customer->notes()
            ->with('user:id,name')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'body' => $n->body,
                'created_at' => $n->created_at->toIso8601String(),
                'user' => $n->user,
            ]);

        $policiesBrief = $customer->policies->map(fn (InsurancePolicy $p) => [
            'id' => $p->id,
            'policy_number' => $p->policy_number,
            'premium_amount' => $p->premium_amount,
        ]);

        return [
            'id' => $customer->id,
            'display_name' => $customer->displayName(),
            'phone' => $customer->phone,
            'email' => $customer->email,
            'summary' => [
                'total_debt' => $totalDebt,
                'paid' => $paid,
                'remaining' => $remaining,
                'overdue_amount' => (float) $a['overdue_amount'],
            ],
            'policies' => $policiesBrief,
            'installments' => $installments,
            'notes' => $notes,
        ];
    }

    public function create(Request $request): Response
    {
        $prefillCustomerId = $request->integer('customer_id') ?: null;

        return Inertia::render('Payments/Create', [
            'customers' => Customer::query()->orderBy('name')->limit(500)->get(['id', 'name', 'company_name']),
            'policies' => InsurancePolicy::query()
                ->with([
                    'customer:id,name,company_name',
                    'installments' => fn ($q) => $q->where('status', '!=', 'paid')->orderBy('sequence'),
                ])
                ->orderByDesc('id')
                ->limit(400)
                ->get(),
            'prefillCustomerId' => $prefillCustomerId,
        ]);
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['recorded_by_user_id'] = $request->user()->id;
        Payment::query()->create($data);

        if (! empty($data['installment_id'])) {
            $inst = Installment::query()->find($data['installment_id']);
            if ($inst) {
                $inst->update([
                    'status' => 'paid',
                    'paid_at' => $data['paid_at'],
                ]);
            }
        }

        return redirect()
            ->route('payments.index', ['customer' => $data['customer_id']])
            ->with('success', 'Tahsilat kaydedildi.');
    }
}
