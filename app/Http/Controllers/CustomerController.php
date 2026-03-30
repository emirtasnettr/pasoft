<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerDocumentRequest;
use App\Http\Requests\StoreCustomerNoteRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Document;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function index(Request $request): Response
    {
        $morphCustomer = (new Customer)->getMorphClass();

        $query = Customer::query()
            ->select('customers.*')
            ->with(['assignedUser:id,name,email'])
            ->withCount(['policies as policies_count'])
            ->withSum('policies as total_premium', 'premium_amount')
            ->withSum('policies as total_commission', 'commission_amount')
            ->withSum('policies as total_commission_collected', 'commission_collected')
            ->withSum('payments as payments_total', 'amount')
            ->selectRaw(
                '(select max(u.ts) from (
                    select max(policies.updated_at) as ts from policies
                        where policies.customer_id = customers.id and policies.deleted_at is null
                    union all
                    select max(payments.paid_at) as ts from payments
                        where payments.customer_id = customers.id
                    union all
                    select max(activities.created_at) as ts from activities
                        where activities.subject_id = customers.id and activities.subject_type = ?
                ) u) as last_transaction_at',
                [$morphCustomer],
            );

        $query
            ->when($request->filled('search'), function ($q) use ($request): void {
                $s = '%'.$request->string('search')->toString().'%';
                $q->where(function ($w) use ($s): void {
                    $w->where('name', 'like', $s)
                        ->orWhere('company_name', 'like', $s)
                        ->orWhere('email', 'like', $s)
                        ->orWhere('phone', 'like', $s);
                });
            })
            ->when($request->filled('segment'), fn ($q) => $q->where('segment', $request->string('segment')))
            ->when($request->filled('assigned_user_id'), fn ($q) => $q->where('assigned_user_id', $request->integer('assigned_user_id')))
            ->when($request->filled('policy_payment_status'), function ($q) use ($request): void {
                $q->whereHas('policies', fn ($p) => $p->where(
                    'premium_payment_status',
                    $request->string('policy_payment_status')->toString(),
                ));
            })
            ->when($request->filled('renewal_within_days'), function ($q) use ($request): void {
                $days = max(1, min(365, $request->integer('renewal_within_days')));
                $q->whereHas('policies', function ($p) use ($days): void {
                    $p->whereNotNull('ends_at')
                        ->where('ends_at', '>=', now()->startOfDay())
                        ->where('ends_at', '<=', now()->addDays($days)->endOfDay());
                });
            });

        $customers = $query
            ->orderByDesc('last_transaction_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $customerDetail = null;
        if ($request->filled('customer')) {
            $cid = $request->integer('customer');
            $customer = Customer::query()->whereKey($cid)->first();
            if ($customer) {
                $customerDetail = $this->formatCustomerDetail($customer);
            }
        }

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'segment' => $request->string('segment')->toString(),
                'assigned_user_id' => $request->input('assigned_user_id'),
                'policy_payment_status' => $request->string('policy_payment_status')->toString(),
                'renewal_within_days' => $request->input('renewal_within_days'),
                'customer' => $request->input('customer'),
            ],
            'customerDetail' => $customerDetail,
            'kpi' => $this->customerKpi(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Customers/Create', [
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = Customer::query()->create($request->validated());
        $this->activityLogger->log($customer, 'created', 'Müşteri kaydı oluşturuldu');

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Müşteri kaydedildi.');
    }

    public function show(Customer $customer): Response
    {
        $customer->load([
            'assignedUser:id,name,email',
            'addresses',
            'contacts',
            'notes' => fn ($q) => $q->latest()->limit(30)->with('user:id,name'),
            'activities' => fn ($q) => $q->latest()->limit(40)->with('user:id,name'),
        ]);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
        ]);
    }

    public function edit(Customer $customer): Response
    {
        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());
        $this->activityLogger->log($customer, 'updated', 'Müşteri bilgileri güncellendi');

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Müşteri güncellendi.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Müşteri arşivlendi.');
    }

    public function storeNote(StoreCustomerNoteRequest $request, Customer $customer): RedirectResponse
    {
        $customer->notes()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);
        $this->activityLogger->log($customer, 'note_added', 'Not eklendi');

        return back()->with('success', 'Not eklendi.');
    }

    public function storeDocument(StoreCustomerDocumentRequest $request, Customer $customer): RedirectResponse
    {
        $file = $request->file('file');
        $path = $file->store('customer-documents/'.$customer->id, 'public');

        $document = $customer->documents()->create([
            'category' => $request->validated('category') ?? 'general',
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'uploaded_by_user_id' => $request->user()->id,
        ]);

        $this->activityLogger->log($customer, 'document_added', 'Evrak yüklendi', [
            'document_id' => $document->id,
            'name' => $document->original_name,
        ]);

        return back()->with('success', 'Dosya yüklendi.');
    }

    public function destroyDocument(Request $request, Document $document): RedirectResponse
    {
        $document->loadMissing('documentable');
        if ($document->documentable_type !== (new Customer)->getMorphClass()
            || ! $document->documentable instanceof Customer) {
            abort(404);
        }

        if ($document->disk && $document->path) {
            Storage::disk($document->disk)->delete($document->path);
        }

        $this->activityLogger->log($document->documentable, 'document_removed', 'Evrak silindi', [
            'document_id' => $document->id,
        ]);

        $document->delete();

        return back()->with('success', 'Dosya kaldırıldı.');
    }

    /**
     * @return array<string, int|float>
     */
    private function customerKpi(): array
    {
        $total = Customer::query()->count();

        $active = Customer::query()
            ->whereHas('policies', function ($p): void {
                $p->where(function ($q): void {
                    $q->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', now()->startOfDay());
                });
            })
            ->distinct()
            ->count('customers.id');

        $newThisMonth = Customer::query()
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $risky = Customer::query()
            ->whereHas('policies', function ($p): void {
                $p->where(function ($q): void {
                    $q->whereBetween('ends_at', [
                        now()->startOfDay(),
                        now()->addDays(60)->endOfDay(),
                    ])
                        ->orWhereIn('premium_payment_status', ['pending', 'partial']);
                });
            })
            ->distinct()
            ->count('customers.id');

        return [
            'total' => $total,
            'active' => $active,
            'newThisMonth' => $newThisMonth,
            'risky' => $risky,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatCustomerDetail(Customer $customer): array
    {
        $customer->load(['assignedUser:id,name,email']);

        $policies = $customer->policies()
            ->with(['policyType:id,name', 'insuranceCompany:id,name'])
            ->orderByRaw('case when ends_at is null or ends_at >= ? then 0 else 1 end', [now()->toDateString()])
            ->orderBy('ends_at')
            ->get();

        $totalPremium = (float) $policies->sum('premium_amount');
        $totalCommission = (float) $policies->sum('commission_amount');
        $commissionCollected = (float) $policies->sum('commission_collected');
        $paymentsTotal = (float) $customer->payments()->sum('amount');

        $renewalAlerts = $policies->filter(function ($p) {
            if (! $p->ends_at || $p->renewal_status === 'renewed') {
                return false;
            }

            return $p->ends_at->lte(now()->addDays(90)) && $p->ends_at->gte(now()->startOfDay());
        })->values();

        $notes = $customer->notes()
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
            ->where('subject_type', $customer->getMorphClass())
            ->where('subject_id', $customer->id)
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

        $documents = $customer->documents()
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

        return [
            'id' => $customer->id,
            'type' => $customer->type,
            'name' => $customer->name,
            'company_name' => $customer->company_name,
            'display_name' => $customer->displayName(),
            'phone' => $customer->phone,
            'email' => $customer->email,
            'segment' => $customer->segment,
            'assigned_user_id' => $customer->assigned_user_id,
            'assigned_user' => $customer->assignedUser,
            'finance' => [
                'total_premium' => $totalPremium,
                'total_commission' => $totalCommission,
                'commission_collected' => $commissionCollected,
                'commission_pending' => max(0, $totalCommission - $commissionCollected),
                'payments_total' => $paymentsTotal,
            ],
            'policies' => $policies->map(fn ($p) => [
                'id' => $p->id,
                'policy_number' => $p->policy_number,
                'starts_at' => $p->starts_at?->toDateString(),
                'ends_at' => $p->ends_at?->toDateString(),
                'premium_amount' => $p->premium_amount,
                'premium_payment_status' => $p->premium_payment_status,
                'renewal_status' => $p->renewal_status,
                'policy_type' => $p->policyType?->name,
                'insurance_company' => $p->insuranceCompany?->name,
                'is_active' => ! $p->ends_at || $p->ends_at->gte(now()->startOfDay()),
            ]),
            'renewal_alerts' => $renewalAlerts->map(fn ($p) => [
                'id' => $p->id,
                'policy_number' => $p->policy_number,
                'ends_at' => $p->ends_at?->toDateString(),
                'renewal_status' => $p->renewal_status,
                'days_left' => $p->ends_at && $p->ends_at->isFuture()
                    ? (int) now()->startOfDay()->diffInDays($p->ends_at->copy()->startOfDay())
                    : null,
            ]),
            'notes' => $notes,
            'activities' => $activities,
            'documents' => $documents,
        ];
    }
}
