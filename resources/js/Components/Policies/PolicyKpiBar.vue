<script setup>
defineProps({
    kpi: { type: Object, required: true },
});
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div
            class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Toplam poliçe
            </p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-slate-900 dark:text-white">
                {{ kpi.total?.toLocaleString('tr-TR') ?? '0' }}
            </p>
        </div>
        <div
            class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Aktif
            </p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-emerald-600 dark:text-emerald-400">
                {{ kpi.active?.toLocaleString('tr-TR') ?? '0' }}
            </p>
        </div>
        <div
            class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Bu ay yenilenen
            </p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-indigo-600 dark:text-indigo-400">
                {{ kpi.renewed_this_month?.toLocaleString('tr-TR') ?? '0' }}
            </p>
        </div>
        <div
            class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Süresi dolan
            </p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-red-600 dark:text-red-400">
                {{ kpi.expired?.toLocaleString('tr-TR') ?? '0' }}
            </p>
        </div>
        <div
            class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Bekleyen yenileme
            </p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-amber-600 dark:text-amber-400">
                {{ kpi.pending_renewals?.toLocaleString('tr-TR') ?? '0' }}
            </p>
        </div>
    </div>
</template>
