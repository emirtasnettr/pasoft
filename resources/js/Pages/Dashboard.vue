<script setup>
import PaCard from '@/Components/PaCard.vue';
import PaEmptyState from '@/Components/PaEmptyState.vue';
import PaKpiCard from '@/Components/PaKpiCard.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { CubeTransparentIcon } from '@heroicons/vue/24/outline';
import { computed } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
});

const kpi = computed(() => props.stats.kpi ?? {});

function kpiOr(key, field, fallback) {
    const block = kpi.value[key];
    if (block && field in block) {
        return block[field];
    }
    if (field === 'value' && props.stats.counts?.[key] !== undefined) {
        return props.stats.counts[key];
    }
    return fallback;
}

const revenueOptions = computed(() => ({
    chart: {
        toolbar: { show: false },
        fontFamily: 'inherit',
        zoom: { enabled: false },
        animations: { enabled: true, easing: 'easeinout', speed: 800 },
    },
    stroke: {
        curve: 'smooth',
        width: 3.5,
        lineCap: 'round',
    },
    colors: ['#6366f1'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.28,
            opacityTo: 0.04,
            stops: [0, 90, 100],
        },
    },
    dataLabels: { enabled: false },
    xaxis: {
        categories: props.stats.revenue_trend.labels,
        labels: {
            style: { colors: '#64748b', fontSize: '11px', fontWeight: 500 },
        },
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        labels: {
            style: { colors: '#64748b', fontSize: '11px', fontWeight: 500 },
            formatter: (v) => `₺${Number(v).toLocaleString('tr-TR')}`,
        },
    },
    grid: {
        borderColor: '#e2e8f0',
        strokeDashArray: 4,
        padding: { top: 12, right: 12 },
    },
    tooltip: {
        theme: 'light',
        style: { fontSize: '13px', fontFamily: 'inherit' },
        cssClass: 'pa-chart-tooltip',
        y: {
            formatter: (v) => `₺${Number(v).toLocaleString('tr-TR')}`,
        },
        marker: { show: true },
    },
}));

const revenueSeries = computed(() => [
    { name: 'Tahsilat', data: props.stats.revenue_trend.values },
]);

const stageOrder = ['new', 'contacted', 'proposal', 'meeting', 'won', 'lost'];
const stageLabels = {
    new: 'Yeni',
    contacted: 'İletişim',
    proposal: 'Teklif',
    meeting: 'Görüşme',
    won: 'Kazanıldı',
    lost: 'Kaybedildi',
};

const funnelOptions = computed(() => {
    const m = props.stats.leads_by_stage ?? {};
    const categories = stageOrder.map((s) => stageLabels[s] ?? s);
    return {
        chart: {
            fontFamily: 'inherit',
            toolbar: { show: false },
            animations: { enabled: true, speed: 700 },
        },
        plotOptions: {
            bar: {
                borderRadius: 12,
                horizontal: true,
                barHeight: '64%',
                distributed: false,
            },
        },
        colors: ['#818cf8'],
        dataLabels: {
            enabled: true,
            formatter: (val) => `${val}`,
            style: {
                colors: ['#0f172a'],
                fontSize: '12px',
                fontWeight: 600,
            },
        },
        xaxis: {
            categories,
            labels: { style: { colors: '#64748b', fontSize: '11px' } },
        },
        grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
        tooltip: {
            theme: 'light',
            style: { fontSize: '13px' },
        },
    };
});

const funnelSeries = computed(() => {
    const m = props.stats.leads_by_stage ?? {};
    return [
        {
            name: 'Lead',
            data: stageOrder.map((s) => Number(m[s] ?? 0)),
        },
    ];
});

const hasTopProducts = computed(
    () => Object.keys(props.stats.top_policy_types ?? {}).length > 0,
);
</script>

<template>
    <Head title="Özet" />

    <AuthenticatedLayout>
        <template #header>
            <div class="space-y-1">
                <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Günün özeti
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Satış, poliçe ve tahsilat performansını tek ekranda izleyin.
                </p>
            </div>
        </template>

        <div class="mx-auto max-w-7xl space-y-10 pb-4">
            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                <PaKpiCard
                    title="Müşteriler"
                    :value="kpiOr('customers', 'value', 0)"
                    :hint="kpi.customers?.hint ?? 'Toplam kayıtlı müşteri'"
                    :change-pct="kpiOr('customers', 'change_pct', 0)"
                    :direction="kpiOr('customers', 'direction', 'flat')"
                    :sparkline="kpi.customers?.sparkline ?? []"
                    accent="indigo"
                />
                <PaKpiCard
                    title="Açık lead"
                    :value="kpiOr('leads_open', 'value', 0)"
                    :hint="kpi.leads_open?.hint ?? 'Kazanıldı / kaybedildi hariç'"
                    :change-pct="kpiOr('leads_open', 'change_pct', 0)"
                    :direction="kpiOr('leads_open', 'direction', 'flat')"
                    :sparkline="kpi.leads_open?.sparkline ?? []"
                    accent="violet"
                />
                <PaKpiCard
                    title="Aktif poliçe"
                    :value="kpiOr('policies_active', 'value', 0)"
                    :hint="kpi.policies_active?.hint ?? 'Yürürlükteki sözleşmeler'"
                    :change-pct="kpiOr('policies_active', 'change_pct', 0)"
                    :direction="kpiOr('policies_active', 'direction', 'flat')"
                    :sparkline="kpi.policies_active?.sparkline ?? []"
                    accent="emerald"
                />
                <PaKpiCard
                    title="30 gün içinde yenileme"
                    :value="kpiOr('renewals_due_30', 'value', 0)"
                    :hint="kpi.renewals_due_30?.hint ?? 'Yaklaşan bitiş tarihleri'"
                    :change-pct="kpiOr('renewals_due_30', 'change_pct', 0)"
                    :direction="kpiOr('renewals_due_30', 'direction', 'flat')"
                    :sparkline="kpi.renewals_due_30?.sparkline ?? []"
                    accent="amber"
                />
            </div>

            <div class="grid gap-8 lg:grid-cols-3">
                <PaCard class="lg:col-span-2" :hover="true">
                    <div class="mb-6 flex items-start justify-between gap-4">
                        <div class="space-y-1">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                Tahsilat trendi
                            </h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Son 6 ay — ödenen primler
                            </p>
                        </div>
                    </div>
                    <apexchart
                        type="area"
                        height="320"
                        :options="revenueOptions"
                        :series="revenueSeries"
                    />
                </PaCard>

                <PaCard :hover="true" class="flex flex-col justify-between">
                    <div class="space-y-6">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                Ciro özeti
                            </h2>
                            <p class="mt-3 text-3xl font-bold tabular-nums tracking-tight text-slate-900 dark:text-white">
                                ₺{{ Number(stats.revenue.month).toLocaleString('tr-TR') }}
                            </p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Bu ay
                            </p>
                        </div>
                        <div class="border-t border-slate-100 pt-6 dark:border-slate-800">
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Bugün
                            </p>
                            <p class="text-xl font-bold tabular-nums text-slate-900 dark:text-white">
                                ₺{{ Number(stats.revenue.today).toLocaleString('tr-TR') }}
                            </p>
                        </div>
                        <div class="border-t border-slate-100 pt-6 dark:border-slate-800">
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Lead → satış dönüşümü
                            </p>
                            <p class="text-xl font-bold tabular-nums text-indigo-600 dark:text-indigo-400">
                                %{{ stats.lead_conversion_rate }}
                            </p>
                        </div>
                    </div>
                </PaCard>
            </div>

            <div class="grid gap-8 lg:grid-cols-2">
                <PaCard :hover="true">
                    <div class="mb-6 space-y-1">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                            Lead aşamaları
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Pipeline dağılımı
                        </p>
                    </div>
                    <apexchart
                        type="bar"
                        height="300"
                        :options="funnelOptions"
                        :series="funnelSeries"
                    />
                </PaCard>

                <PaCard :hover="true">
                    <div class="mb-6 space-y-1">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                            En çok satılan ürünler
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Poliçe türü bazında adet
                        </p>
                    </div>

                    <ul v-if="hasTopProducts" class="space-y-3">
                        <li
                            v-for="(count, name) in stats.top_policy_types"
                            :key="name"
                            class="flex items-center justify-between rounded-xl border border-slate-100/80 bg-gradient-to-r from-slate-50/80 to-white px-5 py-3.5 transition hover:border-indigo-100 hover:from-indigo-50/40 hover:shadow-sm dark:border-slate-800 dark:from-slate-800/50 dark:to-slate-900/40 dark:hover:border-indigo-500/20"
                        >
                            <span class="text-sm font-medium text-slate-800 dark:text-slate-200">
                                {{ name }}
                            </span>
                            <span
                                class="rounded-lg bg-indigo-50 px-2.5 py-1 text-sm font-bold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300"
                            >
                                {{ count }}
                            </span>
                        </li>
                    </ul>

                    <PaEmptyState
                        v-else
                        title="Henüz poliçe verisi yok"
                        description="İlk poliçeyi oluşturduğunuzda burada en çok satılan türler listelenir."
                        action-label="Poliçe ekle"
                        :action-href="route('policies.create')"
                    >
                        <template #icon>
                            <CubeTransparentIcon class="h-7 w-7 text-slate-400" />
                        </template>
                    </PaEmptyState>
                </PaCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
