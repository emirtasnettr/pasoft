<script setup>
import {
    STAGE_LABELS,
    STAGE_ORDER,
    TEMP_EMOJI,
    TEMP_LABELS,
    digitsPhone,
    tempBadgeClass,
    waLink,
} from '@/lib/leadLabels';
import { router } from '@inertiajs/vue3';
import {
    ChatBubbleBottomCenterTextIcon,
    DevicePhoneMobileIcon,
    PhoneIcon,
} from '@heroicons/vue/24/outline';

defineProps({
    rows: { type: Array, default: () => [] },
});

const emit = defineEmits(['open', 'quick-note']);

function formatActivity(iso) {
    if (!iso) {
        return '—';
    }
    return iso.replace('T', ' ').slice(0, 16);
}

function patchStage(lead, stage) {
    router.patch(
        route('leads.stage', lead.id),
        { stage },
        {
            preserveScroll: true,
            only: ['leads', 'kpi', 'leadDetail', 'flash'],
        },
    );
}

function postTouch(lead) {
    router.post(
        route('leads.touch', lead.id),
        {},
        {
            preserveScroll: true,
            only: ['leads', 'kpi', 'leadDetail', 'flash'],
        },
    );
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
                            Lead
                        </th>
                        <th
                            class="hidden px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 lg:table-cell dark:text-slate-400"
                        >
                            İletişim
                        </th>
                        <th
                            class="hidden px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 md:table-cell dark:text-slate-400"
                        >
                            Kaynak
                        </th>
                        <th
                            class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                        >
                            Aşama
                        </th>
                        <th
                            class="hidden px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 xl:table-cell dark:text-slate-400"
                        >
                            Sıcaklık
                        </th>
                        <th
                            class="hidden px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 lg:table-cell dark:text-slate-400"
                        >
                            Son aktivite
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
                        v-for="l in rows"
                        :key="l.id"
                        class="transition-colors duration-200 hover:bg-slate-50/90 dark:hover:bg-slate-800/50"
                    >
                        <td
                            class="cursor-pointer px-5 py-5 align-top"
                            @click="emit('open', l.id)"
                        >
                            <p class="font-semibold text-slate-900 dark:text-white">
                                {{ l.title }}
                            </p>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <span
                                    v-for="ins in (l.insight_badges || []).slice(0, 2)"
                                    :key="ins.text"
                                    class="inline-flex max-w-full truncate rounded-lg px-2 py-0.5 text-[10px] font-semibold"
                                    :class="
                                        ins.type === 'hot'
                                            ? 'bg-orange-100 text-orange-900 dark:bg-orange-950/40 dark:text-orange-200'
                                            : ins.type === 'stale'
                                              ? 'bg-amber-100 text-amber-900 dark:bg-amber-950/40 dark:text-amber-200'
                                              : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
                                    "
                                >
                                    {{ ins.text }}
                                </span>
                            </div>
                        </td>
                        <td
                            class="hidden cursor-pointer px-5 py-5 align-top lg:table-cell"
                            @click="emit('open', l.id)"
                        >
                            <p class="text-sm text-slate-700 dark:text-slate-200">
                                <a
                                    v-if="l.phone"
                                    :href="'tel:' + digitsPhone(l.phone)"
                                    class="font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                                    @click.stop
                                >
                                    {{ l.phone }}
                                </a>
                                <span v-else class="text-slate-400">—</span>
                            </p>
                            <p class="mt-1 truncate text-xs text-slate-500">
                                {{ l.email || '—' }}
                            </p>
                        </td>
                        <td
                            class="hidden cursor-pointer px-5 py-5 align-top text-sm text-slate-600 md:table-cell dark:text-slate-300"
                            @click="emit('open', l.id)"
                        >
                            {{ l.source || '—' }}
                        </td>
                        <td class="px-5 py-5 align-top" @click.stop>
                            <select
                                class="w-full min-w-[8.5rem] rounded-xl border border-slate-200 bg-white px-2 py-2 text-xs font-semibold text-slate-800 shadow-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                                :value="l.stage"
                                @change="patchStage(l, $event.target.value)"
                            >
                                <option v-for="s in STAGE_ORDER" :key="s" :value="s">
                                    {{ STAGE_LABELS[s] }}
                                </option>
                            </select>
                        </td>
                        <td
                            class="hidden cursor-pointer px-5 py-5 align-top xl:table-cell"
                            @click="emit('open', l.id)"
                        >
                            <span
                                class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-bold"
                                :class="tempBadgeClass(l.temperature)"
                            >
                                <span>{{ TEMP_EMOJI[l.temperature] ?? '🌤️' }}</span>
                                {{ TEMP_LABELS[l.temperature] ?? l.temperature }}
                            </span>
                        </td>
                        <td
                            class="hidden cursor-pointer px-5 py-5 align-top text-sm text-slate-600 lg:table-cell dark:text-slate-300"
                            @click="emit('open', l.id)"
                        >
                            {{ formatActivity(l.last_activity_at) }}
                        </td>
                        <td
                            class="cursor-pointer px-5 py-5 align-top"
                            @click="emit('open', l.id)"
                        >
                            <div v-if="l.assigned_user" class="flex items-center gap-2">
                                <span
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-bold text-white shadow-sm"
                                >
                                    {{ initials(l.assigned_user.name) }}
                                </span>
                                <span class="hidden text-sm font-medium text-slate-700 sm:inline dark:text-slate-200">
                                    {{ l.assigned_user.name }}
                                </span>
                            </div>
                            <span v-else class="text-sm text-slate-400">—</span>
                        </td>
                        <td class="px-5 py-5 align-top text-right" @click.stop>
                            <div class="flex flex-wrap items-center justify-end gap-1">
                                <a
                                    v-if="l.phone"
                                    :href="'tel:' + digitsPhone(l.phone)"
                                    class="inline-flex rounded-xl border border-slate-200 p-2 text-slate-600 transition hover:scale-105 hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-600 dark:hover:border-indigo-500 dark:hover:text-indigo-400"
                                    title="Ara"
                                >
                                    <PhoneIcon class="h-4 w-4" />
                                </a>
                                <a
                                    v-if="l.phone"
                                    :href="waLink(l.phone)"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex rounded-xl border border-slate-200 p-2 text-slate-600 transition hover:scale-105 hover:border-emerald-300 hover:text-emerald-600 dark:border-slate-600 dark:hover:border-emerald-500 dark:hover:text-emerald-400"
                                    title="WhatsApp"
                                >
                                    <DevicePhoneMobileIcon class="h-4 w-4" />
                                </a>
                                <button
                                    type="button"
                                    class="inline-flex rounded-xl border border-slate-200 p-2 text-slate-600 transition hover:scale-105 hover:border-indigo-300 dark:border-slate-600"
                                    title="Teması kaydet"
                                    @click="postTouch(l)"
                                >
                                    <span class="text-xs font-bold">✓</span>
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex rounded-xl border border-slate-200 p-2 text-slate-600 transition hover:scale-105 hover:border-indigo-300 dark:border-slate-600"
                                    title="Not ekle"
                                    @click="emit('quick-note', l)"
                                >
                                    <ChatBubbleBottomCenterTextIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
