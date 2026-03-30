<script setup>
import PolicyDrawer from '@/Components/Policies/PolicyDrawer.vue';
import PolicyFilters from '@/Components/Policies/PolicyFilters.vue';
import PolicyKpiBar from '@/Components/Policies/PolicyKpiBar.vue';
import PolicyTable from '@/Components/Policies/PolicyTable.vue';
import PolicyTableSkeleton from '@/Components/Policies/PolicyTableSkeleton.vue';
import InputError from '@/Components/InputError.vue';
import PaPagination from '@/Components/PaPagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { URGENCY_LABELS } from '@/lib/policyLabels';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ExclamationTriangleIcon, PlusIcon } from '@heroicons/vue/24/outline';
import { reactive, ref, watch } from 'vue';

const props = defineProps({
    policies: Object,
    filters: Object,
    policyDetail: Object,
    kpi: Object,
    criticalAlerts: Array,
    policyTypes: Array,
    insuranceCompanies: Array,
    users: Array,
});

const filterForm = reactive({
    search: props.filters?.search ?? '',
    policy_type_id: props.filters?.policy_type_id ?? '',
    insurance_company_id: props.filters?.insurance_company_id ?? '',
    renewal_status: props.filters?.renewal_status ?? '',
    expires_within_days: props.filters?.expires_within_days ?? '',
    owner_user_id: props.filters?.owner_user_id ?? '',
});

watch(
    () => props.filters,
    (f) => {
        if (!f) {
            return;
        }
        filterForm.search = f.search ?? '';
        filterForm.policy_type_id = f.policy_type_id ?? '';
        filterForm.insurance_company_id = f.insurance_company_id ?? '';
        filterForm.renewal_status = f.renewal_status ?? '';
        filterForm.expires_within_days = f.expires_within_days ?? '';
        filterForm.owner_user_id = f.owner_user_id ?? '';
    },
    { deep: true },
);

const filterLoading = ref(false);
const noteTarget = ref(null);
const quickNoteForm = useForm({ body: '' });

function buildFilterPayload() {
    const payload = { ...filterForm };
    Object.keys(payload).forEach((k) => {
        if (payload[k] === '' || payload[k] === null) {
            delete payload[k];
        }
    });
    return payload;
}

function applyFilters() {
    filterLoading.value = true;
    router.get(route('policies.index'), buildFilterPayload(), {
        preserveState: true,
        replace: true,
        onFinish: () => {
            filterLoading.value = false;
        },
    });
}

function resetFilters() {
    filterForm.search = '';
    filterForm.policy_type_id = '';
    filterForm.insurance_company_id = '';
    filterForm.renewal_status = '';
    filterForm.expires_within_days = '';
    filterForm.owner_user_id = '';
    filterLoading.value = true;
    router.get(route('policies.index'), {}, {
        preserveState: true,
        replace: true,
        onFinish: () => {
            filterLoading.value = false;
        },
    });
}

function openPolicy(id) {
    router.get(
        route('policies.index'),
        { ...props.filters, policy: id },
        {
            preserveScroll: true,
            preserveState: true,
            only: ['policyDetail', 'filters', 'flash'],
        },
    );
}

function openAlert(a) {
    openPolicy(a.id);
}

function onQuickNote(p) {
    noteTarget.value = p;
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
    quickNoteForm.post(route('policies.notes.store', noteTarget.value.id), {
        preserveScroll: true,
        only: ['policies', 'policyDetail', 'kpi', 'criticalAlerts', 'flash'],
        onSuccess: () => closeQuickNote(),
    });
}
</script>

<template>
    <Head title="Poliçeler" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Poliçe yönetimi</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Yenileme, prim ve komisyon tek ekranda; detay çekmecede.
                    </p>
                </div>
                <Link :href="route('policies.create')">
                    <PrimaryButton class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5">
                        <PlusIcon class="h-4 w-4" />
                        Yeni poliçe
                    </PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-7xl space-y-8">
            <PolicyKpiBar v-if="kpi" :kpi="kpi" />

            <div
                v-if="criticalAlerts?.length"
                class="rounded-2xl border border-red-200 bg-red-50/90 p-4 shadow-sm dark:border-red-900/50 dark:bg-red-950/30"
            >
                <div class="flex items-start gap-3">
                    <ExclamationTriangleIcon class="h-6 w-6 shrink-0 text-red-600 dark:text-red-400" />
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-red-900 dark:text-red-100">
                            Acil dikkat: bugün / yarın biten veya süresi dolmuş poliçeler
                        </p>
                        <ul class="mt-2 space-y-1 text-sm text-red-800 dark:text-red-200">
                            <li v-for="a in criticalAlerts" :key="a.id">
                                <button
                                    type="button"
                                    class="text-left font-medium underline decoration-red-300 decoration-2 underline-offset-2 hover:text-red-950 dark:hover:text-red-50"
                                    @click="openAlert(a)"
                                >
                                    {{ a.policy_number }} — {{ a.customer_name }} · Bitiş
                                    {{ a.ends_at }} · {{ URGENCY_LABELS[a.urgency] }}
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <PolicyFilters
                :model="filterForm"
                :policy-types="policyTypes"
                :insurance-companies="insuranceCompanies"
                :users="users"
                @apply="applyFilters"
                @reset="resetFilters"
            />

            <PolicyTableSkeleton v-if="filterLoading" />
            <PolicyTable
                v-else
                :rows="policies.data"
                @open="openPolicy"
                @quick-note="onQuickNote"
            />

            <div class="flex justify-center border-t border-slate-100 pt-4 dark:border-slate-800">
                <PaPagination :links="policies.links" />
            </div>
        </div>

        <PolicyDrawer :policy-detail="policyDetail" :filters="filters" />

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
                        Hızlı not — {{ noteTarget.policy_number }}
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
