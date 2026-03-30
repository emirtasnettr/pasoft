<script setup>
defineProps({
    as: {
        type: String,
        default: 'div',
    },
    hover: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <component
        :is="as"
        class="rounded-2xl border border-slate-200/90 bg-gradient-to-br from-white via-white to-slate-50/90 p-7 shadow-sm shadow-slate-900/[0.04] ring-1 ring-slate-900/[0.03] dark:border-slate-800 dark:from-slate-900 dark:via-slate-900 dark:to-slate-900/95 dark:shadow-none dark:ring-white/[0.04]"
        :class="
            hover
                ? 'transition-all duration-300 ease-out will-change-transform hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-lg hover:shadow-indigo-500/[0.07] hover:ring-indigo-500/10 dark:hover:shadow-indigo-500/5'
                : ''
        "
    >
        <slot />
    </component>
</template>
