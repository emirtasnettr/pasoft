<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TaskForm from '@/Components/Tasks/TaskForm.vue';
import TaskTimeline from '@/Components/Tasks/TaskTimeline.vue';
import {
    PRIORITY_LABELS,
    STATUS_LABELS,
    TYPE_LABELS,
    displayStatus,
    priorityBadgeClass,
    statusBadgeClass,
} from '@/lib/taskLabels';
import { Link, router, useForm } from '@inertiajs/vue3';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    taskDetail: { type: Object, default: null },
    filters: { type: Object, default: () => ({}) },
    users: { type: Array, default: () => [] },
    customers: { type: Array, default: () => [] },
    leads: { type: Array, default: () => [] },
    policies: { type: Array, default: () => [] },
});

const editing = ref(false);

const editForm = useForm({
    title: '',
    description: '',
    type: 'call',
    priority: 'medium',
    status: 'pending',
    due_at: '',
    remind_at: '',
    assigned_user_id: '',
    customer_id: '',
    lead_id: '',
    policy_id: '',
});

const noteForm = useForm({ body: '' });

const fileForm = useForm({ file: null });

function toLocalInput(iso) {
    if (!iso) {
        return '';
    }
    return iso.slice(0, 16);
}

function syncEditForm(t) {
    if (!t) {
        return;
    }
    editForm.title = t.title;
    editForm.description = t.description ?? '';
    editForm.type = t.type;
    editForm.priority = t.priority ?? 'medium';
    editForm.status = t.status;
    editForm.due_at = toLocalInput(t.due_at);
    editForm.remind_at = toLocalInput(t.remind_at);
    editForm.assigned_user_id = t.assigned_user?.id ?? '';
    editForm.customer_id = t.customer?.id ?? '';
    editForm.lead_id = t.lead?.id ?? '';
    editForm.policy_id = t.policy?.id ?? '';
    editForm.clearErrors();
}

watch(
    () => props.taskDetail?.id,
    (id) => {
        editing.value = false;
        if (id) {
            syncEditForm(props.taskDetail);
        }
        noteForm.reset();
        noteForm.clearErrors();
        fileForm.reset();
        fileForm.clearErrors();
    },
);

const open = computed(() => !!props.taskDetail);

const dKey = computed(() => {
    if (!props.taskDetail) {
        return 'pending';
    }
    return displayStatus(props.taskDetail);
});

function closeDrawer() {
    const next = { ...props.filters };
    delete next.task;
    router.get(route('tasks.index'), next, {
        replace: true,
        preserveScroll: true,
        preserveState: true,
        only: ['taskDetail'],
    });
}

function saveEdit() {
    if (!props.taskDetail) {
        return;
    }
    editForm
        .transform((data) => ({
            ...data,
            assigned_user_id: data.assigned_user_id || null,
            customer_id: data.customer_id || null,
            lead_id: data.lead_id || null,
            policy_id: data.policy_id || null,
        }))
        .patch(route('tasks.update', props.taskDetail.id), {
            preserveScroll: true,
            only: ['tasks', 'taskDetail', 'dueReminders'],
            onSuccess: () => {
                editing.value = false;
            },
        });
}

function patchStatus(status) {
    if (!props.taskDetail) {
        return;
    }
    router.patch(
        route('tasks.update', props.taskDetail.id),
        { status },
        {
            preserveScroll: true,
            only: ['tasks', 'taskDetail', 'dueReminders'],
        },
    );
}

function markDone() {
    patchStatus('done');
}

function destroyTask() {
    if (!props.taskDetail || !confirm('Bu görevi silmek istediğinize emin misiniz?')) {
        return;
    }
    const next = { ...props.filters };
    delete next.task;
    router.delete(route('tasks.destroy', props.taskDetail.id), {
        preserveScroll: true,
        onSuccess: () => {
            router.get(route('tasks.index'), next, {
                replace: true,
                preserveState: true,
                only: ['tasks', 'taskDetail', 'dueReminders', 'flash'],
            });
        },
    });
}

function submitNote() {
    if (!props.taskDetail) {
        return;
    }
    noteForm.post(route('tasks.notes.store', props.taskDetail.id), {
        preserveScroll: true,
        only: ['taskDetail'],
        onSuccess: () => noteForm.reset(),
    });
}

function onFilePick(e) {
    const f = e.target.files?.[0];
    fileForm.file = f ?? null;
}

function submitFile() {
    if (!props.taskDetail || !fileForm.file) {
        return;
    }
    fileForm.post(route('tasks.attachments.store', props.taskDetail.id), {
        preserveScroll: true,
        forceFormData: true,
        only: ['taskDetail'],
        onSuccess: () => {
            fileForm.reset();
            if (typeof document !== 'undefined') {
                const el = document.getElementById('task-attach-input');
                if (el) {
                    el.value = '';
                }
            }
        },
    });
}

function removeAttachment(att) {
    if (!confirm('Dosyayı kaldırmak istiyor musunuz?')) {
        return;
    }
    router.delete(route('tasks.attachments.destroy', att.id), {
        preserveScroll: true,
        only: ['taskDetail'],
    });
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

function formatNoteTime(iso) {
    if (!iso) {
        return '';
    }
    return iso.replace('T', ' ').slice(0, 16);
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
                        v-if="open && taskDetail"
                        class="flex h-full w-full max-w-lg flex-col border-l border-slate-200/90 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-950"
                        @click.stop
                    >
                        <header
                            class="flex shrink-0 items-start justify-between gap-3 border-b border-slate-100 px-6 py-5 dark:border-slate-800"
                        >
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset"
                                        :class="statusBadgeClass(dKey)"
                                    >
                                        {{ STATUS_LABELS[dKey] ?? dKey }}
                                    </span>
                                    <span
                                        class="inline-flex rounded-lg px-2 py-0.5 text-xs font-medium"
                                        :class="priorityBadgeClass(taskDetail.priority || 'medium')"
                                    >
                                        {{ PRIORITY_LABELS[taskDetail.priority] ?? PRIORITY_LABELS.medium }}
                                    </span>
                                </div>
                                <h2
                                    class="mt-2 text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                                >
                                    {{ taskDetail.title }}
                                </h2>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    {{ TYPE_LABELS[taskDetail.type] ?? taskDetail.type }} · #{{ taskDetail.id }}
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
                            <div v-if="!editing" class="space-y-6">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Açıklama
                                    </p>
                                    <p
                                        class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-slate-700 dark:text-slate-300"
                                    >
                                        {{ taskDetail.description || '—' }}
                                    </p>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                            Durum
                                        </p>
                                        <select
                                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                            :value="taskDetail.status"
                                            @change="patchStatus($event.target.value)"
                                        >
                                            <option value="pending">Bekliyor</option>
                                            <option value="in_progress">Devam ediyor</option>
                                            <option value="done">Tamamlandı</option>
                                            <option value="cancelled">İptal</option>
                                        </select>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                            Vade
                                        </p>
                                        <p class="mt-2 text-sm font-medium text-slate-800 dark:text-slate-200">
                                            {{
                                                taskDetail.due_at
                                                    ? taskDetail.due_at.replace('T', ' ').slice(0, 16)
                                                    : '—'
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Atanan
                                    </p>
                                    <div v-if="taskDetail.assigned_user" class="mt-2 flex items-center gap-3">
                                        <span
                                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white shadow-md"
                                        >
                                            {{ initials(taskDetail.assigned_user.name) }}
                                        </span>
                                        <div>
                                            <p class="font-semibold text-slate-900 dark:text-white">
                                                {{ taskDetail.assigned_user.name }}
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                {{ taskDetail.assigned_user.email }}
                                            </p>
                                        </div>
                                    </div>
                                    <p v-else class="mt-2 text-sm text-slate-500">Atanmamış</p>
                                </div>

                                <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        İlişkiler
                                    </p>
                                    <ul class="mt-3 space-y-2 text-sm">
                                        <li class="flex justify-between gap-2">
                                            <span class="text-slate-500">Müşteri</span>
                                            <Link
                                                v-if="taskDetail.customer"
                                                :href="route('customers.show', taskDetail.customer.id)"
                                                class="font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                                            >
                                                {{ taskDetail.customer.name }}
                                            </Link>
                                            <span v-else class="text-slate-400">—</span>
                                        </li>
                                        <li class="flex justify-between gap-2">
                                            <span class="text-slate-500">Lead</span>
                                            <Link
                                                v-if="taskDetail.lead"
                                                :href="route('leads.kanban')"
                                                class="font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                                            >
                                                {{ taskDetail.lead.title }}
                                            </Link>
                                            <span v-else class="text-slate-400">—</span>
                                        </li>
                                        <li class="flex justify-between gap-2">
                                            <span class="text-slate-500">Poliçe</span>
                                            <Link
                                                v-if="taskDetail.policy"
                                                :href="route('policies.show', taskDetail.policy.id)"
                                                class="font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                                            >
                                                {{ taskDetail.policy.policy_number }}
                                            </Link>
                                            <span v-else class="text-slate-400">—</span>
                                        </li>
                                    </ul>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <PrimaryButton
                                        v-if="taskDetail.status !== 'done'"
                                        type="button"
                                        class="rounded-xl"
                                        @click="markDone"
                                    >
                                        Tamamlandı işaretle
                                    </PrimaryButton>
                                    <SecondaryButton
                                        type="button"
                                        class="rounded-xl border-slate-200 !normal-case !tracking-normal dark:border-slate-600"
                                        @click="editing = true"
                                    >
                                        Düzenle
                                    </SecondaryButton>
                                    <DangerButton type="button" class="rounded-xl" @click="destroyTask">
                                        Sil
                                    </DangerButton>
                                </div>
                            </div>

                            <div v-else class="space-y-4">
                                <TaskForm
                                    :form="editForm"
                                    :users="users"
                                    :customers="customers"
                                    :leads="leads"
                                    :policies="policies"
                                />
                                <div class="flex gap-2">
                                    <PrimaryButton
                                        type="button"
                                        class="rounded-xl"
                                        :disabled="editForm.processing"
                                        @click="saveEdit"
                                    >
                                        Kaydet
                                    </PrimaryButton>
                                    <SecondaryButton
                                        type="button"
                                        class="rounded-xl !normal-case !tracking-normal"
                                        @click="
                                            editing = false;
                                            syncEditForm(taskDetail);
                                        "
                                    >
                                        İptal
                                    </SecondaryButton>
                                </div>
                            </div>

                            <hr class="my-8 border-slate-100 dark:border-slate-800" />

                            <section>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                    Notlar
                                </h3>
                                <form class="mt-3 space-y-2" @submit.prevent="submitNote">
                                    <textarea
                                        v-model="noteForm.body"
                                        rows="3"
                                        placeholder="Not yazın…"
                                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                    <InputError :message="noteForm.errors.body" />
                                    <PrimaryButton
                                        type="submit"
                                        class="rounded-xl"
                                        :disabled="noteForm.processing"
                                    >
                                        Not ekle
                                    </PrimaryButton>
                                </form>
                                <ul class="mt-6 space-y-4">
                                    <li
                                        v-for="n in taskDetail.notes"
                                        :key="n.id"
                                        class="relative border-l-2 border-indigo-200 pl-4 dark:border-indigo-800"
                                    >
                                        <p class="text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                                            {{ n.body }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ n.user?.name }} · {{ formatNoteTime(n.created_at) }}
                                        </p>
                                    </li>
                                    <li
                                        v-if="!taskDetail.notes?.length"
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Henüz not yok.
                                    </li>
                                </ul>
                            </section>

                            <hr class="my-8 border-slate-100 dark:border-slate-800" />

                            <section>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                    Dosyalar
                                </h3>
                                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-end">
                                    <div class="flex-1">
                                        <InputLabel value="Dosya seç" />
                                        <input
                                            id="task-attach-input"
                                            type="file"
                                            class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-slate-400 dark:file:bg-slate-800 dark:file:text-indigo-300"
                                            @change="onFilePick"
                                        />
                                        <InputError :message="fileForm.errors.file" />
                                    </div>
                                    <PrimaryButton
                                        type="button"
                                        class="rounded-xl"
                                        :disabled="fileForm.processing || !fileForm.file"
                                        @click="submitFile"
                                    >
                                        Yükle
                                    </PrimaryButton>
                                </div>
                                <ul class="mt-4 space-y-2">
                                    <li
                                        v-for="a in taskDetail.attachments"
                                        :key="a.id"
                                        class="flex items-center justify-between gap-2 rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-2 text-sm dark:border-slate-800 dark:bg-slate-900/50"
                                    >
                                        <a
                                            :href="a.url"
                                            target="_blank"
                                            rel="noopener"
                                            class="min-w-0 flex-1 truncate font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                                        >
                                            {{ a.original_name }}
                                        </a>
                                        <button
                                            type="button"
                                            class="shrink-0 text-xs font-semibold text-rose-600 hover:underline dark:text-rose-400"
                                            @click="removeAttachment(a)"
                                        >
                                            Kaldır
                                        </button>
                                    </li>
                                    <li
                                        v-if="!taskDetail.attachments?.length"
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Dosya yok.
                                    </li>
                                </ul>
                            </section>

                            <hr class="my-8 border-slate-100 dark:border-slate-800" />

                            <section>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                    Aktivite geçmişi
                                </h3>
                                <div class="mt-4">
                                    <TaskTimeline :activities="taskDetail.activities" />
                                </div>
                            </section>
                        </div>
                    </aside>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
