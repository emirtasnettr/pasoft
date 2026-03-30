<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInstallmentPlanRequest;
use App\Http\Requests\StoreInsurancePolicyRequest;
use App\Http\Requests\StorePolicyDocumentRequest;
use App\Http\Requests\StorePolicyNoteRequest;
use App\Http\Requests\UpdatePolicyRenewalRequest;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Document;
use App\Models\InsuranceCompany;
use App\Models\Installment;
use App\Models\InsurancePolicy;
use App\Models\Lead;
use App\Models\PolicyType;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\PolicyDefaultsService;
use App\Services\PolicyPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class InsurancePolicyController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function index(Request $request): Response
    {
        $query = InsurancePolicy::query()
            ->with([
                'customer:id,name,company_name,phone,email,assigned_user_id',
                'policyType:id,name',
                'insuranceCompany:id,name',
                'owner:id,name,email',
            ])
            ->when($request->filled('search'), function ($q) use ($request): void {
                $s = '%'.$request->string('search')->toString().'%';
                $q->where(function ($w) use ($s): void {
                    $w->where('policy_number', 'like', $s)
                        ->orWhereHas('customer', function ($c) use ($s): void {
                            $c->where('name', 'like', $s)
                                ->orWhere('company_name', 'like', $s);
                        });
                });
            })
            ->when($request->filled('policy_type_id'), fn ($q) => $q->where('policy_type_id', $request->integer('policy_type_id')))
            ->when($request->filled('insurance_company_id'), fn ($q) => $q->where('insurance_company_id', $request->integer('insurance_company_id')))
            ->when($request->filled('renewal_status'), fn ($q) => $q->where('renewal_status', $request->string('renewal_status')))
            ->when($request->filled('owner_user_id'), fn ($q) => $q->where('owner_user_id', $request->integer('owner_user_id')))
            ->when($request->filled('expires_within_days'), function ($q) use ($request): void {
                $d = max(1, min(90, $request->integer('expires_within_days')));
                $q->whereNotNull('ends_at')
                    ->whereDate('ends_at', '>=', now()->toDateString())
                    ->whereDate('ends_at', '<=', now()->addDays($d)->toDateString());
            });

        $policies = $query
            ->orderByRaw('case when ends_at is null then 1 else 0 end')
            ->orderBy('ends_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (InsurancePolicy $p) => $this->mapPolicyListRow($p));

        $policyDetail = null;
        if ($request->filled('policy')) {
            $pid = $request->integer('policy');
            $policy = InsurancePolicy::query()->whereKey($pid)->first();
            if ($policy) {
                $policyDetail = $this->formatPolicyDetail($policy);
            }
        }

        $criticalAlerts = InsurancePolicy::query()
            ->with(['customer:id,name,company_name'])
            ->whereNotNull('ends_at')
            ->where('renewal_status', '!=', 'renewed')
            ->whereDate('ends_at', '<=', now()->addDay()->toDateString())
            ->orderBy('ends_at')
            ->limit(10)
            ->get()
            ->map(fn (InsurancePolicy $p) => [
                'id' => $p->id,
                'policy_number' => $p->policy_number,
                'customer_name' => $p->customer?->displayName() ?? '—',
                'ends_at' => $p->ends_at?->toDateString(),
                'urgency' => PolicyPresenter::urgency($p->ends_at),
            ]);

        return Inertia::render('Policies/Index', [
            'policies' => $policies,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'policy_type_id' => $request->input('policy_type_id'),
                'insurance_company_id' => $request->input('insurance_company_id'),
                'renewal_status' => $request->string('renewal_status')->toString(),
                'expires_within_days' => $request->input('expires_within_days'),
                'owner_user_id' => $request->input('owner_user_id'),
                'policy' => $request->input('policy'),
            ],
            'policyDetail' => $policyDetail,
            'kpi' => $this->policyKpi(),
            'criticalAlerts' => $criticalAlerts,
            'policyTypes' => PolicyType::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'insuranceCompanies' => InsuranceCompany::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPolicyListRow(InsurancePolicy $p): array
    {
        $urgency = PolicyPresenter::urgency($p->ends_at);
        $tw = PolicyPresenter::urgencyTailwind($urgency);

        return [
            'id' => $p->id,
            'policy_number' => $p->policy_number,
            'starts_at' => $p->starts_at?->toDateString(),
            'ends_at' => $p->ends_at?->toDateString(),
            'premium_amount' => $p->premium_amount,
            'commission_amount' => $p->commission_amount,
            'renewal_status' => $p->renewal_status,
            'renewal_label_tr' => PolicyPresenter::renewalLabelTr($p->renewal_status),
            'premium_payment_status' => $p->premium_payment_status,
            'pdf_path' => $p->pdf_path,
            'pdf_url' => $p->pdf_path ? Storage::disk('public')->url($p->pdf_path) : null,
            'days_left' => PolicyPresenter::daysLeft($p->ends_at),
            'urgency' => $urgency,
            'urgency_badge_class' => $tw['badge'],
            'urgency_row_class' => $tw['row'],
            'insight_badges' => PolicyPresenter::insightBadges($p),
            'customer' => $p->customer ? [
                'id' => $p->customer->id,
                'display_name' => $p->customer->displayName(),
                'phone' => $p->customer->phone,
                'email' => $p->customer->email,
            ] : null,
            'policy_type' => $p->policyType ? ['id' => $p->policyType->id, 'name' => $p->policyType->name] : null,
            'insurance_company' => $p->insuranceCompany ? ['id' => $p->insuranceCompany->id, 'name' => $p->insuranceCompany->name] : null,
            'owner' => $p->owner ? ['id' => $p->owner->id, 'name' => $p->owner->name] : null,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function policyKpi(): array
    {
        $base = InsurancePolicy::query();

        $total = (clone $base)->count();

        $active = (clone $base)->where(function ($q): void {
            $q->whereNull('ends_at')
                ->orWhereDate('ends_at', '>=', now()->toDateString());
        })->count();

        $expired = (clone $base)->whereNotNull('ends_at')
            ->whereDate('ends_at', '<', now()->toDateString())
            ->count();

        $renewedThisMonth = (clone $base)->where('renewal_status', 'renewed')
            ->where('updated_at', '>=', now()->startOfMonth())
            ->count();

        $pendingRenewals = (clone $base)->where('renewal_status', 'pending_renewal')->count();

        return [
            'total' => $total,
            'active' => $active,
            'expired' => $expired,
            'renewed_this_month' => $renewedThisMonth,
            'pending_renewals' => $pendingRenewals,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatPolicyDetail(InsurancePolicy $policy): array
    {
        $policy->load([
            'customer.assignedUser:id,name',
            'policyType:id,name',
            'insuranceCompany:id,name',
            'owner:id,name,email',
        ]);

        $paymentsTotal = (float) $policy->payments()->sum('amount');
        $premium = (float) $policy->premium_amount;
        $commission = (float) $policy->commission_amount;
        $commissionCollected = (float) $policy->commission_collected;

        $installmentsPending = (float) $policy->installments()
            ->where('status', '!=', 'paid')
            ->sum('amount');

        $notes = $policy->notes()
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

        $activities = Activity::query()
            ->where('subject_type', $policy->getMorphClass())
            ->where('subject_id', $policy->id)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(80)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'action' => $a->action,
                'description' => $a->description,
                'meta' => $a->meta,
                'created_at' => $a->created_at->toIso8601String(),
                'user' => $a->user,
            ]);

        $documents = $policy->documents()
            ->with('uploadedBy:id,name')
            ->latest()
            ->limit(40)
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'category' => $d->category,
                'original_name' => $d->original_name,
                'mime' => $d->mime,
                'size' => $d->size,
                'url' => $d->url(),
                'created_at' => $d->created_at->toIso8601String(),
                'uploaded_by' => $d->uploadedBy,
            ]);

        $crossSell = $policy->cross_sell_suggestions;
        if (! is_array($crossSell) || $crossSell === []) {
            $crossSell = [
                'Bu müşteriye konut ve sağlık sigortası çapraz satışı önerin.',
                'İşyeri / ferdi kaza paketleri için potansiyel değerlendirin.',
            ];
        }

        $urgency = PolicyPresenter::urgency($policy->ends_at);
        $tw = PolicyPresenter::urgencyTailwind($urgency);

        $cust = $policy->customer;

        return [
            'id' => $policy->id,
            'policy_number' => $policy->policy_number,
            'starts_at' => $policy->starts_at?->toDateString(),
            'ends_at' => $policy->ends_at?->toDateString(),
            'coverage_details' => $policy->coverage_details,
            'premium_amount' => $policy->premium_amount,
            'commission_amount' => $policy->commission_amount,
            'commission_collected' => $policy->commission_collected,
            'premium_payment_status' => $policy->premium_payment_status,
            'renewal_status' => $policy->renewal_status,
            'renewal_label_tr' => PolicyPresenter::renewalLabelTr($policy->renewal_status),
            'renewal_pipeline_stage' => $policy->renewal_pipeline_stage,
            'renewal_reminder_days' => $policy->renewal_reminder_days,
            'last_renewal_reminder_at' => $policy->last_renewal_reminder_at?->toIso8601String(),
            'pdf_path' => $policy->pdf_path,
            'pdf_url' => $policy->pdf_path ? Storage::disk('public')->url($policy->pdf_path) : null,
            'days_left' => PolicyPresenter::daysLeft($policy->ends_at),
            'urgency' => $urgency,
            'urgency_badge_class' => $tw['badge'],
            'insight_badges' => PolicyPresenter::insightBadges($policy),
            'policy_type' => $policy->policyType,
            'insurance_company' => $policy->insuranceCompany,
            'owner' => $policy->owner,
            'customer' => $cust ? [
                'id' => $cust->id,
                'display_name' => $cust->displayName(),
                'name' => $cust->name,
                'company_name' => $cust->company_name,
                'phone' => $cust->phone,
                'email' => $cust->email,
            ] : null,
            'finance' => [
                'premium' => $premium,
                'commission' => $commission,
                'commission_collected' => $commissionCollected,
                'commission_pending' => max(0, $commission - $commissionCollected),
                'payments_total' => $paymentsTotal,
                'premium_balance_estimate' => max(0, $premium - $paymentsTotal),
                'installments_pending_total' => $installmentsPending,
            ],
            'cross_sell_suggestions' => array_values($crossSell),
            'notes' => $notes,
            'activities' => $activities,
            'documents' => $documents,
        ];
    }

    public function create(Request $request, PolicyDefaultsService $policyDefaultsService): Response
    {
        $prefillFromLead = null;
        if ($request->filled('lead_id') && $request->user()) {
            $lead = Lead::query()->visibleTo($request->user())->find($request->integer('lead_id'));
            if ($lead) {
                $lines = ["Lead: {$lead->title} (#{$lead->id})"];
                if ($lead->email) {
                    $lines[] = 'E-posta: '.$lead->email;
                }
                if ($lead->phone) {
                    $lines[] = 'Tel: '.$lead->phone;
                }
                $prefillFromLead = [
                    'customer_id' => $lead->customer_id,
                    'coverage_details' => implode("\n", $lines),
                    'owner_user_id' => $lead->assigned_user_id,
                ];
            }
        }

        $prefillFromCustomer = null;
        if ($request->filled('customer_id') && $request->user()) {
            $cust = Customer::query()->find($request->integer('customer_id'));
            if ($cust) {
                $label = $cust->displayName();
                $prefillFromCustomer = [
                    'customer_id' => $cust->id,
                    'coverage_details' => "Müşteri: {$label} (#{$cust->id})",
                    'owner_user_id' => $cust->assigned_user_id,
                ];
            }
        }

        $defaultsCompany = $request->filled('defaults_company') ? $request->integer('defaults_company') : null;
        $defaultsType = $request->filled('defaults_type') ? $request->integer('defaults_type') : null;

        return Inertia::render('Policies/Create', [
            'customers' => Customer::query()->orderBy('name')->limit(500)->get(['id', 'name', 'company_name']),
            'policyTypes' => PolicyType::query()->where('is_active', true)->orderBy('name')->get(),
            'insuranceCompanies' => InsuranceCompany::query()->where('is_active', true)->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'prefillFromLead' => $prefillFromLead,
            'prefillFromCustomer' => $prefillFromCustomer,
            'policyDefaults' => $policyDefaultsService->forForm($defaultsCompany, $defaultsType),
        ]);
    }

    public function store(StoreInsurancePolicyRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('pdf');
        if ($request->hasFile('pdf')) {
            $data['pdf_path'] = $request->file('pdf')->store('policy-pdfs', 'public');
        }
        $policy = InsurancePolicy::query()->create($data);
        $this->activityLogger->log($policy, 'created', 'Poliçe kaydı oluşturuldu');

        return redirect()
            ->route('policies.show', $policy)
            ->with('success', 'Poliçe kaydedildi.');
    }

    public function show(InsurancePolicy $policy): Response
    {
        $policy->load([
            'customer.assignedUser:id,name',
            'policyType',
            'insuranceCompany',
            'owner:id,name',
            'installments' => fn ($q) => $q->orderBy('sequence'),
            'payments' => fn ($q) => $q->latest('paid_at')->limit(20),
        ]);

        return Inertia::render('Policies/Show', [
            'policy' => $policy,
        ]);
    }

    public function edit(InsurancePolicy $policy): Response
    {
        return Inertia::render('Policies/Edit', [
            'policy' => $policy,
            'customers' => Customer::query()->orderBy('name')->limit(500)->get(['id', 'name', 'company_name']),
            'policyTypes' => PolicyType::query()->where('is_active', true)->orderBy('name')->get(),
            'insuranceCompanies' => InsuranceCompany::query()->where('is_active', true)->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(StoreInsurancePolicyRequest $request, InsurancePolicy $policy): RedirectResponse
    {
        $data = $request->safe()->except('pdf');
        if ($request->hasFile('pdf')) {
            if ($policy->pdf_path) {
                Storage::disk('public')->delete($policy->pdf_path);
            }
            $data['pdf_path'] = $request->file('pdf')->store('policy-pdfs', 'public');
        }
        $policy->update($data);
        $this->activityLogger->log($policy, 'updated', 'Poliçe güncellendi');

        return redirect()
            ->route('policies.show', $policy)
            ->with('success', 'Poliçe güncellendi.');
    }

    public function destroy(InsurancePolicy $policy): RedirectResponse
    {
        if ($policy->pdf_path) {
            Storage::disk('public')->delete($policy->pdf_path);
        }
        $policy->delete();

        return redirect()
            ->route('policies.index')
            ->with('success', 'Poliçe arşivlendi.');
    }

    public function storeInstallmentPlan(StoreInstallmentPlanRequest $request, InsurancePolicy $policy): RedirectResponse
    {
        $rows = $request->validated('installments');

        DB::transaction(function () use ($policy, $rows): void {
            $policy->installments()->where('status', '!=', 'paid')->delete();
            $maxSeq = (int) $policy->installments()->max('sequence');

            foreach ($rows as $idx => $row) {
                Installment::query()->create([
                    'policy_id' => $policy->id,
                    'sequence' => $maxSeq + $idx + 1,
                    'due_date' => $row['due_date'],
                    'amount' => $row['amount'],
                    'status' => 'pending',
                ]);
            }
        });

        $this->activityLogger->log($policy, 'installment_plan_set', 'Taksit planı güncellendi');

        return back()->with('success', 'Taksit planı kaydedildi.');
    }

    public function storeNote(StorePolicyNoteRequest $request, InsurancePolicy $policy): RedirectResponse
    {
        $policy->notes()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);
        $this->activityLogger->log($policy, 'note_added', 'Not eklendi');

        return back()->with('success', 'Not eklendi.');
    }

    public function storeDocument(StorePolicyDocumentRequest $request, InsurancePolicy $policy): RedirectResponse
    {
        $file = $request->file('file');
        $path = $file->store('policy-documents/'.$policy->id, 'public');

        $policy->documents()->create([
            'category' => $request->validated('category') ?? 'general',
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'uploaded_by_user_id' => $request->user()->id,
        ]);

        $this->activityLogger->log($policy, 'document_added', 'Evrak yüklendi');

        return back()->with('success', 'Dosya yüklendi.');
    }

    public function destroyDocument(Request $request, Document $document): RedirectResponse
    {
        $document->loadMissing('documentable');
        if ($document->documentable_type !== (new InsurancePolicy)->getMorphClass()
            || ! $document->documentable instanceof InsurancePolicy) {
            abort(404);
        }

        if ($document->disk && $document->path) {
            Storage::disk($document->disk)->delete($document->path);
        }

        $this->activityLogger->log($document->documentable, 'document_removed', 'Evrak silindi');
        $document->delete();

        return back()->with('success', 'Dosya kaldırıldı.');
    }

    public function updateRenewal(UpdatePolicyRenewalRequest $request, InsurancePolicy $policy): RedirectResponse
    {
        $policy->update($request->validated());
        $this->activityLogger->log($policy, 'renewal_updated', 'Yenileme durumu güncellendi', [
            'renewal_status' => $request->validated('renewal_status'),
        ]);

        return back()->with('success', 'Yenileme bilgisi güncellendi.');
    }

    public function sendRenewalReminder(Request $request, InsurancePolicy $policy): RedirectResponse
    {
        $policy->update(['last_renewal_reminder_at' => now()]);
        $this->activityLogger->log($policy, 'reminder_sent', 'Yenileme hatırlatması kaydedildi (SMS/E-posta entegrasyonuna hazır)');

        return back()->with('success', 'Hatırlatma kaydı oluşturuldu. SMS/E-posta kanalı entegre edildiğinde gönderilecek.');
    }

    public function startRenewal(Request $request, InsurancePolicy $policy): RedirectResponse
    {
        $policy->update([
            'renewal_status' => 'pending_renewal',
            'renewal_pipeline_stage' => $policy->renewal_pipeline_stage ?: 'contact',
        ]);
        $this->activityLogger->log($policy, 'renewal_started', 'Yenileme süreci başlatıldı');

        return back()->with('success', 'Yenileme başlatıldı.');
    }
}
