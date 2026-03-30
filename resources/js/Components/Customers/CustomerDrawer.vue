<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import {
    PAYMENT_STATUS_LABELS,
    RENEWAL_STATUS_LABELS,
    SEGMENT_LABELS,
    activityCustomerLabel,
    formatMoneyTr,
    segmentBadgeClass,
} from '@/lib/customerLabels';
import { digitsPhone, waLink } from '@/lib/leadLabels';
import { Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowTopRightOnSquareIcon,
    DevicePhoneMobileIcon,
    PhoneIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import { computed, watch } from 'vue';

const props = defineProps({
    customerDetail: { type: Object, default: null },
    filters: { type: Object, default: () => ({}) },
});

const noteForm = useForm({ body: '' });
const docForm = useForm({ file: null, category: 'general' });

const open = computed(() => !!props.customerDetail);

const reloadOnly = ['customerDetail', 'customers', 'kpi', 'flash'];

watch(
    () => props.customerDetail?.id,
    () => {
        noteForm.reset();
        noteForm.clearErrors();
        docForm.reset();
        docForm.clearErrors();
    },
);

function closeDrawer() {
    const next = { ...props.filters };
    delete next.customer;
    router.get(route('customers.index'), next, {
        replace: true,
        preserveScroll: true,
        preserveState: true,
        only: ['customerDetail', 'filters', 'flash'],
    });
}

function formatTs(iso) {
    if (!iso) {
        return '—';
    }
    return iso.replace('T', ' ').slice(0, 16);
}

function initials(name) {
    if (!name) {
        return '?';
    }
    const p = name.trim().split(/\s+/);
    if (p.length === 1) {
        return p[0].slice(0, 2).toUpperCase();
    }
    return (p[0][0] + p[p.length - 1][0]).toUpperCase();
}

function submitNote() {
    if (!props.customerDetail) {
        return;
    }
    noteForm.post(route('customers.notes.store', props.customerDetail.id), {
        preserveScroll: true,
        only: reloadOnly,
        onSuccess: () => noteForm.reset(),
    });
}

function onDocPick(e) {
    docForm.file = e.target.files?.[0] ?? null;
}

function submitDoc() {
    if (!props.customerDetail || !docForm.file) {
        return;
    }
    docForm.post(route('customers.documents.store', props.customerDetail.id), {
        preserveScroll: true,
        forceFormData: true,
        only: reloadOnly,
        onSuccess: () => {
            docForm.reset();
            if (typeof document !== 'undefined') {
                const el = document.getElementById('customer-doc-input');
                if (el) {
                    el.value = '';
                }
            }
        },
    });
}

function removeDoc(doc) {
    if (!confirm('Bu dosyayı silmek istediğinize emin misiniz?')) {
        return;
    }
    router.delete(route('customers.documents.destroy', doc.id), {
        preserveScroll: true,
        only: reloadOnly,
    });
}

function policyShowUrl(id) {
    return route('policies.show', id);
}
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
                        v-if="open && customerDetail"
                        class="flex h-full w-full max-w-lg flex-col rounded-l-2xl border-l border-slate-200/90 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-950"
                        @click.stop
                    >
                        <header
                            class="flex shrink-0 items-start justify-between gap-3 border-b border-slate-100 px-6 py-5 dark:border-slate-800"
                        >
                            <div class="min-w-0 flex-1">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold capitalize ring-1 ring-inset"
                                    :class="segmentBadgeClass(customerDetail.segment)"
                                >
                                    {{ SEGMENT_LABELS[customerDetail.segment] ?? customerDetail.segment }}
                                </span>
                                <h2
                                    class="mt-2 text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                                >
                                    {{ customerDetail.display_name }}
                                </h2>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    #{{ customerDetail.id }} ·
                                    {{ customerDetail.type === 'corporate' ? 'Kurumsal' : 'Bireysel' }}
                                </p>
                            </div>
                            <button
                                type="button"
                                class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 active:scale-95 dark:hover:bg-slate-800"
                                aria-label="Kapat"
                                @click="closeDrawer"
                            >
                                <XMarkIcon class="h-6 w-6" />
                            </button>
                        </header>

                        <div class="flex-1 overflow-y-auto px-6 py-6">
                            <div class="flex flex-wrap gap-2">
                                <a
                                    v-if="customerDetail.phone"
                                    :href="'tel:' + digitsPhone(customerDetail.phone)"
                                    class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 dark:bg-white dark:text-slate-900"
                                >
                                    <PhoneIcon class="h-4 w-4" />
                                    Ara
                                </a>
                                <a
                                    v-if="customerDetail.phone"
                                    :href="waLink(customerDetail.phone)"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200"
                                >
                                    <DevicePhoneMobileIcon class="h-4 w-4" />
                                    WhatsApp
                                </a>
                                <Link
                                    :href="route('policies.create', { customer_id: customerDetail.id })"
                                    class="inline-flex items-center gap-2 rounded-xl border border-violet-200 bg-violet-50 px-4 py-2.5 text-sm font-semibold text-violet-900 dark:border-violet-900 dark:bg-violet-950/40 dark:text-violet-100"
                                >
                                    Poliçe ekle
                                </Link>
                                <Link
                                    :href="route('customers.edit', customerDetail.id)"
                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:border-slate-600 dark:text-slate-200"
                                >
                                    <ArrowTopRightOnSquareIcon class="h-4 w-4" />
                                    Düzenle
                                </Link>
                            </div>

                            <section class="mt-8 space-y-4 border-t border-slate-100 pt-8 dark:border-slate-800">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">İletişim</h3>
                                <dl class="grid gap-3 text-sm">
                                    <div>
                                        <dt class="text-xs font-semibold uppercase text-slate-400">Telefon</dt>
                                        <dd class="mt-1 font-medium text-slate-800 dark:text-slate-100">
                                            <a
                                                v-if="customerDetail.phone"
                                                :href="'tel:' + digitsPhone(customerDetail.phone)"
                                                class="text-indigo-600 hover:underline dark:text-indigo-400"
                                            >
                                                {{ customerDetail.phone }}
                                            </a>
                                            <span v-else class="text-slate-400">—</span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase text-slate-400">E-posta</dt>
                                        <dd class="mt-1 text-slate-700 dark:text-slate-200">
                                            {{ customerDetail.email || '—' }}
                                        </dd>
                                    </div>
                                </dl>
                                <div
                                    v-if="customerDetail.assigned_user"
                                    class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-900/40"
                                >
                                    <span
                                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white shadow"
                                    >
                                        {{ initials(customerDetail.assigned_user.name) }}
                                    </span>
                                    <div>
                                        <p class="text-xs font-semibold uppercase text-slate-400">Temsilci</p>
                                        <p class="font-semibold text-slate-900 dark:text-white">
                                            {{ customerDetail.assigned_user.name }}
                                        </p>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-slate-500">Atanan temsilci yok.</p>
                            </section>

                            <section class="mt-8 border-t border-slate-100 pt-8 dark:border-slate-800">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Finans özeti</h3>
                                <div class="mt-4 grid grid-cols-2 gap-3">
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-900/40">
                                        <p class="text-[11px] font-semibold uppercase text-slate-500">
                                            Toplam prim (ciro)
                                        </p>
                                        <p class="mt-1 text-lg font-bold tabular-nums text-slate-900 dark:text-white">
                                            {{ formatMoneyTr(customerDetail.finance?.total_premium) }}
                                        </p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-900/40">
                                        <p class="text-[11px] font-semibold uppercase text-slate-500">Komisyon</p>
                                        <p class="mt-1 text-lg font-bold tabular-nums text-violet-700 dark:text-violet-300">
                                            {{ formatMoneyTr(customerDetail.finance?.total_commission) }}
                                        </p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-900/40">
                                        <p class="text-[11px] font-semibold uppercase text-slate-500">
                                            Tahsil edilen kom.
                                        </p>
                                        <p class="mt-1 font-semibold tabular-nums text-emerald-700 dark:text-emerald-300">
                                            {{ formatMoneyTr(customerDetail.finance?.commission_collected) }}
                                        </p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-900/40">
                                        <p class="text-[11px] font-semibold uppercase text-slate-500">
                                            Bekleyen kom.
                                        </p>
                                        <p class="mt-1 font-semibold tabular-nums text-amber-700 dark:text-amber-300">
                                            {{ formatMoneyTr(customerDetail.finance?.commission_pending) }}
                                        </p>
                                    </div>
                                    <div class="col-span-2 rounded-2xl border border-indigo-100 bg-indigo-50/60 p-4 dark:border-indigo-900 dark:bg-indigo-950/30">
                                        <p class="text-[11px] font-semibold uppercase text-indigo-800 dark:text-indigo-200">
                                            Ödemeler (kayıtlı tahsilat)
                                        </p>
                                        <p class="mt-1 text-xl font-bold tabular-nums text-indigo-900 dark:text-indigo-100">
                                            {{ formatMoneyTr(customerDetail.finance?.payments_total) }}
                                        </p>
                                    </div>
                                </div>
                            </section>

                            <section
                                v-if="(customerDetail.renewal_alerts || []).length"
                                class="mt-8 border-t border-slate-100 pt-8 dark:border-slate-800"
                            >
                                <h3 class="text-sm font-bold text-amber-900 dark:text-amber-200">
                                    Yenileme uyarıları
                                </h3>
                                <ul class="mt-3 space-y-2">
                                    <li
                                        v-for="r in customerDetail.renewal_alerts"
                                        :key="r.id"
                                        class="rounded-xl border border-amber-200 bg-amber-50/80 px-3 py-2 text-sm dark:border-amber-900 dark:bg-amber-950/30"
                                    >
                                        <Link
                                            :href="policyShowUrl(r.id)"
                                            class="font-semibold text-amber-950 hover:underline dark:text-amber-100"
                                        >
                                            {{ r.policy_number }}
                                        </Link>
                                        <span class="text-amber-800 dark:text-amber-200">
                                            · Bitiş {{ r.ends_at }}
                                            <span v-if="r.days_left != null" class="font-medium">
                                                ({{ r.days_left }} gün)
                                            </span>
                                        </span>
                                    </li>
                                </ul>
                            </section>

                            <section class="mt-8 border-t border-slate-100 pt-8 dark:border-slate-800">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Poliçeler</h3>
                                <ul class="mt-4 space-y-3">
                                    <li
                                        v-for="p in customerDetail.policies || []"
                                        :key="p.id"
                                        class="rounded-2xl border border-slate-100 p-4 dark:border-slate-800"
                                    >
                                        <div class="flex items-start justify-between gap-2">
                                            <Link
                                                :href="policyShowUrl(p.id)"
                                                class="font-semibold text-indigo-600 hover:underline dark:text-indigo-400"
                                            >
                                                {{ p.policy_number }}
                                            </Link>
                                            <span
                                                class="shrink-0 rounded-lg px-2 py-0.5 text-[10px] font-bold uppercase"
                                                :class="
                                                    p.is_active
                                                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200'
                                                        : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
                                                "
                                            >
                                                {{ p.is_active ? 'Aktif' : 'Süresi geçmiş' }}
                                            </span>
                                        </div>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ p.policy_type || '—' }} · {{ p.insurance_company || '—' }}
                                        </p>
                                        <p class="mt-2 text-xs text-slate-600 dark:text-slate-300">
                                            Bitiş:
                                            <span class="font-medium">{{ p.ends_at || '—' }}</span>
                                            · Ödeme:
                                            {{
                                                PAYMENT_STATUS_LABELS[p.premium_payment_status] ??
                                                p.premium_payment_status
                                            }}
                                            · Yenileme:
                                            {{ RENEWAL_STATUS_LABELS[p.renewal_status] ?? p.renewal_status }}
                                        </p>
                                        <p class="mt-1 text-sm font-semibold tabular-nums text-slate-800 dark:text-slate-100">
                                            {{ formatMoneyTr(p.premium_amount) }}
                                        </p>
                                    </li>
                                    <li
                                        v-if="!(customerDetail.policies || []).length"
                                        class="text-sm text-slate-400"
                                    >
                                        Henüz poliçe yok.
                                    </li>
                                </ul>
                            </section>

                            <section class="mt-8 border-t border-slate-100 pt-8 dark:border-slate-800">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Notlar</h3>
                                <form class="mt-4 space-y-2" @submit.prevent="submitNote">
                                    <textarea
                                        v-model="noteForm.body"
                                        rows="3"
                                        placeholder="Not yazın…"
                                        class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                    <InputError :message="noteForm.errors.body" />
                                    <PrimaryButton type="submit" class="rounded-xl" :disabled="noteForm.processing">
                                        Not ekle
                                    </PrimaryButton>
                                </form>
                                <ul class="mt-6 space-y-4">
                                    <li
                                        v-for="n in customerDetail.notes || []"
                                        :key="n.id"
                                        class="relative border-l-2 border-indigo-200 pl-4 dark:border-indigo-800"
                                    >
                                        <p class="text-xs text-slate-500">
                                            {{ formatTs(n.created_at) }}
                                            <span v-if="n.user"> · {{ n.user.name }}</span>
                                        </p>
                                        <p
                                            class="mt-1 whitespace-pre-wrap text-sm text-slate-700 dark:text-slate-200"
                                        >
                                            {{ n.body }}
                                        </p>
                                    </li>
                                </ul>
                            </section>

                            <section class="mt-8 border-t border-slate-100 pt-8 dark:border-slate-800">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Aktivite</h3>
                                <ul class="mt-4 space-y-4">
                                    <li
                                        v-for="a in customerDetail.activities || []"
                                        :key="a.id"
                                        class="border-l-2 border-slate-200 pl-4 dark:border-slate-700"
                                    >
                                        <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                                            {{ activityCustomerLabel(a.action) }}
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
                                <form class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end" @submit.prevent="submitDoc">
                                    <div class="flex-1">
                                        <InputLabel value="Dosya" />
                                        <input
                                            id="customer-doc-input"
                                            type="file"
                                            class="mt-1 w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 dark:text-slate-300 dark:file:bg-indigo-950/50 dark:file:text-indigo-200"
                                            @change="onDocPick"
                                        />
                                        <InputError :message="docForm.errors.file" />
                                    </div>
                                    <PrimaryButton type="submit" class="rounded-xl" :disabled="docForm.processing || !docForm.file">
                                        Yükle
                                    </PrimaryButton>
                                </form>
                                <ul class="mt-6 space-y-2">
                                    <li
                                        v-for="d in customerDetail.documents || []"
                                        :key="d.id"
                                        class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 px-3 py-2 text-sm dark:border-slate-800"
                                    >
                                        <div class="min-w-0">
                                            <a
                                                :href="d.url"
                                                target="_blank"
                                                rel="noopener"
                                                class="truncate font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                                            >
                                                {{ d.original_name }}
                                            </a>
                                            <p class="text-xs text-slate-500">
                                                {{ formatTs(d.created_at) }}
                                                <span v-if="d.uploaded_by"> · {{ d.uploaded_by.name }}</span>
                                            </p>
                                        </div>
                                        <DangerButton type="button" class="shrink-0 rounded-lg px-2 py-1 text-xs" @click="removeDoc(d)">
                                            Sil
                                        </DangerButton>
                                    </li>
                                    <li v-if="!(customerDetail.documents || []).length" class="text-sm text-slate-400">
                                        Henüz evrak yok.
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
