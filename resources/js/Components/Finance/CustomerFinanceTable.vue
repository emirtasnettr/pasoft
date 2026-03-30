<script setup>
import { formatMoneyTr, overdueAlertClass, whatsappHref } from '@/lib/financeLabels';
import { ChatBubbleLeftRightIcon, PhoneIcon } from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';

defineProps({
    rows: { type: Array, required: true },
});

const emit = defineEmits(['open']);
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
                            Müşteri
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Toplam borç
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Ödenen
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Kalan
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Uyarı
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            İşlem
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-if="!rows.length">
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                            Kriterlere uygun müşteri yok. Filtreleri değiştirin.
                        </td>
                    </tr>
                    <tr
                        v-for="r in rows"
                        :key="r.id"
                        class="cursor-pointer transition-colors hover:bg-slate-50/90 dark:hover:bg-slate-800/50"
                        @click="emit('open', r.id)"
                    >
                        <td class="px-4 py-3">
                            <span class="font-semibold text-slate-900 dark:text-white">{{ r.display_name }}</span>
                            <p v-if="r.email" class="text-xs text-slate-500 dark:text-slate-400">
                                {{ r.email }}
                            </p>
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-medium tabular-nums text-slate-800 dark:text-slate-200">
                            {{ formatMoneyTr(r.total_debt) }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-emerald-700 dark:text-emerald-300">
                            {{ formatMoneyTr(r.paid) }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums text-slate-900 dark:text-white">
                            {{ formatMoneyTr(r.remaining) }}
                        </td>
                        <td class="px-4 py-3" @click.stop>
                            <div v-if="r.row_alerts?.length" class="flex flex-wrap gap-1">
                                <span
                                    v-for="(a, i) in r.row_alerts"
                                    :key="i"
                                    class="inline-flex rounded-md px-2 py-0.5 text-[10px] font-bold"
                                    :class="overdueAlertClass(a.severity)"
                                >
                                    {{ a.text }}
                                </span>
                            </div>
                            <span v-else class="text-xs text-slate-400">—</span>
                        </td>
                        <td class="px-4 py-3 text-right" @click.stop>
                            <div class="flex flex-wrap items-center justify-end gap-1">
                                <Link
                                    :href="route('payments.create', { customer_id: r.id })"
                                    class="inline-flex h-8 items-center rounded-lg bg-indigo-600 px-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                                >
                                    Tahsilat
                                </Link>
                                <a
                                    v-if="r.phone"
                                    :href="'tel:' + r.phone"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-emerald-950/40"
                                    title="Ara"
                                >
                                    <PhoneIcon class="h-4 w-4" />
                                </a>
                                <a
                                    v-if="whatsappHref(r.phone)"
                                    :href="whatsappHref(r.phone)"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-green-50 hover:text-green-700 dark:hover:bg-green-950/40"
                                    title="WhatsApp"
                                >
                                    <ChatBubbleLeftRightIcon class="h-4 w-4" />
                                </a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
