<script setup>
defineProps({
    kpi: { type: Object, required: true },
});
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div
            class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Toplam müşteri
            </p>
            <p class="mt-2 text-3xl font-bold tabular-nums text-slate-900 dark:text-white">
                {{ kpi.total?.toLocaleString('tr-TR') ?? '0' }}
            </p>
        </div>
        <div
            class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Aktif (poliçesi devam eden)
            </p>
            <p class="mt-2 text-3xl font-bold tabular-nums text-emerald-600 dark:text-emerald-400">
                {{ kpi.active?.toLocaleString('tr-TR') ?? '0' }}
            </p>
        </div>
        <div
            class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Bu ay yeni
            </p>
            <p class="mt-2 text-3xl font-bold tabular-nums text-indigo-600 dark:text-indigo-400">
                {{ kpi.newThisMonth?.toLocaleString('tr-TR') ?? '0' }}
            </p>
        </div>
        <div
            class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Riskli / takip
            </p>
            <p class="mt-2 text-3xl font-bold tabular-nums text-rose-600 dark:text-rose-400">
                {{ kpi.risky?.toLocaleString('tr-TR') ?? '0' }}
            </p>
            <p class="mt-1 text-[11px] text-slate-500">
                Yaklaşan bitiş veya ödeme bekleyen poliçe
            </p>
        </div>
    </div>
</template>
