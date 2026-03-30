<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public function log(Model $subject, string $action, ?string $description = null, ?array $meta = null): Activity
    {
        return Activity::query()->create([
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'meta' => $meta,
        ]);
    }
}
