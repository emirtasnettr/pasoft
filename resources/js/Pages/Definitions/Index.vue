<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BuildingLibraryIcon, RectangleStackIcon } from '@heroicons/vue/24/outline';

defineProps({
    companyCount: Number,
    policyTypeCount: Number,
    activeCompanies: Number,
    activePolicyTypes: Number,
});
</script>

<template>
    <Head title="Tanımlar" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Tanımlar merkezi</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Sigorta firmaları, poliçe türleri ve otomasyon için temel konfigürasyon.
                </p>
            </div>
        </template>

        <div class="mx-auto max-w-4xl space-y-8">
            <div class="grid gap-4 sm:grid-cols-2">
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kayıtlar</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ companyCount }}</p>
                    <p class="text-sm text-slate-500">toplam firma · {{ activeCompanies }} aktif</p>
                </div>
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Türler</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ policyTypeCount }}</p>
                    <p class="text-sm text-slate-500">poliçe türü · {{ activePolicyTypes }} aktif</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <Link
                    :href="route('insurance-companies.index')"
                    class="group flex flex-col rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition hover:border-indigo-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-500/40"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-300"
                    >
                        <BuildingLibraryIcon class="h-7 w-7" />
                    </div>
                    <h2 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">
                        Sigorta firmaları
                    </h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        Logo, iletişim, poliçe türü bazlı komisyon eşleşmeleri ve entegrasyon bayrakları.
                    </p>
                    <span
                        class="mt-4 text-sm font-semibold text-indigo-600 group-hover:underline dark:text-indigo-400"
                    >
                        Listeye git →
                    </span>
                </Link>

                <Link
                    :href="route('policy-types.index')"
                    class="group flex flex-col rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition hover:border-violet-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-violet-500/40"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-950/50 dark:text-violet-300"
                    >
                        <RectangleStackIcon class="h-7 w-7" />
                    </div>
                    <h2 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">Poliçe türleri</h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        Renk, ikon, açıklama, firma bazlı komisyonlar ve yenileme hatırlatma süresi.
                    </p>
                    <span
                        class="mt-4 text-sm font-semibold text-violet-600 group-hover:underline dark:text-violet-400"
                    >
                        Listeye git →
                    </span>
                </Link>
            </div>

            <p class="text-center text-xs text-slate-500 dark:text-slate-400">
                Poliçe oluştururken firma ve tür seçildiğinde komisyon ile yenileme günü tanımlardan otomatik önerilir.
            </p>
        </div>
    </AuthenticatedLayout>
</template>
