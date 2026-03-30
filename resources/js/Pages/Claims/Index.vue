<script setup>
import ClaimDrawer from '@/Components/Claims/ClaimDrawer.vue';
import ClaimKpiBar from '@/Components/Claims/ClaimKpiBar.vue';
import ClaimTable from '@/Components/Claims/ClaimTable.vue';
import PaPagination from '@/Components/PaPagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    claims: Object,
    filters: Object,
    claimDetail: { type: Object, default: null },
    kpi: Object,
    insuranceCompanies: Array,
    statusOptions: Array,
});

const filterForm = reactive({
    status: props.filters.status || '',
    insurance_company_id: props.filters.insurance_company_id ?? '',
    from: props.filters.from || '',
    to: props.filters.to || '',
});

watch(
    () => props.filters,
    (f) => {
        filterForm.status = f.status || '';
        filterForm.insurance_company_id = f.insurance_company_id ?? '';
        filterForm.from = f.from || '';
        filterForm.to = f.to || '';
    },
    { deep: true },
);

function filterPayload(extra = {}) {
    return {
        status: filterForm.status || undefined,
        insurance_company_id: filterForm.insurance_company_id || undefined,
        from: filterForm.from || undefined,
        to: filterForm.to || undefined,
        claim: props.filters.claim || undefined,
        ...extra,
    };
}

function submitFilters(e) {
    e?.preventDefault?.();
    const next = { ...filterPayload() };
    delete next.claim;
    router.get(route('claims.index'), next, {
        preserveState: true,
        replace: true,
        only: ['claims', 'filters', 'claimDetail', 'kpi'],
    });
}

function resetFilters() {
    filterForm.status = '';
    filterForm.insurance_company_id = '';
    filterForm.from = '';
    filterForm.to = '';
    router.get(route('claims.index'), {}, {
        preserveState: true,
        replace: true,
        only: ['claims', 'filters', 'claimDetail', 'kpi'],
    });
}

function openClaim(id) {
    router.get(route('claims.index'), filterPayload({ claim: id }), {
        preserveState: true,
        replace: true,
        only: ['claimDetail'],
    });
}
</script>

<template>
    <Head title="Hasar yönetimi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Hasar yönetimi</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Süreç takibi, evrak ve müşteri iletişimi tek ekranda.
                    </p>
                </div>
                <Link :href="route('claims.create')">
                    <PrimaryButton class="rounded-xl">Yeni hasar</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">
            <ClaimKpiBar :kpi="kpi" />

            <form
                class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:flex-row lg:flex-wrap lg:items-end"
                @submit.prevent="submitFilters"
            >
                <div class="min-w-[160px] flex-1">
                    <label class="text-xs font-semibold uppercase text-slate-500">Durum</label>
                    <select
                        v-model="filterForm.status"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="">Tümü</option>
                        <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                </div>
                <div class="min-w-[180px] flex-1">
                    <label class="text-xs font-semibold uppercase text-slate-500">Sigorta şirketi</label>
                    <select
                        v-model="filterForm.insurance_company_id"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="">Tümü</option>
                        <option v-for="ic in insuranceCompanies" :key="ic.id" :value="ic.id">
                            {{ ic.name }}
                        </option>
                    </select>
                </div>
                <div class="min-w-[140px]">
                    <label class="text-xs font-semibold uppercase text-slate-500">Başlangıç</label>
                    <input
                        v-model="filterForm.from"
                        type="date"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                </div>
                <div class="min-w-[140px]">
                    <label class="text-xs font-semibold uppercase text-slate-500">Bitiş</label>
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

            <ClaimTable :rows="claims.data" :status-options="statusOptions" @open="openClaim" />

            <div
                v-if="claims.data?.length"
                class="flex justify-center border-t border-slate-100 pt-4 dark:border-slate-800"
            >
                <PaPagination :links="claims.links" />
            </div>
        </div>

        <ClaimDrawer
            :claim-detail="claimDetail"
            :filters="filters"
            :status-options="statusOptions"
        />
    </AuthenticatedLayout>
</template>
