<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PolicyType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'color',
        'icon',
        'renewal_reminder_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function policies(): HasMany
    {
        return $this->hasMany(InsurancePolicy::class);
    }

    public function companyPolicyTypes(): HasMany
    {
        return $this->hasMany(CompanyPolicyType::class, 'policy_type_id');
    }
}
