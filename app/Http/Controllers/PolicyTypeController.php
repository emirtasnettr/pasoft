<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePolicyTypeRequest;
use App\Http\Requests\UpdatePolicyTypeRequest;
use App\Models\CompanyPolicyType;
use App\Models\InsuranceCompany;
use App\Models\PolicyType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PolicyTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $types = PolicyType::query()
            ->withCount(['policies', 'companyPolicyTypes'])
            ->when($request->string('search')->toString(), function ($q, string $s): void {
                $q->where(function ($w) use ($s): void {
                    $w->where('name', 'like', "%{$s}%")
                        ->orWhere('slug', 'like', "%{$s}%")
                        ->orWhere('category', 'like', "%{$s}%");
                });
            })
            ->when($request->string('status')->toString() === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->string('status')->toString() === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (PolicyType $t) => $this->mapPolicyTypeListRow($t));

        $typeDetail = null;
        if ($request->filled('type')) {
            $id = $request->integer('type');
            $row = PolicyType::query()->whereKey($id)->first();
            if ($row) {
                $typeDetail = $this->formatPolicyTypeDetail($row);
            }
        }

        return Inertia::render('PolicyTypes/Index', [
            'policyTypes' => $types,
            'typeDetail' => $typeDetail,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'type' => $request->input('type'),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPolicyTypeListRow(PolicyType $t): array
    {
        return [
            'id' => $t->id,
            'name' => $t->name,
            'slug' => $t->slug,
            'category' => $t->category,
            'color' => $t->color,
            'icon' => $t->icon,
            'is_active' => $t->is_active,
            'policies_count' => $t->policies_count,
            'renewal_reminder_days' => $t->renewal_reminder_days,
            'companies_count' => $t->company_policy_types_count,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatPolicyTypeDetail(PolicyType $type): array
    {
        $type->loadCount('policies');
        $type->load([
            'companyPolicyTypes' => fn ($q) => $q->with('insuranceCompany:id,name,code,is_active'),
        ]);
        $premiumSum = (float) $type->policies()->sum('premium_amount');

        $companyLinks = $type->companyPolicyTypes
            ->sortBy(fn (CompanyPolicyType $r) => $r->insuranceCompany?->name ?? '')
            ->values()
            ->map(fn (CompanyPolicyType $r) => [
                'id' => $r->id,
                'insurance_company_id' => $r->insurance_company_id,
                'name' => $r->insuranceCompany?->name ?? '—',
                'code' => $r->insuranceCompany?->code,
                'company_is_active' => (bool) ($r->insuranceCompany?->is_active),
                'commission_rate' => (float) $r->commission_rate,
                'min_commission' => $r->min_commission !== null ? (float) $r->min_commission : null,
            ])
            ->all();

        $linkedCompanyIds = $type->companyPolicyTypes->pluck('insurance_company_id');
        $attachableCompanies = InsuranceCompany::query()
            ->where('is_active', true)
            ->whereNotIn('id', $linkedCompanyIds)
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->all();

        return [
            'id' => $type->id,
            'name' => $type->name,
            'slug' => $type->slug,
            'description' => $type->description,
            'category' => $type->category,
            'color' => $type->color,
            'icon' => $type->icon,
            'renewal_reminder_days' => $type->renewal_reminder_days,
            'is_active' => $type->is_active,
            'stats' => [
                'policies_count' => $type->policies_count,
                'premium_volume' => $premiumSum,
            ],
            'company_links' => $companyLinks,
            'attachable_companies' => $attachableCompanies,
        ];
    }

    public function create(): Response
    {
        return Inertia::render('PolicyTypes/Create');
    }

    public function store(StorePolicyTypeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        PolicyType::query()->create($data);

        return redirect()
            ->route('policy-types.index')
            ->with('success', 'Poliçe türü eklendi.');
    }

    public function edit(PolicyType $policyType): Response
    {
        $policyType->loadCount('policies');
        $policyType->load([
            'companyPolicyTypes' => fn ($q) => $q->with('insuranceCompany:id,name,code,is_active'),
        ]);

        $links = $policyType->companyPolicyTypes
            ->sortBy(fn (CompanyPolicyType $r) => $r->insuranceCompany?->name ?? '')
            ->values()
            ->map(fn (CompanyPolicyType $r) => [
                'id' => $r->id,
                'insurance_company_id' => $r->insurance_company_id,
                'name' => $r->insuranceCompany?->name ?? '—',
                'code' => $r->insuranceCompany?->code,
                'company_is_active' => (bool) ($r->insuranceCompany?->is_active),
                'commission_rate' => (float) $r->commission_rate,
                'min_commission' => $r->min_commission !== null ? (float) $r->min_commission : null,
            ])
            ->all();

        $linkedCompanyIds = $policyType->companyPolicyTypes->pluck('insurance_company_id');
        $attachableCompanies = InsuranceCompany::query()
            ->where('is_active', true)
            ->whereNotIn('id', $linkedCompanyIds)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return Inertia::render('PolicyTypes/Edit', [
            'policyType' => [
                ...$policyType->only([
                    'id', 'name', 'slug', 'description', 'category', 'color', 'icon',
                    'renewal_reminder_days', 'is_active',
                ]),
                'policies_count' => $policyType->policies_count,
                'company_links' => $links,
                'attachable_companies' => $attachableCompanies,
            ],
        ]);
    }

    public function update(UpdatePolicyTypeRequest $request, PolicyType $policyType): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $policyType->update($data);

        return redirect()
            ->route('policy-types.index')
            ->with('success', 'Poliçe türü güncellendi.');
    }

    public function destroy(PolicyType $policyType): RedirectResponse
    {
        if ($policyType->policies()->exists()) {
            return redirect()
                ->route('policy-types.index')
                ->with('error', 'Bu türe bağlı poliçeler olduğu için silinemez. Pasifleştirmeyi deneyin.');
        }

        $policyType->delete();

        return redirect()
            ->route('policy-types.index')
            ->with('success', 'Poliçe türü silindi.');
    }
}
