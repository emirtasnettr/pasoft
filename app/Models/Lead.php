<?php

namespace App\Models;

use App\Services\LeadPresenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'email',
        'phone',
        'source',
        'stage',
        'estimated_value',
        'assigned_user_id',
        'customer_id',
        'next_follow_up_at',
        'last_activity_at',
        'notes',
    ];

    protected $appends = [
        'score',
        'temperature',
        'insight_badges',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:2',
            'next_follow_up_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function leadNotes(): HasMany
    {
        return $this->hasMany(LeadNote::class)->orderByDesc('created_at');
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user || $user->role?->slug === 'admin') {
            return $query;
        }

        return $query->where('assigned_user_id', $user->id);
    }

    public function touchLastActivity(): void
    {
        $this->forceFill(['last_activity_at' => now()])->saveQuietly();
    }

    public function getScoreAttribute(): int
    {
        return LeadPresenter::score($this);
    }

    public function getTemperatureAttribute(): string
    {
        return LeadPresenter::temperature($this);
    }

    /**
     * @return list<array{type: string, text: string}>
     */
    public function getInsightBadgesAttribute(): array
    {
        return LeadPresenter::insights($this);
    }
}
