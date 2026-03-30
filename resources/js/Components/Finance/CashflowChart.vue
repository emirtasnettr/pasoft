<script setup>
import { formatMoneyTr } from '@/lib/financeLabels';
import { computed, ref } from 'vue';

const props = defineProps({
    cashflow: { type: Object, required: true },
});

const tab = ref('monthly');

const active = computed(() => (tab.value === 'daily' ? props.cashflow.daily : props.cashflow.monthly));

const series = computed(() => [{ name: 'Tahsilat', data: active.value.values.map((v) => Number(v)) }]);

const options = computed(() => ({
    chart: {
        fontFamily: 'inherit',
        toolbar: { show: false },
        zoom: { enabled: false },
        animations: { enabled: true, easing: 'easeinout', speed: 700 },
    },
    stroke: { curve: 'smooth', width: 3, lineCap: 'round' },
    colors: ['#6366f1'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.35,
            opacityTo: 0.05,
            stops: [0, 90, 100],
        },
    },
    dataLabels: { enabled: false },
    xaxis: {
        categories: active.value.labels,
        labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 500 } },
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        labels: {
            style: { colors: '#64748b', fontSize: '11px' },
            formatter: (v) => `₺${Number(v).toLocaleString('tr-TR')}`,
        },
    },
    grid: { borderColor: '#e2e8f0', strokeDashArray: 4, padding: { top: 12, right: 12 } },
    tooltip: {
        theme: 'light',
        y: { formatter: (v) => formatMoneyTr(v) },
    },
}));
</script>

<template>
    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Nakit akışı</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Tahsilat girişlerine göre (son 30 gün / 12 ay). Tarih filtreleri grafiği kısıtlar.
                </p>
            </div>
            <div class="inline-flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800">
                <button
                    type="button"
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                    :class="
                        tab === 'daily'
                            ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white'
                            : 'text-slate-600 dark:text-slate-400'
                    "
                    @click="tab = 'daily'"
                >
                    Günlük
                </button>
                <button
                    type="button"
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                    :class="
                        tab === 'monthly'
                            ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white'
                            : 'text-slate-600 dark:text-slate-400'
                    "
                    @click="tab = 'monthly'"
                >
                    Aylık
                </button>
            </div>
        </div>
        <div class="mt-4 min-h-[260px]">
            <apexchart height="260" type="area" :options="options" :series="series" />
        </div>
    </div>
</template>
