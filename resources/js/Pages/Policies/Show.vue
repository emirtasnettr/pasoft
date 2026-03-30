<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowDownTrayIcon, PencilSquareIcon } from '@heroicons/vue/24/outline';

defineProps({
    policy: Object,
});
</script>

<template>
    <Head :title="policy.policy_number" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-lg font-semibold text-slate-900 dark:text-white">
                        {{ policy.policy_number }}
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ policy.policy_type?.name }} · {{ policy.insurance_company?.name }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a
                        v-if="policy.pdf_path"
                        :href="`/storage/${policy.pdf_path}`"
                        target="_blank"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        <ArrowDownTrayIcon class="h-4 w-4" />
                        PDF
                    </a>
                    <Link
                        :href="route('policies.edit', policy.id)"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    >
                        <PencilSquareIcon class="h-4 w-4" />
                        Düzenle
                    </Link>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-5xl space-y-6">
            <div class="grid gap-6 lg:grid-cols-3">
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:col-span-2"
                >
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Özet</h2>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                        <div>
                            <dt class="text-slate-500">Müşteri</dt>
                            <dd class="font-medium text-slate-900 dark:text-white">
                                {{ policy.customer?.company_name || policy.customer?.name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Tarih aralığı</dt>
                            <dd class="font-medium text-slate-900 dark:text-white">
                                {{ policy.starts_at }} → {{ policy.ends_at }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Prim</dt>
                            <dd class="font-semibold text-indigo-600 dark:text-indigo-400">
                                ₺{{ Number(policy.premium_amount).toLocaleString('tr-TR') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Komisyon / Tahsil</dt>
                            <dd class="font-medium text-slate-900 dark:text-white">
                                ₺{{ Number(policy.commission_amount).toLocaleString('tr-TR') }} /
                                ₺{{ Number(policy.commission_collected).toLocaleString('tr-TR') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Prim tahsilat</dt>
                            <dd>{{ policy.premium_payment_status }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Yenileme</dt>
                            <dd>{{ policy.renewal_status }}</dd>
                        </div>
                    </dl>
                    <div v-if="policy.coverage_details" class="mt-6">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">
                            Teminat detayları
                        </h3>
                        <p class="mt-2 whitespace-pre-wrap text-sm text-slate-600 dark:text-slate-300">
                            {{ policy.coverage_details }}
                        </p>
                    </div>
                </div>
                <div
                    class="space-y-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Taksitler</h2>
                    <ul class="space-y-2 text-sm">
                        <li
                            v-for="i in policy.installments"
                            :key="i.id"
                            class="flex justify-between rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60"
                        >
                            <span>#{{ i.sequence }} · {{ i.due_date }}</span>
                            <span
                                :class="
                                    i.status === 'paid'
                                        ? 'text-emerald-600'
                                        : 'text-amber-600'
                                "
                            >
                                ₺{{ Number(i.amount).toLocaleString('tr-TR') }} · {{ i.status }}
                            </span>
                        </li>
                        <li v-if="!policy.installments?.length" class="text-slate-500">
                            Taksit kaydı yok — API veya seeder ile eklenebilir.
                        </li>
                    </ul>
                    <h2 class="pt-4 text-sm font-semibold text-slate-900 dark:text-white">
                        Son tahsilatlar
                    </h2>
                    <ul class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                        <li
                            v-for="p in policy.payments"
                            :key="p.id"
                            class="flex justify-between border-b border-slate-100 pb-2 dark:border-slate-800"
                        >
                            <span>{{ p.paid_at?.slice?.(0, 10) }}</span>
                            <span>₺{{ Number(p.amount).toLocaleString('tr-TR') }} ({{ p.method }})</span>
                        </li>
                        <li v-if="!policy.payments?.length" class="text-slate-500">Kayıt yok.</li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
