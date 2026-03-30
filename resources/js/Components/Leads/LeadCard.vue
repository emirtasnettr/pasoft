<script setup>
import {
    STAGE_LABELS,
    TEMP_EMOJI,
    TEMP_LABELS,
    digitsPhone,
    stageBadgeClass,
    tempBadgeClass,
} from '@/lib/leadLabels';
import { Bars2Icon } from '@heroicons/vue/24/outline';

defineProps({
    lead: { type: Object, required: true },
    compact: { type: Boolean, default: true },
    draggable: { type: Boolean, default: false },
});

const emit = defineEmits(['drag-start']);

function onHandleDragStart(e) {
    emit('drag-start', e);
}
</script>

<template>
    <div
        class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-900 dark:hover:border-indigo-500/40"
    >
        <div class="flex items-start justify-between gap-2">
            <button
                v-if="draggable"
                type="button"
                draggable="true"
                class="-ml-1 cursor-grab rounded-lg p-1 text-slate-400 hover:bg-slate-100 active:cursor-grabbing dark:hover:bg-slate-800"
                title="Sürükle"
                aria-label="Sürükle"
                @dragstart="onHandleDragStart"
                @click.stop.prevent
            >
                <Bars2Icon class="h-5 w-5" />
            </button>
            <p class="min-w-0 flex-1 font-semibold text-slate-900 dark:text-white">
                {{ lead.title }}
            </p>
            <span
                class="shrink-0 text-lg leading-none"
                :title="TEMP_LABELS[lead.temperature] ?? ''"
            >
                {{ TEMP_EMOJI[lead.temperature] ?? '🌤️' }}
            </span>
        </div>
        <p v-if="lead.phone" class="mt-2 text-sm font-medium text-slate-700 dark:text-slate-200">
            <a
                :href="'tel:' + digitsPhone(lead.phone)"
                class="text-indigo-600 hover:underline dark:text-indigo-400"
                @click.stop
            >
                {{ lead.phone }}
            </a>
        </p>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            {{ lead.source || 'Kaynak belirtilmemiş' }}
        </p>
        <div v-if="!compact && lead.email" class="mt-1 truncate text-xs text-slate-500">
            {{ lead.email }}
        </div>
        <div class="mt-2 flex flex-wrap items-center gap-2">
            <span
                class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide ring-1 ring-inset"
                :class="stageBadgeClass(lead.stage)"
            >
                {{ STAGE_LABELS[lead.stage] ?? lead.stage }}
            </span>
            <span
                class="inline-flex rounded-lg px-2 py-0.5 text-[10px] font-semibold"
                :class="tempBadgeClass(lead.temperature)"
            >
                {{ TEMP_LABELS[lead.temperature] ?? lead.temperature }}
            </span>
        </div>
        <p
            v-if="lead.estimated_value"
            class="mt-2 text-sm font-bold text-indigo-600 dark:text-indigo-400"
        >
            ₺{{ Number(lead.estimated_value).toLocaleString('tr-TR') }}
        </p>
        <div v-if="lead.assigned_user" class="mt-2 flex items-center gap-2">
            <span
                class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-slate-600 to-slate-800 text-[10px] font-bold text-white"
            >
                {{
                    lead.assigned_user.name
                        .split(/\s+/)
                        .map((p) => p[0])
                        .join('')
                        .slice(0, 2)
                        .toUpperCase()
                }}
            </span>
            <span class="text-xs font-medium text-slate-600 dark:text-slate-300">
                {{ lead.assigned_user.name }}
            </span>
        </div>
    </div>
</template>
