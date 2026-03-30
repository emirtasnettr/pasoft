<script setup>
import CompanyDetailDrawer from '@/Components/Definitions/CompanyDetailDrawer.vue';
import PaPagination from '@/Components/PaPagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { MagnifyingGlassIcon, PencilSquareIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { reactive, watch } from 'vue';

const props = defineProps({
    companies: Object,
    companyDetail: { type: Object, default: null },
    filters: Object,
});

const filterForm = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
});

watch(
    () => props.filters,
    (f) => {
        filterForm.search = f.search ?? '';
        filterForm.status = f.status ?? '';
    },
    { deep: true },
);

let searchTimer;
watch(
    () => filterForm.search,
    () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(applyListFilters, 300);
    },
);

function applyListFilters(extra = {}) {
    router.get(
        route('insurance-companies.index'),
        {
            search: filterForm.search || undefined,
            status: filterForm.status || undefined,
            company: props.filters.company || undefined,
            ...extra,
        },
        { preserveState: true, replace: true, only: ['companies', 'filters', 'companyDetail'] },
    );
}

function setStatus(s) {
    filterForm.status = filterForm.status === s ? '' : s;
    applyListFilters();
}

function openCompany(id) {
    router.get(
        route('insurance-companies.index'),
        {
            search: filterForm.search || undefined,
            status: filterForm.status || undefined,
            company: id,
        },
        { preserveState: true, replace: true, only: ['companyDetail'] },
    );
}

function removeCompany(id, name) {
    if (!confirm(`“${name}” firmasını kaldırmak istiyor musunuz? (Arşivlenir.)`)) {
        return;
    }
    router.delete(route('insurance-companies.destroy', id));
}
</script>

<template>
    <Head title="Sigorta firmaları" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                        Tanımlar
                    </p>
                    <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Sigorta firmaları</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Liste, logo ve detay çekmecesi ile komisyon yapılandırması.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="route('definitions.index')"
                        class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Tanımlar merkezi
                    </Link>
                    <Link :href="route('insurance-companies.create')">
                        <PrimaryButton class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5">
                            <PlusIcon class="h-4 w-4" />
                            Yeni firma
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-6xl space-y-6">
            <div
                class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:flex-row lg:items-center lg:justify-between"
            >
                <div class="relative max-w-md flex-1">
                    <MagnifyingGlassIcon
                        class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        v-model="filterForm.search"
                        type="search"
                        placeholder="Firma, kod, e-posta ara..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                </div>
                <div class="flex flex-wrap gap-2">
                    <SecondaryButton
                        type="button"
                        class="rounded-xl"
                        :class="
                            filterForm.status === 'active'
                                ? 'border-indigo-300 bg-indigo-50 text-indigo-900 dark:border-indigo-500/40 dark:bg-indigo-950/40'
                                : ''
                        "
                        @click="setStatus('active')"
                    >
                        Aktif
                    </SecondaryButton>
                    <SecondaryButton
                        type="button"
                        class="rounded-xl"
                        :class="
                            filterForm.status === 'inactive'
                                ? 'border-slate-400 bg-slate-100 dark:border-slate-500 dark:bg-slate-800'
                                : ''
                        "
                        @click="setStatus('inactive')"
                    >
                        Pasif
                    </SecondaryButton>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800">
                    <thead class="bg-slate-50/80 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                Logo / Firma
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Kod</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                İletişim
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Durum</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                                İşlem
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-if="!companies.data?.length">
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">
                                Kayıt yok.
                            </td>
                        </tr>
                        <tr
                            v-for="c in companies.data"
                            :key="c.id"
                            class="cursor-pointer transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                            @click="openCompany(c.id)"
                        >
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800"
                                    >
                                        <img
                                            v-if="c.logo_url"
                                            :src="c.logo_url"
                                            alt=""
                                            class="h-full w-full object-contain p-0.5"
                                        />
                                        <span v-else class="text-sm font-bold text-slate-400">
                                            {{ c.name?.slice(0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-slate-900 dark:text-white">{{ c.name }}</span>
                                        <p class="text-xs text-slate-500">{{ c.policies_count }} poliçe</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-sm text-slate-600 dark:text-slate-300">
                                {{ c.code || '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                <span class="block">{{ c.contact_phone || '—' }}</span>
                                <span class="text-xs text-slate-500">{{ c.contact_email || '' }}</span>
                            </td>
                            <td class="px-4 py-3" @click.stop>
                                <span
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    :class="
                                        c.is_active
                                            ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300'
                                            : 'bg-slate-100 text-slate-600 dark:bg-slate-700/40 dark:text-slate-300'
                                    "
                                >
                                    {{ c.is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right" @click.stop>
                                <div class="flex justify-end gap-2">
                                    <Link
                                        :href="route('insurance-companies.edit', c.id)"
                                        class="inline-flex rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-indigo-600 dark:hover:bg-slate-800 dark:hover:text-indigo-400"
                                    >
                                        <PencilSquareIcon class="h-5 w-5" />
                                    </Link>
                                    <button
                                        type="button"
                                        class="inline-flex rounded-lg p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/50 dark:hover:text-rose-400"
                                        @click="removeCompany(c.id, c.name)"
                                    >
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="border-t border-slate-100 px-6 py-4 dark:border-slate-800">
                    <PaPagination :links="companies.links" />
                </div>
            </div>
        </div>

        <CompanyDetailDrawer :company-detail="companyDetail" :filters="filters" />
    </AuthenticatedLayout>
</template>
