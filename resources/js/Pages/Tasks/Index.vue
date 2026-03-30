<script setup>
import PaEmptyState from '@/Components/PaEmptyState.vue';
import PaPagination from '@/Components/PaPagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TaskDetailDrawer from '@/Components/Tasks/TaskDetailDrawer.vue';
import TaskForm from '@/Components/Tasks/TaskForm.vue';
import TaskTable from '@/Components/Tasks/TaskTable.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ClipboardDocumentListIcon, CalendarDaysIcon } from '@heroicons/vue/24/outline';
import { onMounted, reactive, ref, watch } from 'vue';

const page = usePage();

const props = defineProps({
    tasks: Object,
    filters: Object,
    taskDetail: { type: Object, default: null },
    dueReminders: { type: Array, default: () => [] },
    users: Array,
    customers: Array,
    leads: Array,
    policies: Array,
});

const createOpen = ref(false);
const selectedIds = ref([]);
const bulkStatus = ref('in_progress');

const filterForm = reactive({
    q: props.filters.q || '',
    status: props.filters.status || '',
    assigned_user_id: props.filters.assigned_user_id ?? '',
    due_from: props.filters.due_from || '',
    due_to: props.filters.due_to || '',
    mine: props.filters.mine || false,
});

watch(
    () => props.filters,
    (f) => {
        filterForm.q = f.q || '';
        filterForm.status = f.status || '';
        filterForm.assigned_user_id = f.assigned_user_id ?? '';
        filterForm.due_from = f.due_from || '';
        filterForm.due_to = f.due_to || '';
        filterForm.mine = !!f.mine;
    },
    { deep: true },
);

const createForm = useForm({
    title: '',
    description: '',
    type: 'call',
    status: 'pending',
    priority: 'medium',
    due_at: '',
    remind_at: '',
    assigned_user_id: '',
    customer_id: '',
    lead_id: '',
    policy_id: '',
});

const bulkForm = useForm({
    ids: [],
    action: 'status',
    status: 'pending',
});

let qDebounce = null;

function filterPayload(extra = {}) {
    return {
        q: filterForm.q || undefined,
        status: filterForm.status || undefined,
        assigned_user_id: filterForm.assigned_user_id || undefined,
        due_from: filterForm.due_from || undefined,
        due_to: filterForm.due_to || undefined,
        mine: filterForm.mine ? 1 : undefined,
        task: props.filters.task || undefined,
        ...extra,
    };
}

function applyFilters(extra = {}) {
    router.get(route('tasks.index'), filterPayload(extra), {
        preserveState: true,
        replace: true,
        only: ['tasks', 'filters', 'taskDetail', 'dueReminders'],
    });
}

function onSearchInput() {
    clearTimeout(qDebounce);
    qDebounce = setTimeout(() => applyFilters(), 400);
}

function submitFilters(e) {
    e?.preventDefault?.();
    applyFilters();
}

function openTask(id) {
    router.get(route('tasks.index'), filterPayload({ task: id }), {
        preserveState: true,
        replace: true,
        only: ['taskDetail'],
    });
}

function toggleSelect(id) {
    const i = selectedIds.value.indexOf(id);
    if (i === -1) {
        selectedIds.value = [...selectedIds.value, id];
    } else {
        selectedIds.value = selectedIds.value.filter((x) => x !== id);
    }
}

function toggleSelectAll(checked, ids) {
    if (checked) {
        selectedIds.value = [...ids];
    } else {
        selectedIds.value = [];
    }
}

function runBulkStatus() {
    if (!selectedIds.value.length) {
        return;
    }
    bulkForm.ids = [...selectedIds.value];
    bulkForm.action = 'status';
    bulkForm.status = bulkStatus.value;
    bulkForm.post(route('tasks.bulk'), {
        preserveScroll: true,
        only: ['tasks', 'taskDetail', 'dueReminders'],
        onSuccess: () => {
            selectedIds.value = [];
        },
    });
}

function runBulkDelete() {
    if (!selectedIds.value.length || !confirm('Seçili görevleri silmek istiyor musunuz?')) {
        return;
    }
    bulkForm.ids = [...selectedIds.value];
    bulkForm.action = 'delete';
    bulkForm.post(route('tasks.bulk'), {
        preserveScroll: true,
        only: ['tasks', 'taskDetail', 'dueReminders'],
        onSuccess: () => {
            selectedIds.value = [];
        },
    });
}

function submitCreate() {
    createForm.post(route('tasks.store'), {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset();
            createForm.status = 'pending';
            createForm.priority = 'medium';
            createOpen.value = false;
        },
    });
}

onMounted(() => {
    if (typeof sessionStorage === 'undefined') {
        return;
    }
    const key = 'pa_task_due_remind';
    if (sessionStorage.getItem(key)) {
        return;
    }
    const n = page.props.dueReminders?.length;
    if (!n) {
        return;
    }
    sessionStorage.setItem(key, '1');
    window.dispatchEvent(
        new CustomEvent('pa-toast', {
            detail: {
                message: `${n} görevin vadesi önümüzdeki 24 saat içinde.`,
                variant: 'warning',
            },
        }),
    );
});
</script>

<template>
    <Head title="Görevler" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                        Görevler
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Operasyon görevlerinizi tek yerden yönetin; detay için satıra tıklayın.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="route('tasks.calendar')"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:scale-[1.02] hover:border-slate-300 active:scale-[0.98] dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-slate-600"
                    >
                        <CalendarDaysIcon class="h-4 w-4" />
                        Takvim
                    </Link>
                    <PrimaryButton type="button" class="rounded-xl" @click="createOpen = true">
                        Yeni görev
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">
            <form
                class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                @submit.prevent="submitFilters"
            >
                <div class="grid gap-4 lg:grid-cols-6">
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Arama
                        </label>
                        <input
                            v-model="filterForm.q"
                            type="search"
                            placeholder="Görev veya müşteri…"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            @input="onSearchInput"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Durum
                        </label>
                        <select
                            v-model="filterForm.status"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            @change="applyFilters()"
                        >
                            <option value="">Tümü</option>
                            <option value="pending">Bekliyor</option>
                            <option value="in_progress">Devam ediyor</option>
                            <option value="done">Tamamlandı</option>
                            <option value="cancelled">İptal</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Atanan
                        </label>
                        <select
                            v-model="filterForm.assigned_user_id"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            @change="applyFilters()"
                        >
                            <option value="">Herkes</option>
                            <option v-for="u in users" :key="u.id" :value="String(u.id)">
                                {{ u.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Vade başlangıç
                        </label>
                        <input
                            v-model="filterForm.due_from"
                            type="date"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            @change="applyFilters()"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Vade bitiş
                        </label>
                        <input
                            v-model="filterForm.due_to"
                            type="date"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            @change="applyFilters()"
                        />
                    </div>
                    <div class="flex flex-col justify-end gap-2">
                        <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                            <input
                                v-model="filterForm.mine"
                                type="checkbox"
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600"
                                @change="applyFilters()"
                            />
                            Sadece bana atananlar
                        </label>
                        <SecondaryButton
                            type="submit"
                            class="rounded-xl !normal-case !tracking-normal"
                        >
                            Filtreleri uygula
                        </SecondaryButton>
                    </div>
                </div>
            </form>

            <div
                v-if="selectedIds.length"
                class="flex flex-col gap-3 rounded-2xl border border-indigo-200/80 bg-gradient-to-r from-indigo-50/90 to-violet-50/80 p-4 shadow-sm dark:border-indigo-500/30 dark:from-indigo-950/40 dark:to-violet-950/30 sm:flex-row sm:items-center sm:justify-between"
            >
                <p class="text-sm font-semibold text-indigo-900 dark:text-indigo-100">
                    {{ selectedIds.length }} görev seçildi
                </p>
                <div class="flex flex-wrap items-center gap-2">
                    <select
                        v-model="bulkStatus"
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                    >
                        <option value="pending">Bekliyor</option>
                        <option value="in_progress">Devam ediyor</option>
                        <option value="done">Tamamlandı</option>
                        <option value="cancelled">İptal</option>
                    </select>
                    <SecondaryButton
                        type="button"
                        class="rounded-xl !normal-case !tracking-normal"
                        :disabled="bulkForm.processing"
                        @click="runBulkStatus"
                    >
                        Durumu güncelle
                    </SecondaryButton>
                    <DangerButton
                        type="button"
                        class="rounded-xl"
                        :disabled="bulkForm.processing"
                        @click="runBulkDelete"
                    >
                        Toplu sil
                    </DangerButton>
                </div>
            </div>

            <div v-if="tasks.data.length" class="space-y-6">
                <TaskTable
                    :rows="tasks.data"
                    :selected-ids="selectedIds"
                    :only-mine="filterForm.mine"
                    @toggle="toggleSelect"
                    @toggle-all="toggleSelectAll"
                    @open="openTask"
                />
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white px-4 py-3 dark:border-slate-800 dark:bg-slate-900"
                >
                    <PaPagination :links="tasks.links" />
                </div>
            </div>

            <div v-else class="space-y-6">
                <PaEmptyState
                    title="Henüz görev yok"
                    description="İlk görevinizi oluşturarak takvim ve hatırlatmaları kullanmaya başlayın."
                >
                    <template #icon>
                        <ClipboardDocumentListIcon class="h-7 w-7 text-indigo-600 dark:text-indigo-400" />
                    </template>
                </PaEmptyState>
                <div class="flex justify-center">
                    <PrimaryButton type="button" class="rounded-xl" @click="createOpen = true">
                        İlk görevi oluştur
                    </PrimaryButton>
                </div>
            </div>
        </div>

        <TaskDetailDrawer
            :task-detail="taskDetail"
            :filters="filters"
            :users="users"
            :customers="customers"
            :leads="leads"
            :policies="policies"
        />

        <Teleport to="body">
            <div
                v-if="createOpen"
                class="fixed inset-0 z-[65] flex justify-end bg-slate-900/50 backdrop-blur-md"
                @click.self="createOpen = false"
            >
                <div
                    class="h-full w-full max-w-md overflow-y-auto border-l border-slate-200 bg-white p-6 shadow-2xl transition-transform duration-300 ease-out dark:border-slate-800 dark:bg-slate-950"
                >
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                            Yeni görev
                        </h2>
                        <button
                            type="button"
                            class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
                            @click="createOpen = false"
                        >
                            Kapat
                        </button>
                    </div>
                    <form class="mt-6 space-y-4" @submit.prevent="submitCreate">
                        <TaskForm
                            :form="createForm"
                            :users="users"
                            :customers="customers"
                            :leads="leads"
                            :policies="policies"
                        />
                        <PrimaryButton
                            type="submit"
                            class="w-full rounded-xl"
                            :disabled="createForm.processing"
                        >
                            Oluştur
                        </PrimaryButton>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
