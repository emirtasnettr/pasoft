<script setup>
import CashflowChart from '@/Components/Finance/CashflowChart.vue';
import CustomerFinanceDrawer from '@/Components/Finance/CustomerFinanceDrawer.vue';
import CustomerFinanceTable from '@/Components/Finance/CustomerFinanceTable.vue';
import FinanceKpiBar from '@/Components/Finance/FinanceKpiBar.vue';
import PaPagination from '@/Components/PaPagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { formatMoneyTr, formatDateTimeShort, paymentMethodLabel } from '@/lib/financeLabels';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    customerSummaries: Object,
    customerDetail: { type: Object, default: null },
    payments: Object,
    kpi: Object,
    cashflow: Object,
    filters: Object,
});

const filterForm = reactive({
    q: props.filters.q || '',
    status: props.filters.status || '',
    from: props.filters.from || '',
    to: props.filters.to || '',
});

watch(
    () => props.filters,
    (f) => {
        filterForm.q = f.q || '';
        filterForm.status = f.status || '';
        filterForm.from = f.from || '';
        filterForm.to = f.to || '';
    },
    { deep: true },
);

const reloadKeys = ['customerSummaries', 'payments', 'filters', 'kpi', 'cashflow', 'customerDetail'];

function baseParams(extra = {}) {
    return {
        q: filterForm.q || undefined,
        status: filterForm.status || undefined,
        from: filterForm.from || undefined,
        to: filterForm.to || undefined,
        ...extra,
    };
}

function submitFilters() {
    router.get(route('payments.index'), baseParams(), {
        preserveState: true,
        replace: true,
        only: reloadKeys,
    });
}

function resetFilters() {
    filterForm.q = '';
    filterForm.status = '';
    filterForm.from = '';
    filterForm.to = '';
    router.get(route('payments.index'), {}, {
        preserveState: true,
        replace: true,
        only: reloadKeys,
    });
}

function openCustomer(id) {
    router.get(route('payments.index'), baseParams({ customer: id }), {
        preserveState: true,
        replace: true,
        only: ['customerDetail'],
    });
}
</script>

<template>
    <Head title="Finans & tahsilat" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Finans & tahsilat</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Borç, taksit, gecikme ve nakit akışı tek panelde.
                    </p>
                </div>
                <Link :href="route('payments.create')">
                    <PrimaryButton class="rounded-xl">Tahsilat gir</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-7xl space-y-8">
            <FinanceKpiBar :kpi="kpi" />

            <CashflowChart :cashflow="cashflow" />

            <form
                class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:flex-row lg:flex-wrap lg:items-end"
                @submit.prevent="submitFilters"
            >
                <div class="min-w-[200px] flex-1">
                    <label class="text-xs font-semibold uppercase text-slate-500">Müşteri / arama</label>
                    <input
                        v-model="filterForm.q"
                        type="search"
                        placeholder="Ad, şirket…"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                </div>
                <div class="min-w-[160px]">
                    <label class="text-xs font-semibold uppercase text-slate-500">Taksit durumu</label>
                    <select
                        v-model="filterForm.status"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="">Tümü</option>
                        <option value="overdue">Geciken</option>
                        <option value="pending">Vadesi gelmemiş borç</option>
                    </select>
                </div>
                <div class="min-w-[140px]">
                    <label class="text-xs font-semibold uppercase text-slate-500">Tahsilat başlangıç</label>
                    <input
                        v-model="filterForm.from"
                        type="date"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                </div>
                <div class="min-w-[140px]">
                    <label class="text-xs font-semibold uppercase text-slate-500">Tahsilat bitiş</label>
                    <input
                        v-model="filterForm.to"
                        type="date"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                </div>
                <div class="flex flex-wrap gap-2">
                    <PrimaryButton type="submit" class="rounded-xl">Filtrele</PrimaryButton>
                    <SecondaryButton type="button" class="rounded-xl" @click="resetFilters">
                        Sıfırla
                    </SecondaryButton>
                </div>
            </form>

            <section>
                <h2 class="mb-3 text-sm font-bold text-slate-900 dark:text-white">Müşteri bazlı finans</h2>
                <CustomerFinanceTable :rows="customerSummaries.data" @open="openCustomer" />
                <div
                    v-if="customerSummaries.data?.length"
                    class="mt-4 flex justify-center border-t border-slate-100 pt-4 dark:border-slate-800"
                >
                    <PaPagination :links="customerSummaries.links" />
                </div>
            </section>

            <section>
                <h2 class="mb-3 text-sm font-bold text-slate-900 dark:text-white">Son tahsilatlar</h2>
                <p class="mb-3 text-xs text-slate-500 dark:text-slate-400">
                    Liste; yukarıdaki tarih ve müşteri araması ile filtrelenir.
                </p>
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800">
                        <thead class="bg-slate-50/80 dark:bg-slate-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                    Tarih
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                    Müşteri
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                    Poliçe
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                    Yöntem
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                                    Tutar
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-if="!payments.data?.length">
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">
                                    Bu filtrelere uygun tahsilat yok.
                                </td>
                            </tr>
                            <tr
                                v-for="p in payments.data"
                                :key="p.id"
                                class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                    {{ formatDateTimeShort(p.paid_at) }}
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white">
                                    {{ p.customer?.company_name || p.customer?.name }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                    {{ p.policy?.policy_number ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                    {{ paymentMethodLabel(p.method) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums text-slate-900 dark:text-white">
                                    {{ formatMoneyTr(p.amount) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div
                        v-if="payments.data?.length"
                        class="border-t border-slate-100 px-4 py-4 dark:border-slate-800"
                    >
                        <PaPagination :links="payments.links" />
                    </div>
                </div>
            </section>
        </div>

        <CustomerFinanceDrawer :customer-detail="customerDetail" :filters="filters" />
    </AuthenticatedLayout>
</template>
