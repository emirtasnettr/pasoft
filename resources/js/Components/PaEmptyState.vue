<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    actionLabel: { type: String, default: '' },
    actionHref: { type: String, default: '' },
});
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200/90 bg-slate-50/50 px-8 py-14 text-center dark:border-slate-700 dark:bg-slate-900/40"
    >
        <div
            class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-md shadow-slate-900/5 ring-1 ring-slate-900/5 transition-transform duration-300 hover:scale-105 dark:bg-slate-800 dark:ring-white/10"
        >
            <slot name="icon" />
        </div>
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
            {{ title }}
        </h3>
        <p
            v-if="description"
            class="mt-2 max-w-sm text-sm text-slate-500 dark:text-slate-400"
        >
            {{ description }}
        </p>
        <Link
            v-if="actionHref && actionLabel"
            :href="actionHref"
            class="mt-6 inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition duration-200 hover:from-indigo-500 hover:to-violet-500 hover:shadow-indigo-500/40 active:scale-[0.98]"
        >
            {{ actionLabel }}
        </Link>
    </div>
</template>
