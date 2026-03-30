<script setup>
defineOptions({ inheritAttrs: false });
</script>

<template>
    <button
        type="submit"
        class="inline-flex items-center justify-center rounded-xl border border-transparent bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white shadow-md shadow-indigo-500/25 transition duration-200 ease-out hover:from-indigo-500 hover:to-violet-500 hover:shadow-lg hover:shadow-indigo-500/30 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:scale-[0.97] disabled:opacity-40 dark:focus:ring-offset-slate-900"
        v-bind="$attrs"
    >
        <slot />
    </button>
</template>
