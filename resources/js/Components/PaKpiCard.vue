<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: { type: String, required: true },
    value: { type: [Number, String], required: true },
    hint: { type: String, default: '' },
    changePct: { type: Number, default: 0 },
    direction: { type: String, default: 'flat' },
    sparkline: { type: Array, default: () => [] },
    accent: {
        type: String,
        default: 'indigo',
    },
});

const accentColors = {
    indigo: {
        stroke: '#6366f1',
        gradientFrom: 'rgba(99, 102, 241, 0.22)',
        gradientTo: 'rgba(99, 102, 241, 0.02)',
        value: 'text-slate-900 dark:text-white',
    },
    emerald: {
        stroke: '#10b981',
        gradientFrom: 'rgba(16, 185, 129, 0.22)',
        gradientTo: 'rgba(16, 185, 129, 0.02)',
        value: 'text-slate-900 dark:text-white',
    },
    violet: {
        stroke: '#8b5cf6',
        gradientFrom: 'rgba(139, 92, 246, 0.22)',
        gradientTo: 'rgba(139, 92, 246, 0.02)',
        value: 'text-slate-900 dark:text-white',
    },
    amber: {
        stroke: '#f59e0b',
        gradientFrom: 'rgba(245, 158, 11, 0.25)',
        gradientTo: 'rgba(245, 158, 11, 0.02)',
        value: 'text-amber-700 dark:text-amber-300',
    },
};

const c = computed(() => accentColors[props.accent] ?? accentColors.indigo);

const trendLabel = computed(() => {
    if (props.direction === 'flat' || props.changePct === 0) {
        return 'Değişim yok';
    }
    const arrow = props.direction === 'up' ? '↑' : '↓';
    return `${arrow} %${props.changePct}`;
});

const trendClass = computed(() => {
    if (props.direction === 'flat' || props.changePct === 0) {
        return 'text-slate-400 dark:text-slate-500';
    }
    return props.direction === 'up'
        ? 'text-emerald-600 dark:text-emerald-400'
        : 'text-rose-600 dark:text-rose-400';
});

const sparkSeries = computed(() => [{ name: 's', data: props.sparkline.length ? props.sparkline : [0, 0, 0, 0, 0, 0, 0] }]);

const sparkOptions = computed(() => ({
    chart: {
        sparkline: { enabled: true },
        animations: { enabled: true, speed: 600 },
        fontFamily: 'inherit',
        toolbar: { show: false },
    },
    stroke: {
        curve: 'smooth',
        width: 2.5,
        lineCap: 'round',
    },
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.32,
            opacityTo: 0.04,
        },
    },
    colors: [c.value.stroke],
    tooltip: {
        theme: 'light',
        style: { fontSize: '12px', fontFamily: 'inherit' },
        fixed: { enabled: false },
        x: { show: false },
        marker: { show: true },
        y: {
            formatter: (val) => `${val}`,
            title: { formatter: () => '' },
        },
    },
    grid: { show: false },
    xaxis: { labels: { show: false }, axisBorder: { show: false }, axisTicks: { show: false } },
    yaxis: { show: false },
}));
</script>

<template>
    <div
        class="group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-gradient-to-br from-white via-white to-slate-50/80 p-7 shadow-sm shadow-slate-900/[0.04] ring-1 ring-slate-900/[0.03] transition-all duration-300 ease-out will-change-transform hover:-translate-y-1 hover:scale-[1.03] hover:shadow-xl hover:shadow-indigo-500/[0.08] hover:ring-indigo-500/15 dark:border-slate-800 dark:from-slate-900 dark:via-slate-900 dark:to-slate-800/80 dark:shadow-none dark:ring-white/[0.05] dark:hover:shadow-indigo-500/10"
    >
        <div
            class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full opacity-[0.07] blur-2xl transition-opacity duration-500 group-hover:opacity-[0.12]"
            :style="{ background: c.stroke }"
        />
        <div class="relative flex flex-col gap-1">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                {{ title }}
            </p>
            <div class="flex items-end justify-between gap-3">
                <p
                    class="text-3xl font-bold tabular-nums tracking-tight"
                    :class="c.value"
                >
                    {{ value }}
                </p>
                <div class="min-w-[5.5rem] shrink-0 opacity-90">
                    <apexchart
                        type="area"
                        height="48"
                        width="100%"
                        :options="sparkOptions"
                        :series="sparkSeries"
                    />
                </div>
            </div>
            <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1">
                <span class="text-xs font-semibold" :class="trendClass">
                    {{ trendLabel }}
                </span>
                <span class="text-xs text-slate-500 dark:text-slate-500">
                    {{ hint }}
                </span>
            </div>
        </div>
    </div>
</template>
