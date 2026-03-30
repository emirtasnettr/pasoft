<?php

namespace App\Http\Controllers;

use App\Models\InsuranceCompany;
use App\Models\PolicyType;
use Inertia\Inertia;
use Inertia\Response;

class DefinitionsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Definitions/Index', [
            'companyCount' => InsuranceCompany::query()->count(),
            'policyTypeCount' => PolicyType::query()->count(),
            'activeCompanies' => InsuranceCompany::query()->where('is_active', true)->count(),
            'activePolicyTypes' => PolicyType::query()->where('is_active', true)->count(),
        ]);
    }
}
