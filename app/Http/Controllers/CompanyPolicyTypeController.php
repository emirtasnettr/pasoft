<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyPolicyTypeRequest;
use App\Http\Requests\UpdateCompanyPolicyTypeRequest;
use App\Models\CompanyPolicyType;
use Illuminate\Http\RedirectResponse;

class CompanyPolicyTypeController extends Controller
{
    public function store(StoreCompanyPolicyTypeRequest $request): RedirectResponse
    {
        CompanyPolicyType::query()->create($request->validated());

        return back()->with('success', 'Firma — poliçe türü komisyonu eklendi.');
    }

    public function update(UpdateCompanyPolicyTypeRequest $request, CompanyPolicyType $companyPolicyType): RedirectResponse
    {
        $companyPolicyType->update($request->validated());

        return back()->with('success', 'Komisyon güncellendi.');
    }

    public function destroy(CompanyPolicyType $companyPolicyType): RedirectResponse
    {
        $companyPolicyType->delete();

        return back()->with('success', 'Eşleşme kaldırıldı.');
    }
}
