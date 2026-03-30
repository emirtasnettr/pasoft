<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskNoteRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Customer;
use App\Models\InsurancePolicy;
use App\Models\Lead;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskAttachment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = Task::query()
            ->with([
                'assignedUser:id,name,email',
                'customer:id,name',
                'lead:id,title',
                'policy:id,policy_number',
            ])
            ->visibleTo($user)
            ->when($request->boolean('mine'), fn ($q) => $q->where('assigned_user_id', $user->id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('assigned_user_id'), fn ($q) => $q->where('assigned_user_id', $request->integer('assigned_user_id')))
            ->when($request->filled('due_from'), fn ($q) => $q->whereDate('due_at', '>=', $request->date('due_from')->format('Y-m-d')))
            ->when($request->filled('due_to'), fn ($q) => $q->whereDate('due_at', '<=', $request->date('due_to')->format('Y-m-d')))
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $q->where(function ($q) use ($term): void {
                    $q->where('title', 'like', $term)
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term));
                });
            })
            ->orderByRaw("CASE WHEN status = 'done' THEN 1 ELSE 0 END")
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->orderByDesc('id');

        $tasks = $query->paginate(20)->withQueryString();

        $taskDetail = null;
        if ($request->filled('task')) {
            $tid = $request->integer('task');
            $t = Task::query()->visibleTo($user)->whereKey($tid)->first();
            if ($t) {
                $taskDetail = $this->formatTaskDetail($t);
            }
        }

        $dueReminders = Task::query()
            ->visibleTo($user)
            ->whereNotIn('status', ['done', 'cancelled'])
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [now(), now()->addDay()])
            ->orderBy('due_at')
            ->limit(15)
            ->get(['id', 'title', 'due_at']);

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'status' => $request->string('status')->toString(),
                'assigned_user_id' => $request->input('assigned_user_id'),
                'due_from' => $request->input('due_from'),
                'due_to' => $request->input('due_to'),
                'mine' => $request->boolean('mine'),
                'task' => $request->input('task'),
            ],
            'taskDetail' => $taskDetail,
            'dueReminders' => $dueReminders,
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'customers' => Customer::query()->orderBy('name')->limit(200)->get(['id', 'name']),
            'leads' => Lead::query()->orderByDesc('id')->limit(100)->get(['id', 'title']),
            'policies' => InsurancePolicy::query()
                ->with('customer:id,name')
                ->orderByDesc('id')
                ->limit(300)
                ->get(['id', 'policy_number', 'customer_id']),
        ]);
    }

    public function calendar(Request $request): Response
    {
        $user = $request->user();

        $from = $request->date('from') ?? now()->copy()->subMonths(2)->startOfMonth();
        $to = $request->date('to') ?? now()->copy()->addMonths(4)->endOfMonth();

        $rangeStart = $from->copy()->startOfDay();
        $rangeEnd = $to->copy()->endOfDay();

        $tasks = Task::query()
            ->visibleTo($user)
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$rangeStart, $rangeEnd])
            ->with(['assignedUser:id,name', 'customer:id,name'])
            ->orderBy('due_at')
            ->get([
                'id',
                'title',
                'type',
                'due_at',
                'status',
                'priority',
                'assigned_user_id',
                'customer_id',
            ]);

        return Inertia::render('Tasks/Calendar', [
            'tasks' => $tasks,
            'range' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'initialView' => $request->string('view')->toString() ?: 'month',
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by_user_id'] = $request->user()->id;
        $data['status'] = $data['status'] ?? 'pending';
        $data['priority'] = $data['priority'] ?? 'medium';

        Task::query()->create($data);

        return back()->with('success', 'Görev oluşturuldu.');
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $this->authorizeTask($request->user(), $task);

        $task->update($request->validated());

        return back()->with('success', 'Görev güncellendi.');
    }

    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTask($request->user(), $task);
        $task->delete();

        return back()->with('success', 'Görev silindi.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:tasks,id'],
            'action' => ['required', 'in:status,delete'],
            'status' => ['required_if:action,status', 'string', 'in:pending,in_progress,done,cancelled'],
        ]);

        $ids = array_unique($validated['ids']);
        $query = Task::query()->visibleTo($request->user())->whereIn('id', $ids);
        $tasks = $query->get();

        if ($tasks->count() !== count($ids)) {
            abort(403);
        }

        if ($validated['action'] === 'delete') {
            foreach ($tasks as $task) {
                $task->delete();
            }

            return back()->with('success', count($ids).' görev silindi.');
        }

        foreach ($tasks as $task) {
            $task->update(['status' => $validated['status']]);
        }

        return back()->with('success', 'Görev durumları güncellendi.');
    }

    public function storeNote(StoreTaskNoteRequest $request, Task $task): RedirectResponse
    {
        $this->authorizeTask($request->user(), $task);

        $note = $task->notes()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated()['body'],
        ]);

        TaskActivity::query()->create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'action' => 'note_added',
            'meta' => [
                'note_id' => $note->id,
                'preview' => Str::limit($note->body, 120),
            ],
        ]);

        return back()->with('success', 'Not eklendi.');
    }

    public function storeAttachment(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTask($request->user(), $task);

        $request->validate([
            'file' => ['required', File::types(config('upload.document_extensions'))->max(config('upload.document_max_kilobytes'))],
        ]);

        $file = $request->file('file');
        $path = $file->store('task-attachments/'.$task->id, 'public');

        $attachment = TaskAttachment::query()->create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        TaskActivity::query()->create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'action' => 'file_added',
            'meta' => [
                'attachment_id' => $attachment->id,
                'name' => $attachment->original_name,
            ],
        ]);

        return back()->with('success', 'Dosya yüklendi.');
    }

    public function destroyAttachment(Request $request, TaskAttachment $attachment): RedirectResponse
    {
        $task = $attachment->task;
        $this->authorizeTask($request->user(), $task);

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', 'Dosya kaldırıldı.');
    }

    private function authorizeTask(?User $user, Task $task): void
    {
        if (! $user) {
            abort(403);
        }

        if ($user->role?->slug === 'admin') {
            return;
        }

        if ((int) $task->assigned_user_id !== (int) $user->id) {
            abort(403);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formatTaskDetail(Task $task): array
    {
        $task->load([
            'assignedUser:id,name,email',
            'createdBy:id,name',
            'customer:id,name',
            'lead:id,title',
            'policy:id,policy_number,customer_id',
            'notes.user:id,name',
            'attachments.user:id,name',
            'activities.user:id,name',
        ]);

        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'type' => $task->type,
            'status' => $task->status,
            'priority' => $task->priority,
            'due_at' => $task->due_at?->toIso8601String(),
            'remind_at' => $task->remind_at?->toIso8601String(),
            'is_overdue' => $task->is_overdue,
            'assigned_user' => $task->assignedUser,
            'created_by' => $task->createdBy,
            'customer' => $task->customer,
            'lead' => $task->lead,
            'policy' => $task->policy,
            'notes' => $task->notes->map(fn ($n) => [
                'id' => $n->id,
                'body' => $n->body,
                'created_at' => $n->created_at->toIso8601String(),
                'user' => $n->user,
            ]),
            'attachments' => $task->attachments->map(fn ($a) => [
                'id' => $a->id,
                'original_name' => $a->original_name,
                'mime' => $a->mime,
                'size' => $a->size,
                'url' => $a->publicUrl(),
                'created_at' => $a->created_at->toIso8601String(),
                'user' => $a->user,
            ]),
            'activities' => $task->activities->map(fn ($act) => [
                'id' => $act->id,
                'action' => $act->action,
                'meta' => $act->meta,
                'created_at' => $act->created_at->toIso8601String(),
                'user' => $act->user,
            ]),
        ];
    }
}
