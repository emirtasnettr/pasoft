<template>
    <div
        class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
        <div class="animate-pulse space-y-0 divide-y divide-slate-100 dark:divide-slate-800">
            <div v-for="i in 6" :key="i" class="flex gap-4 px-5 py-4">
                <div class="h-10 flex-1 rounded-xl bg-slate-100 dark:bg-slate-800" />
                <div class="hidden h-10 w-24 rounded-xl bg-slate-100 md:block dark:bg-slate-800" />
                <div class="hidden h-10 w-32 rounded-xl bg-slate-100 lg:block dark:bg-slate-800" />
                <div class="h-10 w-20 rounded-xl bg-slate-100 dark:bg-slate-800" />
            </div>
        </div>
    </div>
</template>
