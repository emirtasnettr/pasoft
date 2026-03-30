<script setup>
import { formatClaimDateTime, formatClaimMoney, insightBadgeClass } from '@/lib/claimLabels';
import { router } from '@inertiajs/vue3';
import {
    ArrowPathIcon,
    DocumentPlusIcon,
    PencilSquareIcon,
    PhoneIcon,
} from '@heroicons/vue/24/outline';

defineProps({
    rows: { type: Array, required: true },
    statusOptions: { type: Array, required: true },
});

const emit = defineEmits(['open']);

function openDrawer(id) {
    emit('open', id);
}

function patchStatus(claimId, status) {
    router.patch(
        route('claims.status', claimId),
        { status },
        {
            preserveScroll: true,
            only: ['claims', 'claimDetail', 'kpi'],
        },
    );
}
</script>

<template>
    <div
        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800">
                <thead class="bg-slate-50/80 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Dosya
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Müşteri
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Poliçe
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Şirket
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Tür / Tutar
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Durum
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Güncelleme
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Hızlı işlem
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-if="!rows.length">
                        <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                            Kayıt bulunamadı. Filtreleri değiştirin veya yeni hasar oluşturun.
                        </td>
                    </tr>
                    <tr
                        v-for="c in rows"
                        :key="c.id"
                        class="cursor-pointer transition-colors hover:bg-slate-50/90 dark:hover:bg-slate-800/50"
                        @click="openDrawer(c.id)"
                    >
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="font-semibold text-slate-900 dark:text-white">{{ c.file_number }}</span>
                            <div v-if="c.insight_badges?.length" class="mt-1 flex flex-wrap gap-1">
                                <span
                                    v-for="(b, i) in c.insight_badges"
                                    :key="i"
                                    class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-semibold ring-1 ring-inset"
                                    :class="insightBadgeClass(b.type)"
                                >
                                    {{ b.text }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                            {{ c.customer?.display_name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                            {{ c.policy?.policy_number ?? '—' }}
                        </td>
                        <td class="max-w-[140px] truncate px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                            {{ c.insurance_company?.name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="text-slate-700 dark:text-slate-200">
                                {{ c.claim_type || '—' }}
                            </div>
                            <div class="mt-0.5 font-medium tabular-nums text-slate-900 dark:text-white">
                                {{ formatClaimMoney(c.amount) }}
                            </div>
                        </td>
                        <td class="px-4 py-3" @click.stop>
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset"
                                :class="c.status_badge_class"
                            >
                                {{ c.status_label }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500 dark:text-slate-400">
                            {{ formatClaimDateTime(c.last_activity_at || c.updated_at) }}
                        </td>
                        <td class="px-4 py-3 text-right" @click.stop>
                            <div class="flex flex-wrap items-center justify-end gap-1">
                                <a
                                    v-if="c.customer?.phone"
                                    :href="'tel:' + c.customer.phone"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-emerald-950/40 dark:hover:text-emerald-300"
                                    title="Ara"
                                    @click.stop
                                >
                                    <PhoneIcon class="h-4 w-4" />
                                </a>
                                <button
                                    type="button"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-slate-800 dark:hover:text-white"
                                    title="Not ekle"
                                    @click.stop="openDrawer(c.id)"
                                >
                                    <PencilSquareIcon class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-slate-800 dark:hover:text-white"
                                    title="Evrak yükle"
                                    @click.stop="openDrawer(c.id)"
                                >
                                    <DocumentPlusIcon class="h-4 w-4" />
                                </button>
                                <div class="relative inline-flex items-center">
                                    <select
                                        class="h-8 max-w-[7.5rem] cursor-pointer appearance-none rounded-lg border border-slate-200 bg-white py-0 pl-2 pr-7 text-xs font-medium text-slate-700 shadow-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                                        :value="c.status"
                                        title="Durum değiştir"
                                        @change="patchStatus(c.id, $event.target.value)"
                                    >
                                        <option
                                            v-for="opt in statusOptions"
                                            :key="opt.value"
                                            :value="opt.value"
                                        >
                                            {{ opt.label }}
                                        </option>
                                    </select>
                                    <ArrowPathIcon
                                        class="pointer-events-none absolute right-1.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                                    />
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
