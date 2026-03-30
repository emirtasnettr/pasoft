<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import {
    formatMoneyTr,
    formatDateTimeShort,
    overdueAlertClass,
    whatsappHref,
} from '@/lib/financeLabels';
import { Link, router, useForm } from '@inertiajs/vue3';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    customerDetail: { type: Object, default: null },
    filters: { type: Object, default: () => ({}) },
});

const noteForm = useForm({ body: '' });
const planPolicyId = ref('');
const planForm = useForm({
    installments: [{ due_date: '', amount: '' }],
});

watch(
    () => props.customerDetail?.id,
    () => {
        noteForm.reset();
        noteForm.clearErrors();
        planPolicyId.value = '';
        planForm.installments = [{ due_date: '', amount: '' }];
        planForm.clearErrors();
    },
);

const open = computed(() => !!props.customerDetail);

function closeDrawer() {
    const next = { ...props.filters };
    delete next.customer;
    router.get(route('payments.index'), next, {
        replace: true,
        preserveScroll: true,
        preserveState: true,
        only: ['customerDetail'],
    });
}

function submitNote() {
    if (!props.customerDetail) {
        return;
    }
    noteForm.post(route('customers.notes.store', props.customerDetail.id), {
        preserveScroll: true,
        only: ['customerDetail'],
        onSuccess: () => noteForm.reset(),
    });
}

function addPlanRow() {
    planForm.installments.push({ due_date: '', amount: '' });
}

function removePlanRow(idx) {
    if (planForm.installments.length <= 1) {
        return;
    }
    planForm.installments.splice(idx, 1);
}

function submitPlan() {
    if (!planPolicyId.value) {
        return;
    }
    planForm
        .transform((data) => ({
            installments: data.installments.filter((row) => row.due_date && row.amount !== '' && row.amount != null),
        }))
        .post(route('policies.installments.plan', planPolicyId.value), {
            preserveScroll: true,
            only: ['customerDetail', 'customerSummaries', 'kpi', 'cashflow', 'payments'],
            onSuccess: () => {
                planForm.installments = [{ due_date: '', amount: '' }];
                planPolicyId.value = '';
            },
        });
}

const wa = computed(() => (props.customerDetail?.phone ? whatsappHref(props.customerDetail.phone) : null));
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
                        class="flex h-full w-full max-w-xl flex-col border-l border-slate-200/90 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-950"
                        @click.stop
                    >
                        <header
                            class="flex shrink-0 items-start justify-between gap-3 border-b border-slate-100 px-6 py-5 dark:border-slate-800"
                        >
                            <div class="min-w-0 flex-1">
                                <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                                    {{ customerDetail.display_name }}
                                </h2>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    Finans özeti ve taksitler
                                </p>
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
                            <section
                                class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-900/40"
                            >
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Finans özeti
                                </p>
                                <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-3">
                                    <div>
                                        <dt class="text-slate-500">Borç (plan / prim)</dt>
                                        <dd class="font-semibold tabular-nums text-slate-900 dark:text-white">
                                            {{ formatMoneyTr(customerDetail.summary.total_debt) }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-500">Ödenen</dt>
                                        <dd class="font-semibold tabular-nums text-emerald-700 dark:text-emerald-300">
                                            {{ formatMoneyTr(customerDetail.summary.paid) }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-500">Kalan</dt>
                                        <dd class="font-semibold tabular-nums text-amber-800 dark:text-amber-200">
                                            {{ formatMoneyTr(customerDetail.summary.remaining) }}
                                        </dd>
                                    </div>
                                </dl>
                                <div
                                    v-if="customerDetail.summary.overdue_amount > 0"
                                    class="mt-3 rounded-xl border border-rose-300 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-900 dark:border-rose-500/40 dark:bg-rose-950/40 dark:text-rose-100"
                                >
                                    Geciken tutar: {{ formatMoneyTr(customerDetail.summary.overdue_amount) }}
                                </div>
                            </section>

                            <section class="mt-8">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Hızlı aksiyon</h3>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <Link
                                        :href="route('payments.create', { customer_id: customerDetail.id })"
                                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                                    >
                                        Tahsilat gir
                                    </Link>
                                    <a
                                        v-if="customerDetail.phone"
                                        :href="'tel:' + customerDetail.phone"
                                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:hover:bg-slate-700"
                                    >
                                        Ara
                                    </a>
                                    <a
                                        v-if="wa"
                                        :href="wa"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center justify-center rounded-xl border border-green-200 bg-green-50 px-4 py-2.5 text-sm font-semibold text-green-900 shadow-sm transition hover:bg-green-100 dark:border-green-500/30 dark:bg-green-950/40 dark:text-green-100"
                                    >
                                        WhatsApp
                                    </a>
                                    <Link
                                        :href="route('customers.show', customerDetail.id)"
                                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800"
                                    >
                                        Müşteri kartı
                                    </Link>
                                </div>
                            </section>

                            <section class="mt-8">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Taksit planı (poliçe)</h3>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    Ödenmemiş taksitler silinir; yeni satırlar eklenir. Ödenmiş taksitlere dokunulmaz.
                                </p>
                                <div class="mt-3 space-y-3">
                                    <div>
                                        <InputLabel value="Poliçe" />
                                        <select
                                            v-model="planPolicyId"
                                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                        >
                                            <option value="" disabled>Seçin</option>
                                            <option v-for="p in customerDetail.policies" :key="p.id" :value="p.id">
                                                {{ p.policy_number }} · {{ formatMoneyTr(p.premium_amount) }}
                                            </option>
                                        </select>
                                    </div>
                                    <div
                                        v-for="(row, idx) in planForm.installments"
                                        :key="idx"
                                        class="flex flex-col gap-2 rounded-xl border border-slate-100 bg-slate-50/80 p-3 dark:border-slate-800 dark:bg-slate-900/50 sm:flex-row sm:items-end"
                                    >
                                        <div class="min-w-0 flex-1">
                                            <InputLabel value="Vade" />
                                            <input
                                                v-model="row.due_date"
                                                type="date"
                                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                            />
                                        </div>
                                        <div class="w-full min-w-[120px] sm:w-36">
                                            <InputLabel value="Tutar (₺)" />
                                            <input
                                                v-model="row.amount"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                            />
                                        </div>
                                        <button
                                            type="button"
                                            class="text-xs font-semibold text-rose-600 hover:underline"
                                            @click="removePlanRow(idx)"
                                        >
                                            Sil
                                        </button>
                                    </div>
                                    <InputError :message="planForm.errors.installments" />
                                    <div class="flex flex-wrap gap-2">
                                        <SecondaryButton type="button" class="rounded-xl" @click="addPlanRow">
                                            Satır ekle
                                        </SecondaryButton>
                                        <PrimaryButton
                                            type="button"
                                            class="rounded-xl"
                                            :disabled="planForm.processing || !planPolicyId"
                                            @click="submitPlan"
                                        >
                                            Planı kaydet
                                        </PrimaryButton>
                                    </div>
                                </div>
                            </section>

                            <section class="mt-8">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Taksitler</h3>
                                <ul class="mt-3 space-y-2">
                                    <li
                                        v-for="ins in customerDetail.installments"
                                        :key="ins.id"
                                        class="flex flex-col gap-1 rounded-xl border border-slate-100 bg-white px-3 py-2.5 text-sm dark:border-slate-800 dark:bg-slate-900/50"
                                    >
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <span class="font-medium text-slate-900 dark:text-white">
                                                {{ ins.policy_number }} · #{{ ins.sequence }} · {{ ins.due_date }}
                                            </span>
                                            <span
                                                class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 ring-inset"
                                                :class="ins.bucket_badge_class"
                                            >
                                                {{ ins.bucket_label }}
                                            </span>
                                        </div>
                                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600 dark:text-slate-400">
                                            <span class="font-semibold tabular-nums text-slate-800 dark:text-slate-200">
                                                {{ formatMoneyTr(ins.amount) }}
                                            </span>
                                            <span
                                                v-for="(al, ai) in ins.alerts"
                                                :key="ai"
                                                class="rounded-md px-1.5 py-0.5 font-bold"
                                                :class="overdueAlertClass(al.severity)"
                                            >
                                                {{ al.text }}
                                            </span>
                                        </div>
                                    </li>
                                    <li
                                        v-if="!customerDetail.installments?.length"
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Taksit kaydı yok. Yukarıdan plan oluşturabilirsiniz.
                                    </li>
                                </ul>
                            </section>

                            <section class="mt-8">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Notlar</h3>
                                <form class="mt-3 space-y-2" @submit.prevent="submitNote">
                                    <textarea
                                        v-model="noteForm.body"
                                        rows="3"
                                        placeholder="Finans / tahsilat notu…"
                                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                    <InputError :message="noteForm.errors.body" />
                                    <PrimaryButton type="submit" class="rounded-xl" :disabled="noteForm.processing">
                                        Not ekle
                                    </PrimaryButton>
                                </form>
                                <ul class="mt-6 space-y-4">
                                    <li
                                        v-for="n in customerDetail.notes"
                                        :key="n.id"
                                        class="relative border-l-2 border-indigo-200 pl-4 dark:border-indigo-800"
                                    >
                                        <p class="text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                                            {{ n.body }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ n.user?.name }} · {{ formatDateTimeShort(n.created_at) }}
                                        </p>
                                    </li>
                                    <li
                                        v-if="!customerDetail.notes?.length"
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Henüz not yok.
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
