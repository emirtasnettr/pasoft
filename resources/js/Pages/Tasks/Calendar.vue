<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';
import { computed, ref } from 'vue';
import {
    PRIORITY_LABELS,
    STATUS_LABELS,
    TYPE_LABELS,
    displayStatus,
    statusBadgeClass,
} from '@/lib/taskLabels';

const props = defineProps({
    tasks: { type: Array, default: () => [] },
    range: { type: Object, default: () => ({ from: '', to: '' }) },
    initialView: { type: String, default: 'month' },
});

const view = ref(props.initialView === 'week' || props.initialView === 'day' ? props.initialView : 'month');
const anchor = ref(new Date());

const weekdayLabels = ['Pt', 'Sa', 'Ça', 'Pe', 'Cu', 'Ct', 'Pz'];

function pad(n) {
    return String(n).padStart(2, '0');
}

function toYmd(d) {
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

function startOfWeekMonday(d) {
    const x = new Date(d.getFullYear(), d.getMonth(), d.getDate());
    const dow = (x.getDay() + 6) % 7;
    x.setDate(x.getDate() - dow);
    return x;
}

function addDays(d, n) {
    const x = new Date(d.getFullYear(), d.getMonth(), d.getDate());
    x.setDate(x.getDate() + n);
    return x;
}

function monthLabel(d) {
    return d.toLocaleDateString('tr-TR', { month: 'long', year: 'numeric' });
}

function dayKey(iso) {
    return iso?.slice(0, 10) ?? '';
}

const tasksByDay = computed(() => {
    const m = {};
    for (const t of props.tasks) {
        const k = dayKey(t.due_at);
        if (!k) {
            continue;
        }
        if (!m[k]) {
            m[k] = [];
        }
        m[k].push(t);
    }
    for (const k of Object.keys(m)) {
        m[k].sort((a, b) => (a.due_at > b.due_at ? 1 : -1));
    }
    return m;
});

const monthCells = computed(() => {
    const y = anchor.value.getFullYear();
    const mo = anchor.value.getMonth();
    const first = new Date(y, mo, 1);
    const start = startOfWeekMonday(first);
    const cells = [];
    for (let i = 0; i < 42; i++) {
        const d = addDays(start, i);
        cells.push({
            date: d,
            ymd: toYmd(d),
            inMonth: d.getMonth() === mo,
        });
    }
    return cells;
});

const weekDays = computed(() => {
    const s = startOfWeekMonday(anchor.value);
    return Array.from({ length: 7 }, (_, i) => {
        const d = addDays(s, i);
        return { date: d, ymd: toYmd(d) };
    });
});

const singleDay = computed(() => ({
    date: new Date(anchor.value.getFullYear(), anchor.value.getMonth(), anchor.value.getDate()),
    ymd: toYmd(anchor.value),
}));

function prev() {
    if (view.value === 'month') {
        anchor.value = new Date(anchor.value.getFullYear(), anchor.value.getMonth() - 1, 1);
    } else if (view.value === 'week') {
        anchor.value = addDays(anchor.value, -7);
    } else {
        anchor.value = addDays(anchor.value, -1);
    }
    maybeRefetch();
}

function next() {
    if (view.value === 'month') {
        anchor.value = new Date(anchor.value.getFullYear(), anchor.value.getMonth() + 1, 1);
    } else if (view.value === 'week') {
        anchor.value = addDays(anchor.value, 7);
    } else {
        anchor.value = addDays(anchor.value, 1);
    }
    maybeRefetch();
}

function maybeRefetch() {
    const marginStart = view.value === 'month' ? addDays(startOfWeekMonday(new Date(anchor.value.getFullYear(), anchor.value.getMonth(), 1)), -7) : addDays(anchor.value, -14);
    const marginEnd = view.value === 'month' ? addDays(new Date(anchor.value.getFullYear(), anchor.value.getMonth() + 1, 0), 14) : addDays(anchor.value, 14);
    const fromStr = toYmd(marginStart);
    const toStr = toYmd(marginEnd);
    const r = props.range;
    if (fromStr < r.from || toStr > r.to) {
        router.get(
            route('tasks.calendar'),
            { from: fromStr, to: toStr, view: view.value },
            { preserveState: true, replace: true, only: ['tasks', 'range'] },
        );
    }
}

function setView(v) {
    view.value = v;
    maybeRefetch();
}

function taskTime(iso) {
    if (!iso) {
        return '';
    }
    return iso.slice(11, 16);
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
</script>

<template>
    <Head title="Görev takvimi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                        Görev takvimi
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Gün, hafta ve ay görünümünde vadeleri izleyin.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link
                        :href="route('tasks.index')"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Liste görünümü
                    </Link>
                    <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                        <button
                            type="button"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                            :class="
                                view === 'day'
                                    ? 'bg-indigo-600 text-white shadow'
                                    : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800'
                            "
                            @click="setView('day')"
                        >
                            Gün
                        </button>
                        <button
                            type="button"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                            :class="
                                view === 'week'
                                    ? 'bg-indigo-600 text-white shadow'
                                    : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800'
                            "
                            @click="setView('week')"
                        >
                            Hafta
                        </button>
                        <button
                            type="button"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                            :class="
                                view === 'month'
                                    ? 'bg-indigo-600 text-white shadow'
                                    : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800'
                            "
                            @click="setView('month')"
                        >
                            Ay
                        </button>
                    </div>
                    <div class="flex items-center gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                        <button
                            type="button"
                            class="rounded-lg p-2 text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                            aria-label="Önceki"
                            @click="prev"
                        >
                            <ChevronLeftIcon class="h-5 w-5" />
                        </button>
                        <button
                            type="button"
                            class="rounded-lg p-2 text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                            aria-label="Sonraki"
                            @click="next"
                        >
                            <ChevronRightIcon class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-6xl space-y-6">
            <p class="text-center text-sm font-semibold capitalize text-indigo-600 dark:text-indigo-400">
                <template v-if="view === 'month'">{{ monthLabel(anchor) }}</template>
                <template v-else-if="view === 'week'">
                    {{ toYmd(weekDays[0].date) }} — {{ toYmd(weekDays[6].date) }}
                </template>
                <template v-else>
                    {{
                        singleDay.date.toLocaleDateString('tr-TR', {
                            weekday: 'long',
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric',
                        })
                    }}
                </template>
            </p>

            <!-- Ay -->
            <div
                v-if="view === 'month'"
                class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="grid grid-cols-7 border-b border-slate-100 bg-slate-50/90 text-center text-xs font-bold uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-800/40 dark:text-slate-400">
                    <div v-for="w in weekdayLabels" :key="w" class="py-3">
                        {{ w }}
                    </div>
                </div>
                <div class="grid grid-cols-7 gap-px bg-slate-100 dark:bg-slate-800">
                    <div
                        v-for="cell in monthCells"
                        :key="cell.ymd"
                        class="min-h-[7.5rem] bg-white p-2 transition hover:bg-slate-50/80 dark:bg-slate-900 dark:hover:bg-slate-800/60"
                        :class="{ 'opacity-40': !cell.inMonth }"
                    >
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">
                            {{ cell.date.getDate() }}
                        </p>
                        <ul class="mt-1 space-y-1">
                            <li v-for="t in (tasksByDay[cell.ymd] || []).slice(0, 4)" :key="t.id">
                                <Link
                                    :href="route('tasks.index', { task: t.id })"
                                    class="block truncate rounded-lg bg-indigo-50 px-1.5 py-0.5 text-[10px] font-medium text-indigo-800 transition hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-200 dark:hover:bg-indigo-900/50"
                                >
                                    {{ taskTime(t.due_at) }} {{ t.title }}
                                </Link>
                            </li>
                            <li
                                v-if="(tasksByDay[cell.ymd] || []).length > 4"
                                class="text-[10px] text-slate-500"
                            >
                                +{{ (tasksByDay[cell.ymd] || []).length - 4 }} daha
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Hafta -->
            <div
                v-else-if="view === 'week'"
                class="grid gap-3 md:grid-cols-7"
            >
                <div
                    v-for="d in weekDays"
                    :key="d.ymd"
                    class="rounded-2xl border border-slate-200/90 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <p class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400">
                        {{ d.date.toLocaleDateString('tr-TR', { weekday: 'short' }) }}
                    </p>
                    <p class="text-lg font-bold text-slate-900 dark:text-white">
                        {{ d.date.getDate() }}
                    </p>
                    <ul class="mt-3 space-y-2">
                        <li
                            v-for="t in tasksByDay[d.ymd] || []"
                            :key="t.id"
                            class="rounded-xl border border-slate-100 bg-slate-50/80 p-2 text-xs dark:border-slate-800 dark:bg-slate-800/40"
                        >
                            <Link
                                :href="route('tasks.index', { task: t.id })"
                                class="font-semibold text-indigo-700 hover:underline dark:text-indigo-300"
                            >
                                {{ t.title }}
                            </Link>
                            <p class="mt-1 text-[10px] text-slate-500">
                                {{ taskTime(t.due_at) }} · {{ TYPE_LABELS[t.type] ?? t.type }}
                            </p>
                            <span
                                class="mt-1 inline-flex rounded-full px-1.5 py-0.5 text-[10px] font-semibold ring-1 ring-inset"
                                :class="statusBadgeClass(displayStatus(t))"
                            >
                                {{ STATUS_LABELS[displayStatus(t)] ?? displayStatus(t) }}
                            </span>
                        </li>
                        <li
                            v-if="!(tasksByDay[d.ymd] || []).length"
                            class="text-xs text-slate-400"
                        >
                            Görev yok
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Gün -->
            <div
                v-else
                class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <ul class="space-y-3">
                    <li
                        v-for="t in tasksByDay[singleDay.ymd] || []"
                        :key="t.id"
                        class="flex flex-col gap-2 rounded-2xl border border-slate-100 bg-slate-50/80 p-4 transition hover:border-indigo-200 hover:bg-white dark:border-slate-800 dark:bg-slate-800/40 dark:hover:border-indigo-500/30 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="min-w-0 flex-1">
                            <Link
                                :href="route('tasks.index', { task: t.id })"
                                class="text-base font-bold text-slate-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400"
                            >
                                {{ t.title }}
                            </Link>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ TYPE_LABELS[t.type] ?? t.type }} · {{ PRIORITY_LABELS[t.priority] ?? PRIORITY_LABELS.medium }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                {{ taskTime(t.due_at) }}
                            </span>
                            <div
                                v-if="t.assigned_user"
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-slate-600 to-slate-800 text-xs font-bold text-white"
                                :title="t.assigned_user.name"
                            >
                                {{ initials(t.assigned_user.name) }}
                            </div>
                        </div>
                    </li>
                    <li
                        v-if="!(tasksByDay[singleDay.ymd] || []).length"
                        class="py-12 text-center text-sm text-slate-500"
                    >
                        Bu gün için görev yok.
                    </li>
                </ul>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
