<script setup>
import { activityLabel } from '@/lib/taskLabels';

defineProps({
    activities: { type: Array, default: () => [] },
});

function formatDt(iso) {
    if (!iso) {
        return '';
    }
    return iso.replace('T', ' ').slice(0, 16);
}

function detailLine(act) {
    const m = act.meta || {};
    if (act.action === 'status_changed' && m.from && m.to) {
        return `${m.from} → ${m.to}`;
    }
    if (act.action === 'note_added' && m.preview) {
        return m.preview;
    }
    if (act.action === 'file_added' && m.name) {
        return m.name;
    }
    if (act.action === 'completed' && m.from) {
        return `Önceki: ${m.from}`;
    }
    return '';
}
</script>

<template>
    <div class="space-y-0">
        <div
            v-for="(act, idx) in activities"
            :key="act.id"
            class="relative flex gap-3 pb-6 pl-1 last:pb-0"
        >
            <div
                v-if="idx < activities.length - 1"
                class="absolute bottom-0 left-[11px] top-8 w-px bg-slate-200 dark:bg-slate-700"
                aria-hidden="true"
            />
            <div
                class="relative z-[1] mt-0.5 h-6 w-6 shrink-0 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 shadow-md shadow-indigo-500/25 ring-4 ring-white dark:ring-slate-950"
            />
            <div class="min-w-0 flex-1 rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-2 dark:border-slate-800 dark:bg-slate-900/50">
                <p class="text-sm font-semibold text-slate-900 dark:text-white">
                    {{ activityLabel(act.action) }}
                </p>
                <p v-if="detailLine(act)" class="mt-0.5 text-xs text-slate-600 dark:text-slate-400">
                    {{ detailLine(act) }}
                </p>
                <p class="mt-1 text-xs text-slate-500">
                    {{ act.user?.name ?? 'Sistem' }} · {{ formatDt(act.created_at) }}
                </p>
            </div>
        </div>
    </div>
</template>
