<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'type',
        'due_at',
        'remind_at',
        'status',
        'priority',
        'assigned_user_id',
        'created_by_user_id',
        'customer_id',
        'lead_id',
        'policy_id',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'remind_at' => 'datetime',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(InsurancePolicy::class, 'policy_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(TaskNote::class)->orderByDesc('created_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class)->orderByDesc('created_at');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TaskActivity::class)->orderByDesc('created_at');
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user || $user->role?->slug === 'admin') {
            return $query;
        }

        return $query->where('assigned_user_id', $user->id);
    }

    protected $appends = [
        'is_overdue',
    ];

    public function getIsOverdueAttribute(): bool
    {
        if (in_array($this->status, ['done', 'cancelled'], true) || $this->due_at === null) {
            return false;
        }

        return $this->due_at->isPast();
    }
}
