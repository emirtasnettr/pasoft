<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap gap-1">
        <template v-for="(link, i) in links" :key="i">
            <Link
                v-if="link.url"
                :href="link.url"
                class="rounded-xl px-3 py-1.5 text-sm font-medium transition"
                :class="
                    link.active
                        ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/30'
                        : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-700'
                "
                v-html="link.label"
            />
            <span
                v-else
                class="rounded-xl px-3 py-1.5 text-sm text-slate-400"
                v-html="link.label"
            />
        </template>
    </nav>
</template>
