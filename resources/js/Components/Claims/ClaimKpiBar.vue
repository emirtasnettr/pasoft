<script setup>
defineProps({
    kpi: { type: Object, required: true },
});
</script>

<template>
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div
            class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Açık hasar
            </p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-slate-900 dark:text-white">
                {{ kpi.open }}
            </p>
        </div>
        <div
            class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Bu ay açılan
            </p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-sky-700 dark:text-sky-300">
                {{ kpi.opened_this_month }}
            </p>
        </div>
        <div
            class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Bu ay kapanan
            </p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-emerald-700 dark:text-emerald-300">
                {{ kpi.closed_this_month }}
            </p>
        </div>
        <div
            class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Ödeme bekleyen
            </p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-amber-700 dark:text-amber-300">
                {{ kpi.pending_payment }}
            </p>
        </div>
    </div>
</template>
