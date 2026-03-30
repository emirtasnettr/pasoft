<script setup>
import { URGENCY_LABELS, daysLeftLabel, formatMoneyTr } from '@/lib/policyLabels';
import { digitsPhone, waLink } from '@/lib/leadLabels';
import { Link, router } from '@inertiajs/vue3';
import {
    ArrowPathIcon,
    ChatBubbleBottomCenterTextIcon,
    DevicePhoneMobileIcon,
    DocumentTextIcon,
    PhoneIcon,
} from '@heroicons/vue/24/outline';

defineProps({
    rows: { type: Array, default: () => [] },
});

const emit = defineEmits(['open', 'quick-note']);

function rowIsBold(row) {
    return row.urgency === 'critical_1' || row.urgency === 'expired';
}

function startRenewal(id) {
    router.post(
        route('policies.renewal.start', id),
        {},
        {
            preserveScroll: true,
            only: ['policies', 'policyDetail', 'kpi', 'criticalAlerts', 'flash'],
        },
    );
}
</script>

<template>
    <div
        class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800">
                <thead class="bg-slate-50/90 dark:bg-slate-800/40">
                    <tr>
                        <th
                            class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                        >
                            Müşteri / No
                        </th>
                        <th
                            class="hidden px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 md:table-cell dark:text-slate-400"
                        >
                            Tür / Şirket
                        </th>
                        <th
                            class="hidden px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 lg:table-cell dark:text-slate-400"
                        >
                            Tarihler
                        </th>
                        <th
                            class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                        >
                            Kalan
                        </th>
                        <th
                            class="hidden px-4 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 xl:table-cell dark:text-slate-400"
                        >
                            Prim / Kom.
                        </th>
                        <th
                            class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                        >
                            Yenileme
                        </th>
                        <th
                            class="px-4 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                        >
                            İşlem
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr
                        v-for="p in rows"
                        :key="p.id"
                        class="transition-colors duration-200 hover:bg-slate-50/90 dark:hover:bg-slate-800/50"
                        :class="[
                            p.urgency_row_class,
                            rowIsBold(p) ? 'font-semibold' : '',
                        ]"
                    >
                        <td class="cursor-pointer px-4 py-4 align-top" @click="emit('open', p.id)">
                            <Link
                                v-if="p.customer"
                                :href="route('customers.index', { customer: p.customer.id })"
                                class="font-semibold text-indigo-600 hover:underline dark:text-indigo-400"
                                @click.stop
                            >
                                {{ p.customer.display_name }}
                            </Link>
                            <p v-else class="font-medium text-slate-500">—</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                {{ p.policy_number }}
                            </p>
                            <p class="mt-1 text-xs font-medium tabular-nums text-slate-600 xl:hidden dark:text-slate-300">
                                {{ formatMoneyTr(p.premium_amount) }} · Kom.
                                {{ formatMoneyTr(p.commission_amount) }}
                            </p>
                            <div class="mt-2 flex flex-wrap gap-1">
                                <span
                                    v-for="ins in (p.insight_badges || []).slice(0, 2)"
                                    :key="ins.text"
                                    class="inline-flex max-w-full truncate rounded-lg px-2 py-0.5 text-[10px] font-semibold"
                                    :class="
                                        ins.type === 'danger'
                                            ? 'bg-red-100 text-red-800 dark:bg-red-950/40 dark:text-red-200'
                                            : ins.type === 'warning'
                                              ? 'bg-amber-100 text-amber-900 dark:bg-amber-950/40 dark:text-amber-200'
                                              : ins.type === 'finance'
                                                ? 'bg-violet-100 text-violet-800 dark:bg-violet-950/40 dark:text-violet-200'
                                                : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
                                    "
                                >
                                    {{ ins.text }}
                                </span>
                            </div>
                        </td>
                        <td
                            class="hidden cursor-pointer px-4 py-4 align-top md:table-cell"
                            @click="emit('open', p.id)"
                        >
                            <span
                                class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-800 dark:bg-slate-800 dark:text-slate-200"
                            >
                                {{ p.policy_type?.name ?? '—' }}
                            </span>
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                                {{ p.insurance_company?.name ?? '—' }}
                            </p>
                        </td>
                        <td
                            class="hidden cursor-pointer px-4 py-4 align-top text-sm text-slate-600 lg:table-cell dark:text-slate-300"
                            @click="emit('open', p.id)"
                        >
                            <p>Başlangıç: {{ p.starts_at ?? '—' }}</p>
                            <p class="mt-1">Bitiş: {{ p.ends_at ?? '—' }}</p>
                        </td>
                        <td class="cursor-pointer px-4 py-4 align-top" @click="emit('open', p.id)">
                            <span
                                class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold"
                                :class="p.urgency_badge_class"
                            >
                                {{ URGENCY_LABELS[p.urgency] ?? p.urgency }}
                            </span>
                            <p class="mt-1 text-sm tabular-nums text-slate-700 dark:text-slate-200">
                                {{ daysLeftLabel(p) }}
                            </p>
                        </td>
                        <td
                            class="hidden cursor-pointer px-4 py-4 align-top text-right xl:table-cell"
                            @click="emit('open', p.id)"
                        >
                            <p class="font-semibold tabular-nums text-slate-900 dark:text-white">
                                {{ formatMoneyTr(p.premium_amount) }}
                            </p>
                            <p class="mt-1 text-sm tabular-nums text-violet-700 dark:text-violet-300">
                                {{ formatMoneyTr(p.commission_amount) }}
                            </p>
                        </td>
                        <td class="cursor-pointer px-4 py-4 align-top" @click="emit('open', p.id)">
                            <span
                                class="inline-flex rounded-lg bg-slate-100 px-2 py-1 text-xs font-bold text-slate-800 dark:bg-slate-800 dark:text-slate-200"
                            >
                                {{ p.renewal_label_tr }}
                            </span>
                        </td>
                        <td class="px-4 py-4 align-top text-right" @click.stop>
                            <div class="flex flex-wrap items-center justify-end gap-1">
                                <button
                                    type="button"
                                    class="inline-flex rounded-xl border border-slate-200 p-2 text-slate-600 transition hover:scale-105 hover:border-amber-300 hover:text-amber-700 dark:border-slate-600"
                                    title="Yenileme başlat"
                                    @click="startRenewal(p.id)"
                                >
                                    <ArrowPathIcon class="h-4 w-4" />
                                </button>
                                <a
                                    v-if="p.customer?.phone"
                                    :href="'tel:' + digitsPhone(p.customer.phone)"
                                    class="inline-flex rounded-xl border border-slate-200 p-2 text-slate-600 hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-600"
                                    title="Ara"
                                >
                                    <PhoneIcon class="h-4 w-4" />
                                </a>
                                <a
                                    v-if="p.customer?.phone"
                                    :href="waLink(p.customer.phone)"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex rounded-xl border border-slate-200 p-2 text-slate-600 hover:border-emerald-300 hover:text-emerald-600 dark:border-slate-600"
                                    title="WhatsApp"
                                >
                                    <DevicePhoneMobileIcon class="h-4 w-4" />
                                </a>
                                <button
                                    type="button"
                                    class="inline-flex rounded-xl border border-slate-200 p-2 text-slate-600 hover:border-indigo-300 dark:border-slate-600"
                                    title="Not ekle"
                                    @click="emit('quick-note', p)"
                                >
                                    <ChatBubbleBottomCenterTextIcon class="h-4 w-4" />
                                </button>
                                <a
                                    v-if="p.pdf_url"
                                    :href="p.pdf_url"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex rounded-xl border border-slate-200 p-2 text-slate-600 hover:border-slate-400 dark:border-slate-600"
                                    title="PDF"
                                >
                                    <DocumentTextIcon class="h-4 w-4" />
                                </a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div
                v-if="!rows?.length"
                class="px-6 py-16 text-center text-sm text-slate-500 dark:text-slate-400"
            >
                Kayıt bulunamadı.
            </div>
        </div>
    </div>
</template>
