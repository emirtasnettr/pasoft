<script setup>
import CustomerDrawer from '@/Components/Customers/CustomerDrawer.vue';
import CustomerKpiBar from '@/Components/Customers/CustomerKpiBar.vue';
import CustomerTable from '@/Components/Customers/CustomerTable.vue';
import InputError from '@/Components/InputError.vue';
import PaPagination from '@/Components/PaPagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { SEGMENT_LABELS } from '@/lib/customerLabels';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { PlusIcon } from '@heroicons/vue/24/outline';
import { reactive, ref } from 'vue';

const SEGMENT_KEYS = ['vip', 'active', 'passive', 'potential'];

const props = defineProps({
    customers: Object,
    filters: Object,
    customerDetail: Object,
    kpi: Object,
    users: Array,
});

const filterForm = reactive({
    search: props.filters?.search ?? '',
    segment: props.filters?.segment ?? '',
    assigned_user_id: props.filters?.assigned_user_id ?? '',
    policy_payment_status: props.filters?.policy_payment_status ?? '',
    renewal_within_days: props.filters?.renewal_within_days ?? '',
});

const noteTarget = ref(null);
const quickNoteForm = useForm({ body: '' });

function applyFilters() {
    const payload = { ...filterForm };
    Object.keys(payload).forEach((k) => {
        if (payload[k] === '' || payload[k] === null) {
            delete payload[k];
        }
    });
    router.get(route('customers.index'), payload, {
        preserveState: true,
        replace: true,
    });
}

function resetFilters() {
    filterForm.search = '';
    filterForm.segment = '';
    filterForm.assigned_user_id = '';
    filterForm.policy_payment_status = '';
    filterForm.renewal_within_days = '';
    router.get(route('customers.index'), {}, { preserveState: true, replace: true });
}

function openCustomer(id) {
    router.get(
        route('customers.index'),
        { ...props.filters, customer: id },
        {
            preserveScroll: true,
            preserveState: true,
            only: ['customerDetail', 'filters', 'flash'],
        },
    );
}

function onQuickNote(c) {
    noteTarget.value = c;
    quickNoteForm.reset();
    quickNoteForm.clearErrors();
}

function closeQuickNote() {
    noteTarget.value = null;
    quickNoteForm.reset();
}

function submitQuickNote() {
    if (!noteTarget.value) {
        return;
    }
    quickNoteForm.post(route('customers.notes.store', noteTarget.value.id), {
        preserveScroll: true,
        only: ['customers', 'customerDetail', 'kpi', 'flash'],
        onSuccess: () => closeQuickNote(),
    });
}
</script>

<template>
    <Head title="Müşteriler" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-lg font-semibold tracking-tight text-slate-900 dark:text-white">
                        Müşteri 360
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Ciro, poliçeler ve aksiyonlar tek listede; detay sağ çekmecede.
                    </p>
                </div>
                <Link :href="route('customers.create')">
                    <PrimaryButton class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5">
                        <PlusIcon class="h-4 w-4" />
                        Yeni müşteri
                    </PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-7xl space-y-8">
            <CustomerKpiBar v-if="kpi" :kpi="kpi" />

            <form
                class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                @submit.prevent="applyFilters"
            >
                <div class="grid gap-4 lg:grid-cols-6">
                    <div class="lg:col-span-2">
                        <label class="text-xs font-semibold text-slate-500">Arama</label>
                        <input
                            v-model="filterForm.search"
                            type="search"
                            placeholder="İsim, firma, e-posta, telefon…"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">Segment</label>
                        <select
                            v-model="filterForm.segment"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        >
                            <option value="">Tümü</option>
                            <option v-for="s in SEGMENT_KEYS" :key="s" :value="s">
                                {{ SEGMENT_LABELS[s] }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">Poliçe ödeme</label>
                        <select
                            v-model="filterForm.policy_payment_status"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        >
                            <option value="">Tümü</option>
                            <option value="pending">Bekliyor</option>
                            <option value="partial">Kısmi</option>
                            <option value="collected">Tahsil</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">Yenileme (bitiş)</label>
                        <select
                            v-model="filterForm.renewal_within_days"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        >
                            <option value="">—</option>
                            <option value="30">30 gün içinde</option>
                            <option value="60">60 gün içinde</option>
                            <option value="90">90 gün içinde</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">Temsilci</label>
                        <select
                            v-model="filterForm.assigned_user_id"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        >
                            <option value="">Tümü</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">
                                {{ u.name }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <PrimaryButton type="submit" class="rounded-xl">Filtrele</PrimaryButton>
                    <SecondaryButton type="button" class="rounded-xl" @click="resetFilters">
                        Sıfırla
                    </SecondaryButton>
                </div>
            </form>

            <CustomerTable :rows="customers.data" @open="openCustomer" @quick-note="onQuickNote" />

            <div class="flex justify-center border-t border-slate-100 pt-4 dark:border-slate-800">
                <PaPagination :links="customers.links" />
            </div>
        </div>

        <CustomerDrawer :customer-detail="customerDetail" :filters="filters" />

        <Teleport to="body">
            <div
                v-if="noteTarget"
                class="fixed inset-0 z-[80] flex items-end justify-center bg-slate-900/50 p-4 backdrop-blur-sm sm:items-center"
                @click.self="closeQuickNote"
            >
                <div
                    class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-950"
                    @click.stop
                >
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                        Hızlı not — {{ noteTarget.company_name || noteTarget.name }}
                    </h3>
                    <form class="mt-4 space-y-3" @submit.prevent="submitQuickNote">
                        <textarea
                            v-model="quickNoteForm.body"
                            rows="4"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            placeholder="Notunuzu yazın…"
                        />
                        <InputError :message="quickNoteForm.errors.body" />
                        <div class="flex justify-end gap-2">
                            <SecondaryButton type="button" class="rounded-xl" @click="closeQuickNote">
                                Vazgeç
                            </SecondaryButton>
                            <PrimaryButton type="submit" class="rounded-xl" :disabled="quickNoteForm.processing">
                                Kaydet
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
