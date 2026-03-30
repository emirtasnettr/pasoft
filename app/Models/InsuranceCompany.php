<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsuranceCompany extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'logo_path',
        'contact_phone',
        'contact_email',
        'contact_person',
        'is_active',
        'api_enabled',
        'quote_integration_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'api_enabled' => 'boolean',
            'quote_integration_enabled' => 'boolean',
        ];
    }

    public function policies(): HasMany
    {
        return $this->hasMany(InsurancePolicy::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    public function companyPolicyTypes(): HasMany
    {
        return $this->hasMany(CompanyPolicyType::class, 'insurance_company_id');
    }
}
