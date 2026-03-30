<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadNoteRequest;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadStageRequest;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = Lead::query()
            ->with(['assignedUser:id,name,email', 'customer:id,name,company_name'])
            ->visibleTo($user)
            ->when($request->filled('stage'), fn ($q) => $q->where('stage', $request->string('stage')->toString()))
            ->when($request->filled('source'), function ($q) use ($request): void {
                $q->where('source', 'like', '%'.$request->string('source')->toString().'%');
            })
            ->when($request->filled('assigned_user_id'), fn ($q) => $q->where('assigned_user_id', $request->integer('assigned_user_id')))
            ->when($request->filled('created_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('created_from')->format('Y-m-d')))
            ->when($request->filled('created_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('created_to')->format('Y-m-d')))
            ->when($request->boolean('stale'), function ($q): void {
                $q->whereNotIn('stage', ['won', 'lost'])
                    ->where(function ($q): void {
                        $q->whereNull('last_activity_at')
                            ->orWhere('last_activity_at', '<', now()->subDays(3));
                    });
            })
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $q->where(function ($q) use ($term): void {
                    $q->where('title', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->orderByRaw("CASE WHEN stage IN ('won','lost') THEN 1 ELSE 0 END")
            ->orderByDesc('last_activity_at')
            ->orderByDesc('id');

        $leads = $query->paginate(20)->withQueryString();

        $leadDetail = null;
        if ($request->filled('lead')) {
            $lid = $request->integer('lead');
            $lead = Lead::query()->visibleTo($user)->whereKey($lid)->first();
            if ($lead) {
                $leadDetail = $this->formatLeadDetail($lead);
            }
        }

        return Inertia::render('Leads/Index', [
            'leads' => $leads,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'stage' => $request->string('stage')->toString(),
                'source' => $request->string('source')->toString(),
                'assigned_user_id' => $request->input('assigned_user_id'),
                'created_from' => $request->input('created_from'),
                'created_to' => $request->input('created_to'),
                'stale' => $request->boolean('stale'),
                'lead' => $request->input('lead'),
            ],
            'leadDetail' => $leadDetail,
            'kpi' => $this->kpi($user),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'customers' => Customer::query()->orderBy('name')->limit(200)->get(['id', 'name', 'company_name']),
        ]);
    }

    public function kanban(Request $request): Response
    {
        $user = $request->user();
        $stages = ['new', 'contacted', 'proposal', 'meeting', 'won', 'lost'];
        $columns = [];
        foreach ($stages as $stage) {
            $columns[$stage] = Lead::query()
                ->visibleTo($user)
                ->where('stage', $stage)
                ->with(['assignedUser:id,name,email', 'customer:id,name'])
                ->orderByDesc('last_activity_at')
                ->orderByDesc('id')
                ->get();
        }

        $leadDetail = null;
        if ($request->filled('lead')) {
            $lid = $request->integer('lead');
            $lead = Lead::query()->visibleTo($user)->whereKey($lid)->first();
            if ($lead) {
                $leadDetail = $this->formatLeadDetail($lead);
            }
        }

        return Inertia::render('Leads/Kanban', [
            'columns' => $columns,
            'kpi' => $this->kpi($user),
            'leadDetail' => $leadDetail,
            'filters' => [
                'lead' => $request->input('lead'),
            ],
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'customers' => Customer::query()->orderBy('name')->limit(200)->get(['id', 'name', 'company_name']),
        ]);
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['last_activity_at'] = now();
        $lead = Lead::query()->create($data);
        $this->activityLogger->log($lead, 'created', 'Lead oluşturuldu');

        return back()->with('success', 'Lead oluşturuldu.');
    }

    public function update(StoreLeadRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorizeLead($request->user(), $lead);
        $data = $request->validated();
        $data['last_activity_at'] = now();
        $lead->update($data);
        $this->activityLogger->log($lead, 'updated', 'Lead güncellendi');

        return back()->with('success', 'Lead güncellendi.');
    }

    public function updateStage(UpdateLeadStageRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorizeLead($request->user(), $lead);
        $from = $lead->stage;
        $lead->update([
            'stage' => $request->validated('stage'),
            'last_activity_at' => now(),
        ]);
        $this->activityLogger->log($lead, 'stage_changed', 'Aşama güncellendi', [
            'from' => $from,
            'to' => $request->validated('stage'),
        ]);

        return back()->with('success', 'Aşama güncellendi.');
    }

    public function destroy(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorizeLead($request->user(), $lead);
        $lead->delete();

        return back()->with('success', 'Lead silindi.');
    }

    public function storeNote(StoreLeadNoteRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorizeLead($request->user(), $lead);
        $note = $lead->leadNotes()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated()['body'],
        ]);
        $lead->update(['last_activity_at' => now()]);
        $this->activityLogger->log($lead, 'note_added', 'Not eklendi', [
            'note_id' => $note->id,
            'preview' => Str::limit($note->body, 120),
        ]);

        return back()->with('success', 'Not kaydedildi.');
    }

    public function touch(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorizeLead($request->user(), $lead);
        $lead->update(['last_activity_at' => now()]);
        $this->activityLogger->log($lead, 'called', 'Temas / arama kaydedildi');

        return back()->with('success', 'Temas kaydedildi.');
    }

    private function authorizeLead(?User $user, Lead $lead): void
    {
        if (! $user) {
            abort(403);
        }

        if ($user->role?->slug === 'admin') {
            return;
        }

        if ((int) $lead->assigned_user_id !== (int) $user->id) {
            abort(403);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function kpi(?User $user): array
    {
        $base = Lead::query()->visibleTo($user);
        $total = (clone $base)->count();
        $today = (clone $base)->whereDate('created_at', today())->count();
        $won = (clone $base)->where('stage', 'won')->count();
        $lost = (clone $base)->where('stage', 'lost')->count();
        $closed = $won + $lost;
        $conversionRate = $closed > 0 ? round(100 * $won / $closed, 1) : 0.0;

        return [
            'total' => $total,
            'today' => $today,
            'conversionRate' => $conversionRate,
            'lost' => $lost,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatLeadDetail(Lead $lead): array
    {
        $lead->load(['assignedUser:id,name,email', 'customer:id,name,company_name']);

        $notes = $lead->leadNotes()->with('user:id,name')->get()->map(fn ($n) => [
            'id' => $n->id,
            'body' => $n->body,
            'created_at' => $n->created_at->toIso8601String(),
            'user' => $n->user,
        ]);

        $activities = Activity::query()
            ->where('subject_type', $lead->getMorphClass())
            ->where('subject_id', $lead->id)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(80)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'action' => $a->action,
                'description' => $a->description,
                'meta' => $a->meta,
                'created_at' => $a->created_at->toIso8601String(),
                'user' => $a->user,
            ]);

        return [
            'id' => $lead->id,
            'title' => $lead->title,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'source' => $lead->source,
            'stage' => $lead->stage,
            'estimated_value' => $lead->estimated_value,
            'assigned_user_id' => $lead->assigned_user_id,
            'customer_id' => $lead->customer_id,
            'next_follow_up_at' => $lead->next_follow_up_at?->toIso8601String(),
            'last_activity_at' => $lead->last_activity_at?->toIso8601String(),
            'crm_notes' => $lead->notes,
            'score' => $lead->score,
            'temperature' => $lead->temperature,
            'insight_badges' => $lead->insight_badges,
            'assigned_user' => $lead->assignedUser,
            'customer' => $lead->customer,
            'lead_notes' => $notes,
            'activities' => $activities,
        ];
    }
}
