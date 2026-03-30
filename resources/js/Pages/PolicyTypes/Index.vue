<script setup>
import PolicyTypeDetailDrawer from '@/Components/Definitions/PolicyTypeDetailDrawer.vue';
import PaPagination from '@/Components/PaPagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { policyTypeOutlineIcon } from '@/lib/policyTypeIcons';
import { Head, Link, router } from '@inertiajs/vue3';
import { MagnifyingGlassIcon, PencilSquareIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { reactive, watch } from 'vue';

const props = defineProps({
    policyTypes: Object,
    typeDetail: { type: Object, default: null },
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
        route('policy-types.index'),
        {
            search: filterForm.search || undefined,
            status: filterForm.status || undefined,
            type: props.filters.type || undefined,
            ...extra,
        },
        { preserveState: true, replace: true, only: ['policyTypes', 'filters', 'typeDetail'] },
    );
}

function setStatus(s) {
    filterForm.status = filterForm.status === s ? '' : s;
    applyListFilters();
}

function openType(id) {
    router.get(
        route('policy-types.index'),
        {
            search: filterForm.search || undefined,
            status: filterForm.status || undefined,
            type: id,
        },
        { preserveState: true, replace: true, only: ['typeDetail'] },
    );
}

function rowIcon(icon) {
    return policyTypeOutlineIcon(icon || 'rectangle-stack');
}

function removeType(row) {
    if (row.policies_count > 0) {
        alert('Bu türe bağlı poliçeler var; silinemez. Pasifleştirmek için düzenleyin.');
        return;
    }
    if (!confirm(`“${row.name}” türünü silmek istiyor musunuz?`)) {
        return;
    }
    router.delete(route('policy-types.destroy', row.id));
}
</script>

<template>
    <Head title="Poliçe türleri" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-violet-600 dark:text-violet-400">
                        Tanımlar
                    </p>
                    <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Poliçe türleri</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Renk, ikon, yenileme ve firma başına komisyon eşleşmeleri.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="route('definitions.index')"
                        class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Tanımlar merkezi
                    </Link>
                    <Link :href="route('policy-types.create')">
                        <PrimaryButton class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5">
                            <PlusIcon class="h-4 w-4" />
                            Yeni tür
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
                        placeholder="Ad, slug, kategori ara..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm focus:border-violet-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-violet-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                </div>
                <div class="flex flex-wrap gap-2">
                    <SecondaryButton
                        type="button"
                        class="rounded-xl"
                        :class="
                            filterForm.status === 'active'
                                ? 'border-violet-300 bg-violet-50 text-violet-900 dark:border-violet-500/40 dark:bg-violet-950/40'
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
                                Tür
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Renk</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">İkon</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                                Poliçe
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Durum</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                                İşlem
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-if="!policyTypes.data?.length">
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500">Kayıt yok.</td>
                        </tr>
                        <tr
                            v-for="row in policyTypes.data"
                            :key="row.id"
                            class="cursor-pointer transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                            @click="openType(row.id)"
                        >
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ring-1 ring-slate-200 dark:ring-slate-600"
                                        :style="{
                                            backgroundColor: (row.color || '#6366f1') + '2a',
                                            color: row.color || '#6366f1',
                                        }"
                                    >
                                        <component :is="rowIcon(row.icon)" class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <span class="font-semibold text-slate-900 dark:text-white">{{
                                            row.name
                                        }}</span>
                                        <p class="font-mono text-xs text-slate-500">{{ row.slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-block h-6 w-6 rounded-md ring-1 ring-slate-200 dark:ring-slate-600"
                                        :style="{ backgroundColor: row.color }"
                                    />
                                    <span class="font-mono text-xs text-slate-600 dark:text-slate-400">{{
                                        row.color
                                    }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-300">
                                {{ row.icon }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-slate-600 dark:text-slate-300">
                                {{ row.policies_count }}
                            </td>
                            <td class="px-4 py-3" @click.stop>
                                <span
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    :class="
                                        row.is_active
                                            ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300'
                                            : 'bg-slate-100 text-slate-600 dark:bg-slate-700/40 dark:text-slate-300'
                                    "
                                >
                                    {{ row.is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right" @click.stop>
                                <div class="flex justify-end gap-2">
                                    <Link
                                        :href="route('policy-types.edit', row.id)"
                                        class="inline-flex rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-indigo-600 dark:hover:bg-slate-800 dark:hover:text-indigo-400"
                                    >
                                        <PencilSquareIcon class="h-5 w-5" />
                                    </Link>
                                    <button
                                        type="button"
                                        class="inline-flex rounded-lg p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-rose-950/50 dark:hover:text-rose-400"
                                        :disabled="row.policies_count > 0"
                                        @click="removeType(row)"
                                    >
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="border-t border-slate-100 px-6 py-4 dark:border-slate-800">
                    <PaPagination :links="policyTypes.links" />
                </div>
            </div>
        </div>

        <PolicyTypeDetailDrawer :type-detail="typeDetail" :filters="filters" />
    </AuthenticatedLayout>
</template>
