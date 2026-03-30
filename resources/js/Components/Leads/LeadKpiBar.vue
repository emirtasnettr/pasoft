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
                Toplam lead
            </p>
            <p class="mt-2 text-3xl font-bold tabular-nums text-slate-900 dark:text-white">
                {{ kpi.total?.toLocaleString('tr-TR') ?? '0' }}
            </p>
        </div>
        <div
            class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Bugün gelen
            </p>
            <p class="mt-2 text-3xl font-bold tabular-nums text-indigo-600 dark:text-indigo-400">
                {{ kpi.today?.toLocaleString('tr-TR') ?? '0' }}
            </p>
        </div>
        <div
            class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Dönüşüm (kazanılan / kapanan)
            </p>
            <p class="mt-2 text-3xl font-bold tabular-nums text-emerald-600 dark:text-emerald-400">
                %{{ kpi.conversionRate ?? 0 }}
            </p>
        </div>
        <div
            class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Kaybedilen
            </p>
            <p class="mt-2 text-3xl font-bold tabular-nums text-rose-600 dark:text-rose-400">
                {{ kpi.lost?.toLocaleString('tr-TR') ?? '0' }}
            </p>
        </div>
    </div>
</template>
