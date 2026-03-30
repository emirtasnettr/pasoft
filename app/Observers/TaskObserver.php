<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\TaskActivity;

class TaskObserver
{
    public function created(Task $task): void
    {
        TaskActivity::query()->create([
            'task_id' => $task->id,
            'user_id' => $task->created_by_user_id,
            'action' => 'created',
            'meta' => null,
        ]);
    }

    public function updated(Task $task): void
    {
        $actor = auth()->id();

        if ($task->wasChanged('status')) {
            $from = $task->getOriginal('status');
            $to = $task->status;
            if ($to === 'done') {
                TaskActivity::query()->create([
                    'task_id' => $task->id,
                    'user_id' => $actor,
                    'action' => 'completed',
                    'meta' => ['from' => $from],
                ]);
            } else {
                TaskActivity::query()->create([
                    'task_id' => $task->id,
                    'user_id' => $actor,
                    'action' => 'status_changed',
                    'meta' => ['from' => $from, 'to' => $to],
                ]);
            }
        }

        if ($task->wasChanged('assigned_user_id')) {
            TaskActivity::query()->create([
                'task_id' => $task->id,
                'user_id' => $actor,
                'action' => 'assigned_changed',
                'meta' => [
                    'from_id' => $task->getOriginal('assigned_user_id'),
                    'to_id' => $task->assigned_user_id,
                ],
            ]);
        }
    }
}
