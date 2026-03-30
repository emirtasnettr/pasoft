<script setup>
import PaPagination from '@/Components/PaPagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { MagnifyingGlassIcon, PencilSquareIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { reactive, watch } from 'vue';

const props = defineProps({
    users: Object,
    filters: Object,
});

const filterForm = reactive({
    search: props.filters.search ?? '',
});

let searchTimer;
watch(
    () => filterForm.search,
    () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            router.get(
                route('admin.users.index'),
                { search: filterForm.search || undefined },
                { preserveState: true, replace: true, only: ['users', 'filters'] },
            );
        }, 300);
    },
);

function destroyUser(row) {
    if (!confirm(`“${row.name}” kullanıcısını silmek istiyor musunuz?`)) {
        return;
    }
    router.delete(route('admin.users.destroy', row.id));
}
</script>

<template>
    <Head title="Kullanıcılar" />

    <AdminLayout>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">Kullanıcılar</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Rol atama, şifre sıfırlama ve hesap durumu.
                </p>
            </div>
            <Link :href="route('admin.users.create')">
                <PrimaryButton class="inline-flex items-center gap-2 rounded-xl">
                    <PlusIcon class="h-4 w-4" />
                    Yeni kullanıcı
                </PrimaryButton>
            </Link>
        </div>

        <div class="relative mt-6 max-w-md">
            <MagnifyingGlassIcon
                class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
            />
            <input
                v-model="filterForm.search"
                type="search"
                placeholder="Ad veya e-posta ara..."
                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            />
        </div>

        <div
            class="mt-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800">
                <thead class="bg-slate-50/80 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Kullanıcı</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Rol</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Durum</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-if="!users.data?.length">
                        <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">Kayıt yok.</td>
                    </tr>
                    <tr v-for="row in users.data" :key="row.id">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-900 dark:text-white">{{ row.name }}</p>
                            <p class="text-xs text-slate-500">{{ row.email }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ row.role?.name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                :class="
                                    row.is_active
                                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300'
                                        : 'bg-slate-100 text-slate-600 dark:bg-slate-700/40'
                                "
                            >
                                {{ row.is_active ? 'Aktif' : 'Pasif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <Link
                                    :href="route('admin.users.edit', row.id)"
                                    class="inline-flex rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-indigo-600 dark:hover:bg-slate-800"
                                >
                                    <PencilSquareIcon class="h-5 w-5" />
                                </Link>
                                <button
                                    type="button"
                                    class="inline-flex rounded-lg p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/50"
                                    @click="destroyUser(row)"
                                >
                                    <TrashIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="border-t border-slate-100 px-4 py-3 dark:border-slate-800">
                <PaPagination :links="users.links" />
            </div>
        </div>
    </AdminLayout>
</template>
