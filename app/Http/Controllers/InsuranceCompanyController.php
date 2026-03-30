<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInsuranceCompanyRequest;
use App\Http\Requests\UpdateInsuranceCompanyRequest;
use App\Models\CompanyPolicyType;
use App\Models\InsuranceCompany;
use App\Models\PolicyType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class InsuranceCompanyController extends Controller
{
    public function index(Request $request): Response
    {
        $companies = InsuranceCompany::query()
            ->withCount('policies')
            ->when($request->string('search')->toString(), function ($q, string $s): void {
                $q->where(function ($w) use ($s): void {
                    $w->where('name', 'like', "%{$s}%")
                        ->orWhere('code', 'like', "%{$s}%")
                        ->orWhere('contact_email', 'like', "%{$s}%");
                });
            })
            ->when($request->string('status')->toString() === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->string('status')->toString() === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (InsuranceCompany $c) => $this->mapCompanyListRow($c));

        $companyDetail = null;
        if ($request->filled('company')) {
            $id = $request->integer('company');
            $row = InsuranceCompany::query()->whereKey($id)->first();
            if ($row) {
                $companyDetail = $this->formatCompanyDetail($row);
            }
        }

        return Inertia::render('InsuranceCompanies/Index', [
            'companies' => $companies,
            'companyDetail' => $companyDetail,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'company' => $request->input('company'),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapCompanyListRow(InsuranceCompany $c): array
    {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'code' => $c->code,
            'logo_url' => $this->logoUrl($c->logo_path),
            'contact_phone' => $c->contact_phone,
            'contact_email' => $c->contact_email,
            'contact_person' => $c->contact_person,
            'is_active' => $c->is_active,
            'policies_count' => $c->policies_count,
        ];
    }

    private function logoUrl(?string $path): ?string
    {
        return $path ? '/storage/'.ltrim($path, '/') : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatCompanyDetail(InsuranceCompany $company): array
    {
        $company->load([
            'companyPolicyTypes' => fn ($q) => $q->with('policyType:id,name,is_active'),
        ]);
        $company->loadCount('policies');

        $premiumSum = (float) $company->policies()->sum('premium_amount');
        $commissionSum = (float) $company->policies()->sum('commission_amount');
        $avgCommissionPercent = $premiumSum > 0 ? round(($commissionSum / $premiumSum) * 100, 2) : null;

        $supported = $company->companyPolicyTypes
            ->sortBy(fn (CompanyPolicyType $r) => $r->policyType?->name ?? '')
            ->values()
            ->map(fn (CompanyPolicyType $r) => [
                'id' => $r->id,
                'policy_type_id' => $r->policy_type_id,
                'name' => $r->policyType?->name ?? '—',
                'type_is_active' => (bool) ($r->policyType?->is_active),
                'commission_rate' => (float) $r->commission_rate,
                'min_commission' => $r->min_commission !== null ? (float) $r->min_commission : null,
            ])
            ->all();

        $linkedTypeIds = $company->companyPolicyTypes->pluck('policy_type_id');
        $attachablePolicyTypes = PolicyType::query()
            ->where('is_active', true)
            ->whereNotIn('id', $linkedTypeIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->all();

        return [
            'id' => $company->id,
            'name' => $company->name,
            'code' => $company->code,
            'logo_url' => $this->logoUrl($company->logo_path),
            'contact_phone' => $company->contact_phone,
            'contact_email' => $company->contact_email,
            'contact_person' => $company->contact_person,
            'is_active' => $company->is_active,
            'api_enabled' => $company->api_enabled,
            'quote_integration_enabled' => $company->quote_integration_enabled,
            'performance' => [
                'policies_count' => $company->policies_count,
                'premium_volume' => $premiumSum,
                'avg_commission_percent' => $avgCommissionPercent,
            ],
            'supported_policy_types' => $supported,
            'attachable_policy_types' => $attachablePolicyTypes,
        ];
    }

    public function create(): Response
    {
        return Inertia::render('InsuranceCompanies/Create');
    }

    public function store(StoreInsuranceCompanyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['logo']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['api_enabled'] = $request->boolean('api_enabled');
        $data['quote_integration_enabled'] = $request->boolean('quote_integration_enabled');

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('insurance-company-logos', 'public');
        }

        InsuranceCompany::query()->create($data);

        return redirect()
            ->route('insurance-companies.index')
            ->with('success', 'Sigorta firması eklendi.');
    }

    public function edit(InsuranceCompany $insuranceCompany): Response
    {
        return Inertia::render('InsuranceCompanies/Edit', [
            'company' => array_merge($insuranceCompany->toArray(), [
                'logo_url' => $this->logoUrl($insuranceCompany->logo_path),
            ]),
        ]);
    }

    public function update(UpdateInsuranceCompanyRequest $request, InsuranceCompany $insuranceCompany): RedirectResponse
    {
        $data = $request->validated();
        unset($data['logo']);
        $data['is_active'] = $request->boolean('is_active');
        $data['api_enabled'] = $request->boolean('api_enabled');
        $data['quote_integration_enabled'] = $request->boolean('quote_integration_enabled');

        if ($request->hasFile('logo')) {
            if ($insuranceCompany->logo_path) {
                Storage::disk('public')->delete($insuranceCompany->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('insurance-company-logos', 'public');
        }

        $insuranceCompany->update($data);

        return redirect()
            ->route('insurance-companies.index')
            ->with('success', 'Sigorta firması güncellendi.');
    }

    public function destroy(InsuranceCompany $insuranceCompany): RedirectResponse
    {
        if ($insuranceCompany->logo_path) {
            Storage::disk('public')->delete($insuranceCompany->logo_path);
        }

        $insuranceCompany->delete();

        return redirect()
            ->route('insurance-companies.index')
            ->with('success', 'Sigorta firması kaldırıldı (arşivlendi).');
    }
}
