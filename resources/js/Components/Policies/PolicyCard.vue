<script setup>
import { URGENCY_LABELS, daysLeftLabel, formatMoneyTr } from '@/lib/policyLabels';
import { Link } from '@inertiajs/vue3';

defineProps({
    policy: { type: Object, required: true },
});
</script>

<template>
    <div
        class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-900 dark:hover:border-indigo-500/40"
    >
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <p class="truncate font-semibold text-slate-900 dark:text-white">
                    {{ policy.policy_number }}
                </p>
                <p class="mt-1 truncate text-sm text-slate-600 dark:text-slate-300">
                    {{ policy.customer?.display_name ?? '—' }}
                </p>
            </div>
            <span
                class="shrink-0 rounded-lg px-2 py-0.5 text-[10px] font-bold"
                :class="policy.urgency_badge_class"
            >
                {{ URGENCY_LABELS[policy.urgency] }}
            </span>
        </div>
        <p class="mt-2 text-xs text-slate-500">
            {{ policy.policy_type?.name }} · {{ daysLeftLabel(policy) }}
        </p>
        <p class="mt-2 text-sm font-bold tabular-nums text-slate-900 dark:text-white">
            {{ formatMoneyTr(policy.premium_amount) }}
        </p>
        <Link
            v-if="policy.customer?.id"
            :href="route('customers.index', { customer: policy.customer.id })"
            class="mt-3 inline-block text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400"
        >
            Müşteri →
        </Link>
    </div>
</template>
