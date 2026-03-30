<script setup>
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

function notify(message, variant = 'success') {
    if (!message || typeof window === 'undefined') {
        return;
    }
    window.dispatchEvent(
        new CustomEvent('pa-toast', { detail: { message, variant } }),
    );
}

watch(
    () => page.props.flash?.success,
    (v) => notify(v, 'success'),
    { immediate: true },
);

watch(
    () => page.props.flash?.error,
    (v) => {
        if (v) {
            notify(v, 'error');
        }
    },
    { immediate: true },
);

watch(
    () => page.props.flash?.warning,
    (v) => {
        if (v) {
            notify(v, 'warning');
        }
    },
    { immediate: true },
);
</script>

<template>
    <div />
</template>
