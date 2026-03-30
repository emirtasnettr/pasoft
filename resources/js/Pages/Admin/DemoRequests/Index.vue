<script setup>
import PaPagination from '@/Components/PaPagination.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    demoRequests: {
        type: Object,
        required: true,
    },
});

function formatDate(iso) {
    if (!iso) {
        return '—';
    }
    try {
        return new Intl.DateTimeFormat('tr-TR', {
            dateStyle: 'short',
            timeStyle: 'short',
        }).format(new Date(iso));
    } catch {
        return iso;
    }
}
</script>

<template>
    <Head title="Demo talepleri" />

    <AdminLayout>
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Demo talepleri</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Ana sayfadaki formdan gelen talepler yalnızca sistem yöneticisi tarafından görüntülenir.
            </p>
        </div>

        <div
            class="mt-8 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800">
                <thead class="bg-slate-50/80 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Şirket</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Yetkili</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">E-posta</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Telefon</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Tarih</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Mesaj</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="row in demoRequests.data" :key="row.id">
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-900 dark:text-white">
                            {{ row.company_name }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">
                            {{ row.contact_name }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <a
                                :href="`mailto:${row.email}`"
                                class="text-indigo-600 underline decoration-indigo-600/30 hover:text-indigo-500 dark:text-indigo-400"
                            >
                                {{ row.email }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                            {{ row.phone || '—' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                            {{ formatDate(row.created_at) }}
                        </td>
                        <td class="max-w-xs px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                            <span class="line-clamp-3" :title="row.message || ''">
                                {{ row.message?.trim() ? row.message : '—' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div
                v-if="!demoRequests.data?.length"
                class="border-t border-slate-100 px-4 py-12 text-center text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400"
            >
                Henüz demo talebi yok.
            </div>

            <div v-if="demoRequests.links?.length > 3" class="border-t border-slate-100 px-4 py-3 dark:border-slate-800">
                <PaPagination :links="demoRequests.links" />
            </div>
        </div>
    </AdminLayout>
</template>
