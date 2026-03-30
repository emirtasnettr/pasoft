<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsurancePolicy extends Model
{
    use SoftDeletes;

    protected $table = 'policies';

    protected $fillable = [
        'customer_id',
        'policy_type_id',
        'insurance_company_id',
        'policy_number',
        'starts_at',
        'ends_at',
        'premium_amount',
        'commission_amount',
        'commission_collected',
        'premium_payment_status',
        'coverage_details',
        'pdf_path',
        'renewal_status',
        'renewal_pipeline_stage',
        'renewal_reminder_days',
        'last_renewal_reminder_at',
        'cross_sell_suggestions',
        'owner_user_id',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'premium_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'commission_collected' => 'decimal:2',
            'last_renewal_reminder_at' => 'datetime',
            'cross_sell_suggestions' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function policyType(): BelongsTo
    {
        return $this->belongsTo(PolicyType::class);
    }

    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class, 'policy_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'policy_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'policy_id');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
