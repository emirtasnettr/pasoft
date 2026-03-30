<script setup>
import {
    PRIORITY_LABELS,
    STATUS_LABELS,
    TYPE_LABELS,
    displayStatus,
    priorityBadgeClass,
    statusBadgeClass,
    typeBadgeClass,
} from '@/lib/taskLabels';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    rows: { type: Array, default: () => [] },
    selectedIds: { type: Array, default: () => [] },
    onlyMine: { type: Boolean, default: false },
});

const emit = defineEmits(['toggle', 'toggle-all', 'open']);

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

function isSelected(id) {
    return props.selectedIds.includes(id);
}

function patchStatus(task, status) {
    router.patch(
        route('tasks.update', task.id),
        { status },
        {
            preserveScroll: true,
            only: ['tasks', 'taskDetail', 'dueReminders'],
        },
    );
}

const allSelectableIds = () => props.rows.map((r) => r.id);

function allSelected() {
    const ids = allSelectableIds();
    return ids.length > 0 && ids.every((id) => props.selectedIds.includes(id));
}

function toggleSelectAll(e) {
    emit('toggle-all', e.target.checked, allSelectableIds());
}

function formatDue(d) {
    if (!d) {
        return '—';
    }
    return d.replace('T', ' ').slice(0, 16);
}
</script>

<template>
    <div
        class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800">
                <thead class="bg-slate-50/90 dark:bg-slate-800/40">
                    <tr>
                        <th class="w-12 px-4 py-4 text-left">
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                                :checked="allSelected()"
                                @change="toggleSelectAll"
                            />
                        </th>
                        <th
                            class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                        >
                            Görev
                        </th>
                        <th
                            class="hidden px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 lg:table-cell dark:text-slate-400"
                        >
                            Durum
                        </th>
                        <th
                            class="hidden px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 md:table-cell dark:text-slate-400"
                        >
                            Öncelik
                        </th>
                        <th
                            class="hidden px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 lg:table-cell dark:text-slate-400"
                        >
                            Tür
                        </th>
                        <th
                            class="hidden px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 xl:table-cell dark:text-slate-400"
                        >
                            Müşteri
                        </th>
                        <th
                            class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                        >
                            Vade
                        </th>
                        <th
                            class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                        >
                            Atanan
                        </th>
                        <th
                            class="hidden w-44 px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 lg:table-cell dark:text-slate-400"
                        >
                            Hızlı durum
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr
                        v-for="row in rows"
                        :key="row.id"
                        class="transition-colors duration-200 hover:bg-slate-50/90 dark:hover:bg-slate-800/50"
                    >
                        <td class="px-4 py-5 align-middle" @click.stop>
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                                :checked="isSelected(row.id)"
                                @change="emit('toggle', row.id)"
                            />
                        </td>
                        <td
                            class="cursor-pointer px-4 py-5 align-middle"
                            @click="emit('open', row.id)"
                        >
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                                :class="{ 'line-through opacity-50': row.status === 'done' }"
                            >
                                {{ row.title }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                #{{ row.id }}
                                <span v-if="onlyMine" class="ml-1 text-indigo-600 dark:text-indigo-400">
                                    · Bana atanan
                                </span>
                            </p>
                        </td>
                        <td
                            class="hidden cursor-pointer px-4 py-5 align-middle lg:table-cell"
                            @click="emit('open', row.id)"
                        >
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset"
                                :class="statusBadgeClass(displayStatus(row))"
                            >
                                {{ STATUS_LABELS[displayStatus(row)] ?? displayStatus(row) }}
                            </span>
                        </td>
                        <td
                            class="hidden cursor-pointer px-4 py-5 align-middle md:table-cell"
                            @click="emit('open', row.id)"
                        >
                            <span
                                class="inline-flex rounded-lg px-2 py-0.5 text-xs font-medium"
                                :class="priorityBadgeClass(row.priority || 'medium')"
                            >
                                {{ PRIORITY_LABELS[row.priority] ?? PRIORITY_LABELS.medium }}
                            </span>
                        </td>
                        <td
                            class="hidden cursor-pointer px-4 py-5 align-middle lg:table-cell"
                            @click="emit('open', row.id)"
                        >
                            <span
                                class="inline-flex rounded-lg px-2 py-0.5 text-xs font-medium"
                                :class="typeBadgeClass()"
                            >
                                {{ TYPE_LABELS[row.type] ?? row.type }}
                            </span>
                        </td>
                        <td class="hidden px-4 py-5 align-middle xl:table-cell" @click.stop>
                            <Link
                                v-if="row.customer"
                                :href="route('customers.show', row.customer.id)"
                                class="text-sm font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                                @click.stop
                            >
                                {{ row.customer.name }}
                            </Link>
                            <span v-else class="text-sm text-slate-400">—</span>
                        </td>
                        <td
                            class="cursor-pointer px-4 py-5 align-middle text-sm text-slate-600 dark:text-slate-300"
                            @click="emit('open', row.id)"
                        >
                            {{ formatDue(row.due_at) }}
                        </td>
                        <td
                            class="cursor-pointer px-4 py-5 align-middle"
                            @click="emit('open', row.id)"
                        >
                            <div v-if="row.assigned_user" class="flex items-center gap-2">
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-br from-slate-600 to-slate-800 text-xs font-bold text-white shadow-sm"
                                >
                                    {{ initials(row.assigned_user.name) }}
                                </span>
                                <span class="hidden text-sm font-medium text-slate-700 sm:inline dark:text-slate-200">
                                    {{ row.assigned_user.name }}
                                </span>
                            </div>
                            <span v-else class="text-sm text-slate-400">—</span>
                        </td>
                        <td class="hidden px-4 py-5 align-middle lg:table-cell" @click.stop>
                            <select
                                class="w-full rounded-xl border border-slate-200 bg-white px-2 py-1.5 text-xs font-medium text-slate-800 shadow-sm transition hover:border-slate-300 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                                :value="row.status"
                                @change="patchStatus(row, $event.target.value)"
                            >
                                <option value="pending">Bekliyor</option>
                                <option value="in_progress">Devam ediyor</option>
                                <option value="done">Tamamlandı</option>
                                <option value="cancelled">İptal</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
