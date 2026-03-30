<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { formatClaimMoney } from '@/lib/claimLabels';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    claim: Object,
    statusOptions: Array,
});

const statusLabel = computed(() => {
    const o = props.statusOptions?.find((x) => x.value === props.claim.status);
    return o?.label ?? props.claim.status;
});
</script>

<template>
    <Head :title="claim.file_number" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-lg font-semibold text-slate-900 dark:text-white">
                            {{ claim.file_number }}
                        </h1>
                        <span
                            class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-slate-500/15 dark:ring-slate-500/30"
                        >
                            {{ statusLabel }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ claim.customer?.name ?? claim.customer?.company_name ?? '—' }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link :href="route('claims.index', { claim: claim.id })">
                        <SecondaryButton class="rounded-xl border-slate-200 dark:border-slate-600">
                            Panelde aç
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('claims.edit', claim.id)">
                        <PrimaryButton class="rounded-xl">Düzenle</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-3xl space-y-6">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <dl class="grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-slate-500">Hasar türü</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">
                            {{ claim.claim_type ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Hasar tutarı</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">
                            {{ formatClaimMoney(claim.amount) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Ödenen</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">
                            {{ formatClaimMoney(claim.paid_amount) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Şirket</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">
                            {{ claim.insurance_company?.name ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Sorumlu</dt>
                        <dd>{{ claim.handler?.name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Poliçe</dt>
                        <dd>{{ claim.policy?.policy_number ?? '—' }}</dd>
                    </div>
                </dl>
                <div v-if="claim.customer_notice_notes" class="mt-6">
                    <h2 class="text-sm font-semibold">Müşteri notu</h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                        {{ claim.customer_notice_notes }}
                    </p>
                </div>
                <div v-if="claim.internal_notes" class="mt-6">
                    <h2 class="text-sm font-semibold">İç not</h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                        {{ claim.internal_notes }}
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
