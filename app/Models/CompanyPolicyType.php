<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyPolicyType extends Model
{
    protected $table = 'company_policy_type';

    protected $fillable = [
        'insurance_company_id',
        'policy_type_id',
        'commission_rate',
        'min_commission',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'min_commission' => 'decimal:2',
        ];
    }

    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class);
    }

    public function policyType(): BelongsTo
    {
        return $this->belongsTo(PolicyType::class);
    }
}
