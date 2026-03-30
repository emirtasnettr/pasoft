<script setup>
import LeadCard from '@/Components/Leads/LeadCard.vue';
import { STAGE_LABELS, STAGE_ORDER } from '@/lib/leadLabels';
import { router } from '@inertiajs/vue3';

defineProps({
    columns: { type: Object, required: true },
});

const emit = defineEmits(['open-lead']);

function onDragStart(e, leadId) {
    e.dataTransfer?.setData('text/x-lead-id', String(leadId));
    e.dataTransfer?.setData('text/plain', String(leadId));
    if (e.dataTransfer) {
        e.dataTransfer.effectAllowed = 'move';
    }
}

function onDrop(e, stage) {
    const id = Number(e.dataTransfer?.getData('text/x-lead-id') || e.dataTransfer?.getData('text/plain'));
    if (!id) {
        return;
    }
    router.patch(
        route('leads.stage', id),
        { stage },
        {
            preserveScroll: true,
            only: ['columns', 'kpi', 'leadDetail', 'flash'],
        },
    );
}
</script>

<template>
    <div class="mx-auto max-w-[1600px] overflow-x-auto pb-4">
        <div class="flex min-w-[1100px] gap-4">
            <div
                v-for="stage in STAGE_ORDER"
                :key="stage"
                class="w-72 shrink-0 rounded-2xl border border-slate-200/80 bg-slate-50/80 p-3 dark:border-slate-800 dark:bg-slate-900/50"
                @dragover.prevent
                @drop.prevent="onDrop($event, stage)"
            >
                <div class="mb-3 flex items-center justify-between px-1">
                    <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                        {{ STAGE_LABELS[stage] }}
                    </h2>
                    <span
                        class="rounded-full bg-white px-2.5 py-0.5 text-xs font-bold tabular-nums text-slate-600 shadow-sm dark:bg-slate-800 dark:text-slate-300"
                    >
                        {{ columns[stage]?.length ?? 0 }}
                    </span>
                </div>
                <div class="space-y-3">
                    <div
                        v-for="card in columns[stage] || []"
                        :key="card.id"
                        class="cursor-pointer"
                        @click="emit('open-lead', card.id)"
                    >
                        <LeadCard
                            :lead="card"
                            draggable
                            @drag-start="onDragStart($event, card.id)"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
