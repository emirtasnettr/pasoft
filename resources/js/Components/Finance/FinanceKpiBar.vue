<script setup>
import { formatMoneyTr } from '@/lib/financeLabels';

defineProps({
    kpi: { type: Object, required: true },
});
</script>

<template>
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div
            class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Toplam tahsilat
            </p>
            <p class="mt-2 text-xl font-bold tabular-nums text-slate-900 dark:text-white">
                {{ formatMoneyTr(kpi.total_collected) }}
            </p>
        </div>
        <div
            class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Bu ay tahsilat
            </p>
            <p class="mt-2 text-xl font-bold tabular-nums text-indigo-700 dark:text-indigo-300">
                {{ formatMoneyTr(kpi.this_month) }}
            </p>
        </div>
        <div
            class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Bekleyen ödeme
            </p>
            <p class="mt-2 text-xl font-bold tabular-nums text-amber-700 dark:text-amber-300">
                {{ formatMoneyTr(kpi.pending_payment) }}
            </p>
        </div>
        <div
            class="rounded-2xl border border-rose-200/90 bg-rose-50/50 p-4 shadow-sm dark:border-rose-500/30 dark:bg-rose-950/30"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-rose-700 dark:text-rose-300">
                Geciken ödeme
            </p>
            <p class="mt-2 text-xl font-bold tabular-nums text-rose-700 dark:text-rose-200">
                {{ formatMoneyTr(kpi.overdue_payment) }}
            </p>
        </div>
    </div>
</template>
