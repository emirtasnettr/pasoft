<script setup>
import {
    SEGMENT_LABELS,
    formatMoneyTr,
    segmentBadgeClass,
} from '@/lib/customerLabels';
import { digitsPhone, waLink } from '@/lib/leadLabels';
import { Link } from '@inertiajs/vue3';
import {
    ChatBubbleBottomCenterTextIcon,
    DevicePhoneMobileIcon,
    PhoneIcon,
    PlusIcon,
} from '@heroicons/vue/24/outline';

defineProps({
    rows: { type: Array, default: () => [] },
});

const emit = defineEmits(['open', 'quick-note']);

function displayName(c) {
    return c.company_name || c.name || '—';
}

function formatWhen(iso) {
    if (!iso) {
        return '—';
    }
    return String(iso).replace('T', ' ').slice(0, 16);
}

function initials(name) {
    if (!name) {
        return '?';
    }
    const p = name.trim().split(/\s+/);
    if (p.length === 1) {
        return p[0].slice(0, 2).toUpperCase();
    }
    return (p[0][0] + p[p.length - 1][0]).toUpperCase();
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
                            class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                        >
                            Müşteri
                        </th>
                        <th
                            class="hidden px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 lg:table-cell dark:text-slate-400"
                        >
                            İletişim
                        </th>
                        <th
                            class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                        >
                            Segment
                        </th>
                        <th
                            class="hidden px-5 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 md:table-cell dark:text-slate-400"
                        >
                            Poliçe
                        </th>
                        <th
                            class="hidden px-5 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 lg:table-cell dark:text-slate-400"
                        >
                            Ciro (prim)
                        </th>
                        <th
                            class="hidden px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 xl:table-cell dark:text-slate-400"
                        >
                            Son işlem
                        </th>
                        <th
                            class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                        >
                            Temsilci
                        </th>
                        <th
                            class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                        >
                            Hızlı işlem
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr
                        v-for="c in rows"
                        :key="c.id"
                        class="transition-colors duration-200 hover:bg-slate-50/90 dark:hover:bg-slate-800/50"
                    >
                        <td class="cursor-pointer px-5 py-5 align-top" @click="emit('open', c.id)">
                            <p class="font-semibold text-slate-900 dark:text-white">
                                {{ displayName(c) }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ c.type === 'corporate' ? 'Kurumsal' : 'Bireysel' }}
                            </p>
                        </td>
                        <td
                            class="hidden cursor-pointer px-5 py-5 align-top lg:table-cell"
                            @click="emit('open', c.id)"
                        >
                            <p class="text-sm">
                                <a
                                    v-if="c.phone"
                                    :href="'tel:' + digitsPhone(c.phone)"
                                    class="font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                                    @click.stop
                                >
                                    {{ c.phone }}
                                </a>
                                <span v-else class="text-slate-400">—</span>
                            </p>
                            <p class="mt-1 truncate text-xs text-slate-500">
                                {{ c.email || '—' }}
                            </p>
                        </td>
                        <td class="px-5 py-5 align-top" @click="emit('open', c.id)">
                            <span
                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold capitalize ring-1 ring-inset"
                                :class="segmentBadgeClass(c.segment)"
                            >
                                {{ SEGMENT_LABELS[c.segment] ?? c.segment }}
                            </span>
                        </td>
                        <td
                            class="hidden cursor-pointer px-5 py-5 align-top text-right text-sm font-semibold tabular-nums text-slate-800 md:table-cell dark:text-slate-100"
                            @click="emit('open', c.id)"
                        >
                            {{ c.policies_count ?? 0 }}
                        </td>
                        <td
                            class="hidden cursor-pointer px-5 py-5 align-top text-right text-sm font-semibold tabular-nums text-slate-800 lg:table-cell dark:text-slate-100"
                            @click="emit('open', c.id)"
                        >
                            {{ formatMoneyTr(c.total_premium) }}
                        </td>
                        <td
                            class="hidden cursor-pointer px-5 py-5 align-top text-sm text-slate-600 xl:table-cell dark:text-slate-300"
                            @click="emit('open', c.id)"
                        >
                            {{ formatWhen(c.last_transaction_at) }}
                        </td>
                        <td class="cursor-pointer px-5 py-5 align-top" @click="emit('open', c.id)">
                            <div v-if="c.assigned_user" class="flex items-center gap-2">
                                <span
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-bold text-white shadow-sm"
                                >
                                    {{ initials(c.assigned_user.name) }}
                                </span>
                                <span class="hidden text-sm font-medium text-slate-700 sm:inline dark:text-slate-200">
                                    {{ c.assigned_user.name }}
                                </span>
                            </div>
                            <span v-else class="text-sm text-slate-400">—</span>
                        </td>
                        <td class="px-5 py-5 align-top text-right" @click.stop>
                            <div class="flex flex-wrap items-center justify-end gap-1">
                                <a
                                    v-if="c.phone"
                                    :href="'tel:' + digitsPhone(c.phone)"
                                    class="inline-flex rounded-xl border border-slate-200 p-2 text-slate-600 transition hover:scale-105 hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-600"
                                    title="Ara"
                                >
                                    <PhoneIcon class="h-4 w-4" />
                                </a>
                                <a
                                    v-if="c.phone"
                                    :href="waLink(c.phone)"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex rounded-xl border border-slate-200 p-2 text-slate-600 transition hover:scale-105 hover:border-emerald-300 hover:text-emerald-600 dark:border-slate-600"
                                    title="WhatsApp"
                                >
                                    <DevicePhoneMobileIcon class="h-4 w-4" />
                                </a>
                                <Link
                                    :href="route('policies.create', { customer_id: c.id })"
                                    class="inline-flex rounded-xl border border-slate-200 p-2 text-slate-600 transition hover:scale-105 hover:border-violet-300 hover:text-violet-600 dark:border-slate-600"
                                    title="Poliçe ekle"
                                >
                                    <PlusIcon class="h-4 w-4" />
                                </Link>
                                <button
                                    type="button"
                                    class="inline-flex rounded-xl border border-slate-200 p-2 text-slate-600 transition hover:scale-105 hover:border-indigo-300 dark:border-slate-600"
                                    title="Not ekle"
                                    @click="emit('quick-note', c)"
                                >
                                    <ChatBubbleBottomCenterTextIcon class="h-4 w-4" />
                                </button>
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
