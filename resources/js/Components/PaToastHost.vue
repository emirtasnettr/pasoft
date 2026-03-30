<script setup>
import {
    CheckCircleIcon,
    ExclamationTriangleIcon,
    XCircleIcon,
} from '@heroicons/vue/24/solid';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const open = ref(false);
const message = ref('');
const variant = ref('success');
let timer;

const styles = computed(() => {
    switch (variant.value) {
        case 'error':
            return {
                wrap: 'border-rose-200/90 bg-rose-50/95 text-rose-900 dark:border-rose-500/30 dark:bg-rose-950/90 dark:text-rose-100',
                icon: 'text-rose-500 dark:text-rose-400',
            };
        case 'warning':
            return {
                wrap: 'border-amber-200/90 bg-amber-50/95 text-amber-950 dark:border-amber-500/30 dark:bg-amber-950/90 dark:text-amber-100',
                icon: 'text-amber-500 dark:text-amber-400',
            };
        default:
            return {
                wrap: 'border-emerald-200/90 bg-emerald-50/95 text-emerald-950 dark:border-emerald-500/25 dark:bg-emerald-950/90 dark:text-emerald-50',
                icon: 'text-emerald-500 dark:text-emerald-400',
            };
    }
});

const Icon = computed(() => {
    if (variant.value === 'error') {
        return XCircleIcon;
    }
    if (variant.value === 'warning') {
        return ExclamationTriangleIcon;
    }
    return CheckCircleIcon;
});

function onToast(e) {
    message.value = e.detail?.message ?? '';
    variant.value = e.detail?.variant ?? 'success';
    open.value = !!message.value;
    clearTimeout(timer);
    timer = setTimeout(() => {
        open.value = false;
    }, 4200);
}

onMounted(() => {
    window.addEventListener('pa-toast', onToast);
});

onUnmounted(() => {
    window.removeEventListener('pa-toast', onToast);
    clearTimeout(timer);
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-3 opacity-0 scale-95"
            enter-to-class="translate-y-0 opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0 translate-y-2"
        >
            <div
                v-if="open"
                class="pointer-events-none fixed bottom-8 left-1/2 z-[100] flex w-full max-w-md -translate-x-1/2 justify-center px-4"
            >
                <div
                    class="pointer-events-auto flex w-full items-start gap-3 rounded-2xl border px-4 py-3.5 text-sm font-medium shadow-2xl shadow-slate-900/15 backdrop-blur-md dark:shadow-black/50"
                    :class="styles.wrap"
                >
                    <component
                        :is="Icon"
                        class="mt-0.5 h-5 w-5 shrink-0"
                        :class="styles.icon"
                    />
                    <p class="min-w-0 flex-1 leading-snug">
                        {{ message }}
                    </p>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
