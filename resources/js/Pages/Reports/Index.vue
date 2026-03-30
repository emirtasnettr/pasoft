<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowTrendingDownIcon,
    ArrowTrendingUpIcon,
    CalendarDaysIcon,
    ChartBarIcon,
    LightBulbIcon,
} from '@heroicons/vue/24/outline';
import { computed, reactive, watch } from 'vue';

const props = defineProps({
    analytics: { type: Object, required: true },
});

const custom = reactive({
    from: props.analytics.filters?.from ?? '',
    to: props.analytics.filters?.to ?? '',
});

watch(
    () => props.analytics.filters,
    (f) => {
        if (!f) {
            return;
        }
        custom.from = f.from ?? '';
        custom.to = f.to ?? '';
    },
    { deep: true },
);

function setPeriod(period) {
    router.get(
        route('reports.index'),
        period === 'custom' ? { period: 'custom', from: custom.from, to: custom.to } : { period },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function applyCustom() {
    setPeriod('custom');
}

const periodActive = (p) => props.analytics.period === p;

function formatKpiValue(k) {
    if (k.suffix === '%') {
        return `%${Number(k.value).toLocaleString('tr-TR', { maximumFractionDigits: 1 })}`;
    }
    return `₺${Number(k.value).toLocaleString('tr-TR', { maximumFractionDigits: 0 })}`;
}

function trendUi(k) {
    if (k.direction === 'flat' || k.change_pct === 0) {
        return { cls: 'text-slate-400 dark:text-slate-500', label: 'Değişim yok' };
    }
    const up = k.direction === 'up';
    return {
        cls: up ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400',
        label: `${up ? '↑' : '↓'} %${k.change_pct} önceki döneme göre`,
    };
}

const chartStroke = '#6366f1';
const chartGrid = '#e2e8f0';
const chartMuted = '#64748b';

const areaBase = computed(() => ({
    chart: {
        toolbar: { show: false },
        fontFamily: 'inherit',
        zoom: { enabled: false },
        animations: { enabled: true, easing: 'easeinout', speed: 750 },
    },
    stroke: { curve: 'smooth', width: 3, lineCap: 'round' },
    dataLabels: { enabled: false },
    xaxis: {
        labels: { style: { colors: chartMuted, fontSize: '11px', fontWeight: 500 } },
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    grid: { borderColor: chartGrid, strokeDashArray: 4, padding: { top: 8, right: 8 } },
    tooltip: {
        theme: 'light',
        style: { fontSize: '13px', fontFamily: 'inherit' },
    },
}));

const revenueTrendOptions = computed(() => ({
    ...areaBase.value,
    colors: [chartStroke],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.35,
            opacityTo: 0.05,
            stops: [0, 90, 100],
        },
    },
    xaxis: {
        ...areaBase.value.xaxis,
        categories: props.analytics.trends?.revenue?.labels ?? [],
    },
    yaxis: {
        labels: {
            style: { colors: chartMuted, fontSize: '11px' },
            formatter: (v) => `₺${Number(v).toLocaleString('tr-TR')}`,
        },
    },
    tooltip: {
        ...areaBase.value.tooltip,
        y: { formatter: (v) => `₺${Number(v).toLocaleString('tr-TR')}` },
    },
}));

const revenueTrendSeries = computed(() => [
    { name: 'Tahsilat', data: props.analytics.trends?.revenue?.values ?? [] },
]);

const commissionTrendOptions = computed(() => ({
    ...areaBase.value,
    colors: ['#8b5cf6'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.32,
            opacityTo: 0.05,
            stops: [0, 90, 100],
        },
    },
    xaxis: {
        ...areaBase.value.xaxis,
        categories: props.analytics.trends?.commission?.labels ?? [],
    },
    yaxis: {
        labels: {
            style: { colors: chartMuted, fontSize: '11px' },
            formatter: (v) => `₺${Number(v).toLocaleString('tr-TR')}`,
        },
    },
    tooltip: {
        ...areaBase.value.tooltip,
        y: { formatter: (v) => `₺${Number(v).toLocaleString('tr-TR')}` },
    },
}));

const commissionTrendSeries = computed(() => [
    { name: 'Komisyon', data: props.analytics.trends?.commission?.values ?? [] },
]);

const policiesTrendOptions = computed(() => ({
    ...areaBase.value,
    colors: ['#14b8a6'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.3,
            opacityTo: 0.05,
            stops: [0, 90, 100],
        },
    },
    xaxis: {
        ...areaBase.value.xaxis,
        categories: props.analytics.trends?.policies?.labels ?? [],
    },
    yaxis: {
        labels: { style: { colors: chartMuted, fontSize: '11px' } },
    },
    tooltip: {
        ...areaBase.value.tooltip,
        y: { formatter: (v) => `${Math.round(v)} poliçe` },
    },
}));

const policiesTrendSeries = computed(() => [
    { name: 'Satış', data: props.analytics.trends?.policies?.values ?? [] },
]);

const repLabels = computed(() => (props.analytics.rep_performance ?? []).map((r) => r.name));
const repValues = computed(() => (props.analytics.rep_performance ?? []).map((r) => r.revenue));

const repColors = computed(() => {
    const n = repValues.value.length;
    if (!n) {
        return ['#6366f1'];
    }
    return repValues.value.map((_, i) => (i === 0 ? '#f59e0b' : '#6366f1'));
});

const repOptions = computed(() => ({
    chart: { fontFamily: 'inherit', toolbar: { show: false }, animations: { speed: 700 } },
    plotOptions: {
        bar: {
            borderRadius: 10,
            horizontal: true,
            barHeight: '72%',
            distributed: true,
            dataLabels: { position: 'top' },
        },
    },
    colors: repColors.value,
    dataLabels: {
        enabled: true,
        formatter: (v) => `₺${Number(v).toLocaleString('tr-TR')}`,
        offsetX: 24,
        style: { fontSize: '11px', colors: ['#334155'] },
    },
    xaxis: {
        categories: repLabels.value,
        labels: {
            style: { colors: chartMuted, fontSize: '11px' },
            formatter: (v) => `₺${Number(v).toLocaleString('tr-TR')}`,
        },
    },
    yaxis: { labels: { style: { colors: chartMuted, fontSize: '12px', fontWeight: 600 } } },
    grid: { borderColor: chartGrid, strokeDashArray: 4 },
    legend: { show: false },
    tooltip: {
        y: { formatter: (v) => `₺${Number(v).toLocaleString('tr-TR')}` },
    },
}));

const repSeries = computed(() => [{ name: 'Tahsilat', data: repValues.value }]);

const repChartHeight = computed(() => Math.max(280, (repValues.value.length || 1) * 52));

const donutLabels = computed(() => props.analytics.revenue_by_type?.labels ?? []);
const donutSeries = computed(() => props.analytics.revenue_by_type?.values ?? []);

const donutOptions = computed(() => ({
    chart: { fontFamily: 'inherit' },
    labels: donutLabels.value,
    legend: { position: 'bottom', fontSize: '12px' },
    plotOptions: {
        pie: {
            donut: { size: '68%' },
        },
    },
    dataLabels: { enabled: false },
    colors: ['#6366f1', '#8b5cf6', '#ec4899', '#14b8a6', '#f59e0b', '#0ea5e9'],
}));

const funnelStages = computed(() => props.analytics.funnel?.stages ?? []);
const funnelMax = computed(() =>
    Math.max(1, ...funnelStages.value.map((s) => s.count)),
);

const funnelOptions = computed(() => ({
    chart: { fontFamily: 'inherit', toolbar: { show: false } },
    plotOptions: { bar: { borderRadius: 12, horizontal: true, barHeight: '70%' } },
    colors: ['#4f46e5'],
    dataLabels: {
        enabled: true,
        formatter: (val) => `${val}`,
        style: { colors: ['#0f172a'], fontSize: '12px', fontWeight: 600 },
    },
    xaxis: {
        categories: funnelStages.value.map((s) => s.label),
        max: funnelMax.value,
    },
    yaxis: { labels: { style: { colors: chartMuted, fontSize: '11px' } } },
    grid: { borderColor: chartGrid },
}));

const funnelSeries = computed(() => [
    { name: 'Adet', data: funnelStages.value.map((s) => s.count) },
]);
</script>

<template>
    <Head title="Raporlar & analitik" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2">
                <div class="flex flex-wrap items-center gap-2">
                    <ChartBarIcon class="h-6 w-6 text-indigo-500" />
                    <h1 class="text-lg font-semibold text-slate-900 dark:text-white">
                        Analitik & raporlama
                    </h1>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Karar destek paneli — {{ analytics.period_label }} · KPI, trend ve performans.
                </p>
            </div>
        </template>

        <div class="mx-auto max-w-7xl space-y-8 pb-10">
            <!-- Tarih filtresi -->
            <div
                class="flex flex-col gap-4 rounded-2xl border border-slate-200/90 bg-gradient-to-br from-white to-slate-50/80 p-5 shadow-sm dark:border-slate-800 dark:from-slate-900 dark:to-slate-950"
            >
                <div class="flex flex-wrap items-center gap-2">
                    <CalendarDaysIcon class="h-5 w-5 text-slate-500" />
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Dönem</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                        :class="
                            periodActive('today')
                                ? 'bg-indigo-600 text-white shadow-md'
                                : 'border border-slate-200 bg-white text-slate-700 hover:border-indigo-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200'
                        "
                        @click="setPeriod('today')"
                    >
                        Bugün
                    </button>
                    <button
                        type="button"
                        class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                        :class="
                            periodActive('week')
                                ? 'bg-indigo-600 text-white shadow-md'
                                : 'border border-slate-200 bg-white text-slate-700 hover:border-indigo-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200'
                        "
                        @click="setPeriod('week')"
                    >
                        Bu hafta
                    </button>
                    <button
                        type="button"
                        class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                        :class="
                            periodActive('month')
                                ? 'bg-indigo-600 text-white shadow-md'
                                : 'border border-slate-200 bg-white text-slate-700 hover:border-indigo-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200'
                        "
                        @click="setPeriod('month')"
                    >
                        Bu ay
                    </button>
                </div>
                <div class="flex flex-wrap items-end gap-3 border-t border-slate-100 pt-4 dark:border-slate-800">
                    <div>
                        <label class="text-xs font-semibold text-slate-500">Başlangıç</label>
                        <input
                            v-model="custom.from"
                            type="date"
                            class="mt-1 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">Bitiş</label>
                        <input
                            v-model="custom.to"
                            type="date"
                            class="mt-1 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        />
                    </div>
                    <button
                        type="button"
                        class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100"
                        @click="applyCustom"
                    >
                        Özel tarih uygula
                    </button>
                </div>
            </div>

            <!-- Akıllı yorum -->
            <div
                class="rounded-2xl border border-indigo-200/80 bg-gradient-to-r from-indigo-50 to-violet-50 p-5 shadow-sm dark:border-indigo-900/40 dark:from-indigo-950/40 dark:to-violet-950/30"
            >
                <div class="flex items-center gap-2 text-indigo-900 dark:text-indigo-100">
                    <LightBulbIcon class="h-6 w-6" />
                    <h2 class="text-base font-bold">Otomatik yorumlar</h2>
                </div>
                <ul class="mt-4 space-y-2">
                    <li
                        v-for="(ins, idx) in analytics.insights || []"
                        :key="idx"
                        class="flex gap-2 text-sm leading-relaxed"
                        :class="
                            ins.type === 'positive'
                                ? 'text-emerald-800 dark:text-emerald-200'
                                : ins.type === 'negative'
                                  ? 'text-rose-800 dark:text-rose-200'
                                  : ins.type === 'warning'
                                    ? 'text-amber-900 dark:text-amber-200'
                                    : 'text-slate-700 dark:text-slate-300'
                        "
                    >
                        <span class="shrink-0 font-bold">•</span>
                        <span>{{ ins.text }}</span>
                    </li>
                </ul>
            </div>

            <!-- KPI -->
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="(k, i) in analytics.kpis || []"
                    :key="i"
                    class="group rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
                >
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        {{ k.title }}
                    </p>
                    <p class="mt-2 text-2xl font-bold tabular-nums text-slate-900 dark:text-white">
                        {{ formatKpiValue(k) }}
                    </p>
                    <div class="mt-3 flex items-center gap-1.5 text-sm font-semibold" :class="trendUi(k).cls">
                        <ArrowTrendingUpIcon v-if="k.direction === 'up'" class="h-4 w-4" />
                        <ArrowTrendingDownIcon v-else-if="k.direction === 'down'" class="h-4 w-4" />
                        <span>{{ trendUi(k).label }}</span>
                    </div>
                </div>
            </div>

            <!-- Trendler -->
            <div>
                <h2 class="mb-4 text-base font-bold text-slate-900 dark:text-white">Trend (son 12 ay)</h2>
                <div class="grid gap-6 lg:grid-cols-3">
                    <div
                        class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Aylık tahsilat</h3>
                        <apexchart type="area" height="260" :options="revenueTrendOptions" :series="revenueTrendSeries" />
                    </div>
                    <div
                        class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Komisyon (yeni iş)</h3>
                        <apexchart
                            type="area"
                            height="260"
                            :options="commissionTrendOptions"
                            :series="commissionTrendSeries"
                        />
                    </div>
                    <div
                        class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Poliçe satış adedi</h3>
                        <apexchart
                            type="area"
                            height="260"
                            :options="policiesTrendOptions"
                            :series="policiesTrendSeries"
                        />
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Temsilci -->
                <div
                    class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Satış temsilcisi performansı</h2>
                    <p class="mt-1 text-xs text-slate-500">Dönem içi poliçe tahsilatı (owner). İlk sıra vurgulu.</p>
                    <apexchart
                        v-if="repValues.length"
                        type="bar"
                        :height="repChartHeight"
                        :options="repOptions"
                        :series="repSeries"
                    />
                    <p v-else class="mt-8 text-sm text-slate-500">Bu dönemde poliçeye bağlı tahsilat yok.</p>
                </div>

                <!-- Gelir dağılımı -->
                <div
                    class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Gelir — poliçe türü</h2>
                    <p class="mt-1 text-xs text-slate-500">Seçili dönem tahsilatları.</p>
                    <apexchart
                        v-if="donutSeries.length"
                        type="donut"
                        height="340"
                        :options="donutOptions"
                        :series="donutSeries"
                    />
                    <p v-else class="mt-8 text-sm text-slate-500">Tür bazlı tahsilat yok.</p>
                </div>
            </div>

            <!-- Yenileme + funnel -->
            <div class="grid gap-6 lg:grid-cols-2">
                <div
                    class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Yenileme özeti</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-xl bg-emerald-50 p-4 dark:bg-emerald-950/30">
                            <p class="text-xs font-semibold text-emerald-800 dark:text-emerald-200">Yenilenen</p>
                            <p class="mt-1 text-2xl font-bold text-emerald-900 dark:text-emerald-100">
                                {{ analytics.renewal_report?.renewed_in_period ?? 0 }}
                            </p>
                            <p class="mt-1 text-[11px] text-emerald-700 dark:text-emerald-300">Dönemde güncellenen</p>
                        </div>
                        <div class="rounded-xl bg-rose-50 p-4 dark:bg-rose-950/30">
                            <p class="text-xs font-semibold text-rose-800 dark:text-rose-200">Yenilenmedi</p>
                            <p class="mt-1 text-2xl font-bold text-rose-900 dark:text-rose-100">
                                {{ analytics.renewal_report?.not_renewed_open ?? 0 }}
                            </p>
                            <p class="mt-1 text-[11px] text-rose-700 dark:text-rose-300">Açık kayıt</p>
                        </div>
                        <div class="rounded-xl bg-amber-50 p-4 dark:bg-amber-950/30">
                            <p class="text-xs font-semibold text-amber-900 dark:text-amber-200">Yaklaşan 30g</p>
                            <p class="mt-1 text-2xl font-bold text-amber-950 dark:text-amber-100">
                                {{ analytics.renewal_report?.upcoming_30d ?? 0 }}
                            </p>
                            <p class="mt-1 text-[11px] text-amber-800 dark:text-amber-300">Bitiş yakında</p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Lead → satış hunisi</h2>
                    <p class="mt-1 text-xs text-slate-500">Dönemde oluşturulan leadler (kümülatif aşama).</p>
                    <apexchart type="bar" height="280" :options="funnelOptions" :series="funnelSeries" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
