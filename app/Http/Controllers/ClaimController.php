<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClaimDocumentRequest;
use App\Http\Requests\StoreClaimNoteRequest;
use App\Http\Requests\StoreClaimRequest;
use App\Http\Requests\UpdateClaimStatusRequest;
use App\Models\Activity;
use App\Models\Claim;
use App\Models\Customer;
use App\Models\Document;
use App\Models\InsuranceCompany;
use App\Models\InsurancePolicy;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\ClaimPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ClaimController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    /**
     * @return list<array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return collect(ClaimPresenter::STATUSES)
            ->map(fn (string $s) => ['value' => $s, 'label' => ClaimPresenter::statusLabel($s)])
            ->values()
            ->all();
    }

    public function index(Request $request): Response
    {
        $query = Claim::query()
            ->with([
                'customer:id,name,company_name,phone,email',
                'policy:id,policy_number',
                'insuranceCompany:id,name',
                'handler:id,name',
            ])
            ->withCount('documents')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('insurance_company_id'), fn ($q) => $q->where('insurance_company_id', $request->integer('insurance_company_id')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('from')->format('Y-m-d')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('to')->format('Y-m-d')))
            ->orderByDesc('last_activity_at')
            ->orderByDesc('id');

        $claims = $query->paginate(15)->withQueryString()->through(fn (Claim $c) => $this->mapClaimRow($c));

        $claimDetail = null;
        if ($request->filled('claim')) {
            $cid = $request->integer('claim');
            $claim = Claim::query()->whereKey($cid)->first();
            if ($claim) {
                $claimDetail = $this->formatClaimDetail($claim);
            }
        }

        return Inertia::render('Claims/Index', [
            'claims' => $claims,
            'filters' => [
                'status' => $request->string('status')->toString(),
                'insurance_company_id' => $request->input('insurance_company_id'),
                'from' => $request->input('from'),
                'to' => $request->input('to'),
                'claim' => $request->input('claim'),
            ],
            'claimDetail' => $claimDetail,
            'kpi' => $this->claimKpi(),
            'insuranceCompanies' => InsuranceCompany::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapClaimRow(Claim $c): array
    {
        $customer = $c->customer;

        return [
            'id' => $c->id,
            'file_number' => $c->file_number,
            'claim_type' => $c->claim_type,
            'amount' => $c->amount,
            'paid_amount' => $c->paid_amount,
            'status' => $c->status,
            'status_label' => ClaimPresenter::statusLabel($c->status),
            'status_badge_class' => ClaimPresenter::statusBadgeClass($c->status),
            'last_activity_at' => $c->last_activity_at?->toIso8601String(),
            'updated_at' => $c->updated_at?->toIso8601String(),
            'insight_badges' => ClaimPresenter::insightBadgesForList($c, (int) $c->documents_count),
            'customer' => $customer ? [
                'id' => $customer->id,
                'display_name' => $customer->displayName(),
                'phone' => $customer->phone,
            ] : null,
            'policy' => $c->policy ? ['id' => $c->policy->id, 'policy_number' => $c->policy->policy_number] : null,
            'insurance_company' => $c->insuranceCompany ? ['id' => $c->insuranceCompany->id, 'name' => $c->insuranceCompany->name] : null,
            'handler' => $c->handler ? ['id' => $c->handler->id, 'name' => $c->handler->name] : null,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function claimKpi(): array
    {
        $open = Claim::query()->where('status', '!=', 'closed')->count();
        $openedMonth = Claim::query()->where('created_at', '>=', now()->startOfMonth())->count();
        $closedMonth = Claim::query()
            ->where('status', 'closed')
            ->where('updated_at', '>=', now()->startOfMonth())
            ->count();
        $pendingPay = Claim::query()->where('status', 'approved')->count();

        return [
            'open' => $open,
            'opened_this_month' => $openedMonth,
            'closed_this_month' => $closedMonth,
            'pending_payment' => $pendingPay,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatClaimDetail(Claim $claim): array
    {
        $claim->load([
            'customer:id,name,company_name,phone,email',
            'policy:id,policy_number',
            'insuranceCompany:id,name',
            'handler:id,name',
        ]);

        $amount = (float) ($claim->amount ?? 0);
        $paid = (float) ($claim->paid_amount ?? 0);

        $notes = $claim->notes()
            ->with('user:id,name')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'body' => $n->body,
                'created_at' => $n->created_at->toIso8601String(),
                'user' => $n->user,
            ]);

        $activities = Activity::query()
            ->where('subject_type', $claim->getMorphClass())
            ->where('subject_id', $claim->id)
            ->with('user:id,name')
            ->orderBy('created_at')
            ->limit(100)
            ->get();

        $timeline = collect();

        if ($activities->isEmpty()) {
            $timeline->push([
                'id' => 0,
                'action' => 'opened',
                'label' => 'Hasar açıldı',
                'description' => 'Dosya oluşturuldu',
                'meta' => null,
                'created_at' => $claim->created_at->toIso8601String(),
                'user' => null,
            ]);
        }

        foreach ($activities as $a) {
            $timeline->push([
                'id' => $a->id,
                'action' => $a->action,
                'label' => ClaimPresenter::activityLabel($a->action),
                'description' => $a->description,
                'meta' => $a->meta,
                'created_at' => $a->created_at->toIso8601String(),
                'user' => $a->user,
            ]);
        }

        $timeline = $timeline->sortBy('created_at')->values();

        $documents = $claim->documents()
            ->with('uploadedBy:id,name')
            ->latest()
            ->limit(40)
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'category' => $d->category,
                'original_name' => $d->original_name,
                'mime' => $d->mime,
                'size' => $d->size,
                'url' => $d->url(),
                'created_at' => $d->created_at->toIso8601String(),
                'uploaded_by' => $d->uploadedBy,
            ]);

        $cust = $claim->customer;

        return [
            'id' => $claim->id,
            'file_number' => $claim->file_number,
            'claim_type' => $claim->claim_type,
            'amount' => $claim->amount,
            'paid_amount' => $claim->paid_amount,
            'pending_amount' => max(0, $amount - $paid),
            'status' => $claim->status,
            'status_label' => ClaimPresenter::statusLabel($claim->status),
            'status_badge_class' => ClaimPresenter::statusBadgeClass($claim->status),
            'customer_notice_notes' => $claim->customer_notice_notes,
            'internal_notes' => $claim->internal_notes,
            'last_activity_at' => $claim->last_activity_at?->toIso8601String(),
            'insight_badges' => ClaimPresenter::insightBadges($claim),
            'customer' => $cust ? [
                'id' => $cust->id,
                'display_name' => $cust->displayName(),
                'phone' => $cust->phone,
                'email' => $cust->email,
            ] : null,
            'policy' => $claim->policy ? [
                'id' => $claim->policy->id,
                'policy_number' => $claim->policy->policy_number,
            ] : null,
            'insurance_company' => $claim->insuranceCompany,
            'handler' => $claim->handler,
            'notes' => $notes,
            'timeline' => $timeline,
            'documents' => $documents,
        ];
    }

    public function create(): Response
    {
        return Inertia::render('Claims/Create', [
            'customers' => Customer::query()->orderBy('name')->limit(500)->get(['id', 'name', 'company_name']),
            'policies' => InsurancePolicy::query()->with('customer:id,name,company_name')->orderByDesc('id')->limit(300)->get(),
            'insuranceCompanies' => InsuranceCompany::query()->where('is_active', true)->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(StoreClaimRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['last_activity_at'] = now();
        if (! isset($data['paid_amount'])) {
            $data['paid_amount'] = 0;
        }
        $claim = Claim::query()->create($data);
        $this->activityLogger->log($claim, 'created', 'Hasar kaydı oluşturuldu');

        return redirect()
            ->route('claims.index')
            ->with('success', 'Hasar kaydı oluşturuldu.');
    }

    public function show(Claim $claim): Response
    {
        $claim->load(['customer', 'policy.policyType', 'insuranceCompany', 'handler']);

        return Inertia::render('Claims/Show', [
            'claim' => $claim,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function edit(Claim $claim): Response
    {
        return Inertia::render('Claims/Edit', [
            'claim' => $claim,
            'customers' => Customer::query()->orderBy('name')->limit(500)->get(['id', 'name', 'company_name']),
            'policies' => InsurancePolicy::query()->with('customer:id,name,company_name')->orderByDesc('id')->limit(300)->get(),
            'insuranceCompanies' => InsuranceCompany::query()->where('is_active', true)->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function destroy(Claim $claim): RedirectResponse
    {
        $claim->delete();

        return redirect()
            ->route('claims.index')
            ->with('success', 'Hasar kaydı silindi.');
    }

    public function update(StoreClaimRequest $request, Claim $claim): RedirectResponse
    {
        $data = $request->validated();
        $data['last_activity_at'] = now();
        $claim->update($data);
        $this->activityLogger->log($claim, 'updated', 'Hasar kaydı güncellendi');

        return redirect()
            ->route('claims.show', $claim)
            ->with('success', 'Hasar kaydı güncellendi.');
    }

    public function storeNote(StoreClaimNoteRequest $request, Claim $claim): RedirectResponse
    {
        $claim->notes()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);
        $claim->update(['last_activity_at' => now()]);
        $this->activityLogger->log($claim, 'note_added', 'Not eklendi');

        return back()->with('success', 'Not eklendi.');
    }

    public function storeDocument(StoreClaimDocumentRequest $request, Claim $claim): RedirectResponse
    {
        $file = $request->file('file');
        $path = $file->store('claim-documents/'.$claim->id, 'public');

        $claim->documents()->create([
            'category' => $request->validated('category') ?? 'general',
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'uploaded_by_user_id' => $request->user()->id,
        ]);
        $claim->update(['last_activity_at' => now()]);
        $this->activityLogger->log($claim, 'document_added', 'Evrak yüklendi');

        return back()->with('success', 'Dosya yüklendi.');
    }

    public function destroyDocument(Request $request, Document $document): RedirectResponse
    {
        $document->loadMissing('documentable');
        if ($document->documentable_type !== (new Claim)->getMorphClass()
            || ! $document->documentable instanceof Claim) {
            abort(404);
        }

        if ($document->disk && $document->path) {
            Storage::disk($document->disk)->delete($document->path);
        }

        $claim = $document->documentable;
        $this->activityLogger->log($claim, 'document_removed', 'Evrak silindi');
        $document->delete();
        $claim->update(['last_activity_at' => now()]);

        return back()->with('success', 'Dosya kaldırıldı.');
    }

    public function updateStatus(UpdateClaimStatusRequest $request, Claim $claim): RedirectResponse
    {
        $from = $claim->status;
        $data = ['status' => $request->validated('status'), 'last_activity_at' => now()];
        if ($request->filled('paid_amount')) {
            $data['paid_amount'] = $request->validated('paid_amount');
        }
        $claim->update($data);
        $this->activityLogger->log($claim, 'status_changed', 'Durum güncellendi', [
            'from' => $from,
            'to' => $request->validated('status'),
        ]);

        return back()->with('success', 'Durum güncellendi.');
    }
}
