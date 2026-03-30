<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import {
    URGENCY_LABELS,
    activityPolicyLabel,
    daysLeftLabel,
    formatMoneyTr,
} from '@/lib/policyLabels';
import { digitsPhone, waLink } from '@/lib/leadLabels';
import { Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowPathIcon,
    ArrowTopRightOnSquareIcon,
    BellAlertIcon,
    DevicePhoneMobileIcon,
    DocumentTextIcon,
    PhoneIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import { computed, watch } from 'vue';

const props = defineProps({
    policyDetail: { type: Object, default: null },
    filters: { type: Object, default: () => ({}) },
});

const noteForm = useForm({ body: '' });
const docForm = useForm({ file: null, category: 'general' });
const renewalForm = useForm({
    renewal_status: '',
    renewal_pipeline_stage: '',
});

const open = computed(() => !!props.policyDetail);

const reloadOnly = ['policyDetail', 'policies', 'kpi', 'criticalAlerts', 'flash'];

watch(
    () => props.policyDetail?.id,
    () => {
        noteForm.reset();
        noteForm.clearErrors();
        docForm.reset();
        docForm.clearErrors();
        if (props.policyDetail) {
            renewalForm.renewal_status = props.policyDetail.renewal_status;
            renewalForm.renewal_pipeline_stage = props.policyDetail.renewal_pipeline_stage ?? '';
            renewalForm.clearErrors();
        }
    },
);

function closeDrawer() {
    const next = { ...props.filters };
    delete next.policy;
    router.get(route('policies.index'), next, {
        replace: true,
        preserveScroll: true,
        preserveState: true,
        only: ['policyDetail', 'filters', 'flash'],
    });
}

function formatTs(iso) {
    if (!iso) {
        return '—';
    }
    return iso.replace('T', ' ').slice(0, 16);
}

function submitNote() {
    if (!props.policyDetail) {
        return;
    }
    noteForm.post(route('policies.notes.store', props.policyDetail.id), {
        preserveScroll: true,
        only: reloadOnly,
        onSuccess: () => noteForm.reset(),
    });
}

function onDocPick(e) {
    docForm.file = e.target.files?.[0] ?? null;
}

function submitDoc() {
    if (!props.policyDetail || !docForm.file) {
        return;
    }
    docForm.post(route('policies.documents.store', props.policyDetail.id), {
        preserveScroll: true,
        forceFormData: true,
        only: reloadOnly,
        onSuccess: () => {
            docForm.reset();
            const el = document.getElementById('policy-doc-input');
            if (el) {
                el.value = '';
            }
        },
    });
}

function removeDoc(doc) {
    if (!confirm('Bu dosyayı silmek istediğinize emin misiniz?')) {
        return;
    }
    router.delete(route('policies.documents.destroy', doc.id), {
        preserveScroll: true,
        only: reloadOnly,
    });
}

function saveRenewal() {
    if (!props.policyDetail) {
        return;
    }
    renewalForm.patch(route('policies.renewal.update', props.policyDetail.id), {
        preserveScroll: true,
        only: reloadOnly,
    });
}

function startRenewal() {
    if (!props.policyDetail) {
        return;
    }
    router.post(
        route('policies.renewal.start', props.policyDetail.id),
        {},
        { preserveScroll: true, only: reloadOnly },
    );
}

function sendReminder() {
    if (!props.policyDetail) {
        return;
    }
    router.post(
        route('policies.reminder', props.policyDetail.id),
        {},
        { preserveScroll: true, only: reloadOnly },
    );
}

const teklifUrl = computed(() => {
    if (!props.policyDetail?.customer?.id) {
        return route('policies.create');
    }
    return route('policies.create', { customer_id: props.policyDetail.customer.id });
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-300 ease-out"
            leave-active-class="transition-opacity duration-200 ease-in"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-[70] flex justify-end bg-slate-900/50 backdrop-blur-md"
                @click.self="closeDrawer"
            >
                <Transition
                    enter-active-class="transition-transform duration-300 ease-out"
                    leave-active-class="transition-transform duration-200 ease-in"
                    enter-from-class="translate-x-full"
                    leave-to-class="translate-x-full"
                >
                    <aside
                        v-if="open && policyDetail"
                        class="flex h-full w-full max-w-lg flex-col rounded-l-2xl border-l border-slate-200/90 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-950"
                        @click.stop
                    >
                        <header
                            class="flex shrink-0 items-start justify-between gap-3 border-b border-slate-100 px-6 py-5 dark:border-slate-800"
                        >
                            <div class="min-w-0 flex-1">
                                <span
                                    class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold"
                                    :class="policyDetail.urgency_badge_class"
                                >
                                    {{ URGENCY_LABELS[policyDetail.urgency] ?? policyDetail.urgency }}
                                </span>
                                <h2
                                    class="mt-2 text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                                >
                                    {{ policyDetail.policy_number }}
                                </h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ policyDetail.policy_type?.name }} ·
                                    {{ policyDetail.insurance_company?.name }}
                                </p>
                                <p class="mt-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Kalan: {{ daysLeftLabel(policyDetail) }}
                                </p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span
                                        v-for="ins in policyDetail.insight_badges || []"
                                        :key="ins.text"
                                        class="inline-flex max-w-full truncate rounded-xl px-2.5 py-1 text-[11px] font-semibold"
                                        :class="
                                            ins.type === 'danger'
                                                ? 'bg-red-100 text-red-800 dark:bg-red-950/40 dark:text-red-200'
                                                : ins.type === 'warning'
                                                  ? 'bg-amber-100 text-amber-900 dark:bg-amber-950/40 dark:text-amber-200'
                                                  : ins.type === 'finance'
                                                    ? 'bg-violet-100 text-violet-800 dark:bg-violet-950/40 dark:text-violet-200'
                                                    : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
                                        "
                                    >
                                        {{ ins.text }}
                                    </span>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 dark:hover:bg-slate-800"
                                aria-label="Kapat"
                                @click="closeDrawer"
                            >
                                <XMarkIcon class="h-6 w-6" />
                            </button>
                        </header>

                        <div class="flex-1 overflow-y-auto px-6 py-6">
                            <section>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Genel</h3>
                                <dl class="mt-3 space-y-2 text-sm">
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-slate-500">Başlangıç</dt>
                                        <dd class="font-medium">{{ policyDetail.starts_at ?? '—' }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-slate-500">Bitiş</dt>
                                        <dd class="font-medium">{{ policyDetail.ends_at ?? '—' }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-slate-500">Temsilci</dt>
                                        <dd class="font-medium">{{ policyDetail.owner?.name ?? '—' }}</dd>
                                    </div>
                                </dl>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <Link
                                        :href="route('policies.edit', policyDetail.id)"
                                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 dark:border-slate-600 dark:text-slate-200"
                                    >
                                        Poliçeyi düzenle
                                    </Link>
                                    <a
                                        v-if="policyDetail.pdf_url"
                                        :href="policyDetail.pdf_url"
                                        target="_blank"
                                        rel="noopener"
                                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 dark:border-slate-600 dark:text-slate-200"
                                    >
                                        <DocumentTextIcon class="h-4 w-4" />
                                        PDF
                                    </a>
                                </div>
                            </section>

                            <section
                                v-if="policyDetail.customer"
                                class="mt-8 border-t border-slate-100 pt-8 dark:border-slate-800"
                            >
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Müşteri</h3>
                                <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">
                                    {{ policyDetail.customer.display_name }}
                                </p>
                                <p class="mt-2 text-sm">
                                    <a
                                        v-if="policyDetail.customer.phone"
                                        :href="'tel:' + digitsPhone(policyDetail.customer.phone)"
                                        class="font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                                    >
                                        {{ policyDetail.customer.phone }}
                                    </a>
                                    <span v-else class="text-slate-400">—</span>
                                </p>
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                                    {{ policyDetail.customer.email || '—' }}
                                </p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a
                                        v-if="policyDetail.customer.phone"
                                        :href="'tel:' + digitsPhone(policyDetail.customer.phone)"
                                        class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white dark:bg-white dark:text-slate-900"
                                    >
                                        <PhoneIcon class="h-4 w-4" />
                                        Ara
                                    </a>
                                    <a
                                        v-if="policyDetail.customer.phone"
                                        :href="waLink(policyDetail.customer.phone)"
                                        target="_blank"
                                        rel="noopener"
                                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200"
                                    >
                                        <DevicePhoneMobileIcon class="h-4 w-4" />
                                        WhatsApp
                                    </a>
                                    <Link
                                        :href="route('customers.index', { customer: policyDetail.customer.id })"
                                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 dark:border-slate-600 dark:text-slate-200"
                                    >
                                        <ArrowTopRightOnSquareIcon class="h-4 w-4" />
                                        Müşteri 360
                                    </Link>
                                </div>
                            </section>

                            <section class="mt-8 border-t border-slate-100 pt-8 dark:border-slate-800">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Finans</h3>
                                <div class="mt-4 grid grid-cols-2 gap-3">
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-3 dark:border-slate-800 dark:bg-slate-900/40">
                                        <p class="text-[10px] font-semibold uppercase text-slate-500">Prim</p>
                                        <p class="mt-1 font-bold tabular-nums">
                                            {{ formatMoneyTr(policyDetail.finance?.premium) }}
                                        </p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-3 dark:border-slate-800 dark:bg-slate-900/40">
                                        <p class="text-[10px] font-semibold uppercase text-slate-500">Komisyon</p>
                                        <p class="mt-1 font-bold tabular-nums text-violet-700 dark:text-violet-300">
                                            {{ formatMoneyTr(policyDetail.finance?.commission) }}
                                        </p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-3 dark:border-slate-800 dark:bg-slate-900/40">
                                        <p class="text-[10px] font-semibold uppercase text-slate-500">
                                            Tahsil edilen
                                        </p>
                                        <p class="mt-1 font-semibold tabular-nums text-emerald-700 dark:text-emerald-300">
                                            {{ formatMoneyTr(policyDetail.finance?.commission_collected) }}
                                        </p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-3 dark:border-slate-800 dark:bg-slate-900/40">
                                        <p class="text-[10px] font-semibold uppercase text-slate-500">
                                            Kalan komisyon
                                        </p>
                                        <p class="mt-1 font-semibold tabular-nums text-amber-700 dark:text-amber-300">
                                            {{ formatMoneyTr(policyDetail.finance?.commission_pending) }}
                                        </p>
                                    </div>
                                    <div class="col-span-2 rounded-2xl border border-indigo-100 bg-indigo-50/60 p-3 dark:border-indigo-900 dark:bg-indigo-950/30">
                                        <p class="text-[10px] font-semibold uppercase text-indigo-800 dark:text-indigo-200">
                                            Tahsilatlar toplamı / prim bakiyesi (tahmini)
                                        </p>
                                        <p class="mt-1 text-sm text-indigo-900 dark:text-indigo-100">
                                            Ödeme:
                                            {{ formatMoneyTr(policyDetail.finance?.payments_total) }} · Kalan:
                                            {{ formatMoneyTr(policyDetail.finance?.premium_balance_estimate) }}
                                        </p>
                                        <p
                                            v-if="policyDetail.finance?.installments_pending_total > 0"
                                            class="mt-1 text-xs text-indigo-800 dark:text-indigo-200"
                                        >
                                            Bekleyen taksit tutarı:
                                            {{ formatMoneyTr(policyDetail.finance?.installments_pending_total) }}
                                        </p>
                                    </div>
                                </div>
                            </section>

                            <section class="mt-8 border-t border-slate-100 pt-8 dark:border-slate-800">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Yenileme</h3>
                                <form class="mt-4 space-y-3" @submit.prevent="saveRenewal">
                                    <div>
                                        <InputLabel value="Durum" />
                                        <select
                                            v-model="renewalForm.renewal_status"
                                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                        >
                                            <option value="active">Devam ediyor</option>
                                            <option value="pending_renewal">Bekliyor</option>
                                            <option value="renewed">Yenilendi</option>
                                            <option value="not_renewed">Yenilenmedi</option>
                                        </select>
                                        <InputError :message="renewalForm.errors.renewal_status" />
                                    </div>
                                    <div>
                                        <InputLabel value="Pipeline aşaması (opsiyonel)" />
                                        <input
                                            v-model="renewalForm.renewal_pipeline_stage"
                                            type="text"
                                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                            placeholder="örn. teklif, imza"
                                        />
                                        <InputError :message="renewalForm.errors.renewal_pipeline_stage" />
                                    </div>
                                    <PrimaryButton type="submit" class="rounded-xl" :disabled="renewalForm.processing">
                                        Yenileme durumunu kaydet
                                    </PrimaryButton>
                                </form>
                                <div class="mt-4 flex flex-col gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-amber-600"
                                        @click="startRenewal"
                                    >
                                        <ArrowPathIcon class="h-4 w-4" />
                                        Yenileme başlat
                                    </button>
                                    <Link
                                        :href="teklifUrl"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-violet-300 bg-violet-50 px-4 py-2.5 text-sm font-semibold text-violet-900 dark:border-violet-800 dark:bg-violet-950/40 dark:text-violet-100"
                                    >
                                        Teklif oluştur (yeni poliçe)
                                    </Link>
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-800"
                                        @click="sendReminder"
                                    >
                                        <BellAlertIcon class="h-4 w-4" />
                                        Hatırlatma gönder (kayıt)
                                    </button>
                                    <p
                                        v-if="policyDetail.last_renewal_reminder_at"
                                        class="text-xs text-slate-500"
                                    >
                                        Son hatırlatma: {{ formatTs(policyDetail.last_renewal_reminder_at) }}
                                    </p>
                                </div>
                            </section>

                            <section class="mt-8 border-t border-slate-100 pt-8 dark:border-slate-800">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                    Çapraz satış önerileri
                                </h3>
                                <ul class="mt-3 list-inside list-disc space-y-2 text-sm text-slate-700 dark:text-slate-200">
                                    <li v-for="(line, idx) in policyDetail.cross_sell_suggestions || []" :key="idx">
                                        {{ line }}
                                    </li>
                                </ul>
                            </section>

                            <section class="mt-8 border-t border-slate-100 pt-8 dark:border-slate-800">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Notlar</h3>
                                <form class="mt-4 space-y-2" @submit.prevent="submitNote">
                                    <textarea
                                        v-model="noteForm.body"
                                        rows="3"
                                        placeholder="Not…"
                                        class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                    <InputError :message="noteForm.errors.body" />
                                    <PrimaryButton type="submit" class="rounded-xl" :disabled="noteForm.processing">
                                        Not ekle
                                    </PrimaryButton>
                                </form>
                                <ul class="mt-6 space-y-4">
                                    <li
                                        v-for="n in policyDetail.notes || []"
                                        :key="n.id"
                                        class="border-l-2 border-indigo-200 pl-4 dark:border-indigo-800"
                                    >
                                        <p class="text-xs text-slate-500">
                                            {{ formatTs(n.created_at) }}
                                            <span v-if="n.user"> · {{ n.user.name }}</span>
                                        </p>
                                        <p class="mt-1 whitespace-pre-wrap text-sm text-slate-700 dark:text-slate-200">
                                            {{ n.body }}
                                        </p>
                                    </li>
                                </ul>
                            </section>

                            <section class="mt-8 border-t border-slate-100 pt-8 dark:border-slate-800">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Aktivite</h3>
                                <ul class="mt-4 space-y-4">
                                    <li
                                        v-for="a in policyDetail.activities || []"
                                        :key="a.id"
                                        class="border-l-2 border-slate-200 pl-4 dark:border-slate-700"
                                    >
                                        <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                                            {{ activityPolicyLabel(a.action) }}
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            {{ formatTs(a.created_at) }}
                                            <span v-if="a.user"> · {{ a.user.name }}</span>
                                        </p>
                                        <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                                            {{ a.description }}
                                        </p>
                                    </li>
                                </ul>
                            </section>

                            <section class="mt-8 border-t border-slate-100 pt-8 dark:border-slate-800">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Evraklar</h3>
                                <p v-if="policyDetail.pdf_url" class="mt-2 text-sm">
                                    <a
                                        :href="policyDetail.pdf_url"
                                        target="_blank"
                                        rel="noopener"
                                        class="font-semibold text-indigo-600 hover:underline dark:text-indigo-400"
                                    >
                                        Ana poliçe PDF
                                    </a>
                                </p>
                                <form class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end" @submit.prevent="submitDoc">
                                    <div class="flex-1">
                                        <InputLabel value="Dosya yükle" />
                                        <input
                                            id="policy-doc-input"
                                            type="file"
                                            class="mt-1 w-full text-sm file:mr-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 dark:file:bg-indigo-950/50 dark:file:text-indigo-200"
                                            @change="onDocPick"
                                        />
                                        <InputError :message="docForm.errors.file" />
                                    </div>
                                    <PrimaryButton type="submit" class="rounded-xl" :disabled="docForm.processing || !docForm.file">
                                        Yükle
                                    </PrimaryButton>
                                </form>
                                <ul class="mt-4 space-y-2">
                                    <li
                                        v-for="d in policyDetail.documents || []"
                                        :key="d.id"
                                        class="flex items-center justify-between gap-2 rounded-xl border border-slate-100 px-3 py-2 text-sm dark:border-slate-800"
                                    >
                                        <a
                                            :href="d.url"
                                            target="_blank"
                                            rel="noopener"
                                            class="min-w-0 truncate font-medium text-indigo-600 dark:text-indigo-400"
                                        >
                                            {{ d.original_name }}
                                        </a>
                                        <DangerButton type="button" class="shrink-0 rounded-lg px-2 py-1 text-xs" @click="removeDoc(d)">
                                            Sil
                                        </DangerButton>
                                    </li>
                                </ul>
                            </section>
                        </div>
                    </aside>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
