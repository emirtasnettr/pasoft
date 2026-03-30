<script setup>
import LeadDrawer from '@/Components/Leads/LeadDrawer.vue';
import LeadKpiBar from '@/Components/Leads/LeadKpiBar.vue';
import LeadTable from '@/Components/Leads/LeadTable.vue';
import PaPagination from '@/Components/PaPagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { STAGE_LABELS, STAGE_ORDER } from '@/lib/leadLabels';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Squares2X2Icon } from '@heroicons/vue/24/outline';
import { reactive, ref } from 'vue';

const props = defineProps({
    leads: Object,
    filters: Object,
    leadDetail: Object,
    kpi: Object,
    users: Array,
    customers: Array,
});

const filterForm = reactive({
    q: props.filters?.q ?? '',
    stage: props.filters?.stage ?? '',
    source: props.filters?.source ?? '',
    assigned_user_id: props.filters?.assigned_user_id ?? '',
    created_from: props.filters?.created_from ?? '',
    created_to: props.filters?.created_to ?? '',
    stale: props.filters?.stale ?? false,
});

const noteTarget = ref(null);
const quickNoteForm = useForm({ body: '' });

function applyFilters() {
    const payload = { ...filterForm };
    if (!payload.stale) {
        delete payload.stale;
    }
    Object.keys(payload).forEach((k) => {
        if (payload[k] === '' || payload[k] === false) {
            delete payload[k];
        }
    });
    router.get(route('leads.index'), payload, {
        preserveState: true,
        replace: true,
    });
}

function resetFilters() {
    filterForm.q = '';
    filterForm.stage = '';
    filterForm.source = '';
    filterForm.assigned_user_id = '';
    filterForm.created_from = '';
    filterForm.created_to = '';
    filterForm.stale = false;
    router.get(route('leads.index'), {}, { preserveState: true, replace: true });
}

function openLead(id) {
    router.get(
        route('leads.index'),
        { ...props.filters, lead: id },
        {
            preserveScroll: true,
            preserveState: true,
            only: ['leadDetail', 'filters', 'flash'],
        },
    );
}

function onQuickNote(lead) {
    noteTarget.value = lead;
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
    quickNoteForm.post(route('leads.notes.store', noteTarget.value.id), {
        preserveScroll: true,
        only: ['leads', 'leadDetail', 'kpi', 'flash'],
        onSuccess: () => {
            closeQuickNote();
        },
    });
}
</script>

<template>
    <Head title="Leadler" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Lead yönetimi</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Öncelikleri görün, hızlı aksiyon alın, detayları çekmecede açın.
                    </p>
                </div>
                <Link
                    :href="route('leads.kanban')"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    <Squares2X2Icon class="h-4 w-4" />
                    Pipeline (Kanban)
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-7xl space-y-8">
            <LeadKpiBar v-if="kpi" :kpi="kpi" />

            <form
                class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                @submit.prevent="applyFilters"
            >
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="grid flex-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">Arama</label>
                            <input
                                v-model="filterForm.q"
                                type="search"
                                placeholder="İsim, e-posta, telefon…"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Aşama</label>
                            <select
                                v-model="filterForm.stage"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            >
                                <option value="">Tümü</option>
                                <option v-for="s in STAGE_ORDER" :key="s" :value="s">
                                    {{ STAGE_LABELS[s] }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Kaynak</label>
                            <input
                                v-model="filterForm.source"
                                type="text"
                                placeholder="Google Ads…"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            />
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
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Oluşturulma (başlangıç)</label>
                            <input
                                v-model="filterForm.created_from"
                                type="date"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Oluşturulma (bitiş)</label>
                            <input
                                v-model="filterForm.created_to"
                                type="date"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            />
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <label
                            class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-900 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-100"
                        >
                            <input v-model="filterForm.stale" type="checkbox" class="rounded border-slate-300" />
                            3+ gün işlem yok
                        </label>
                        <PrimaryButton type="submit" class="rounded-xl">Filtrele</PrimaryButton>
                        <SecondaryButton type="button" class="rounded-xl" @click="resetFilters">
                            Sıfırla
                        </SecondaryButton>
                    </div>
                </div>
            </form>

            <LeadTable :rows="leads.data" @open="openLead" @quick-note="onQuickNote" />

            <div class="flex justify-center border-t border-slate-100 pt-4 dark:border-slate-800">
                <PaPagination :links="leads.links" />
            </div>
        </div>

        <LeadDrawer
            :lead-detail="leadDetail"
            :filters="filters"
            :users="users"
            :customers="customers"
            context="index"
        />

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
                        Hızlı not — {{ noteTarget.title }}
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
