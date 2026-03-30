<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    claim: Object,
    customers: Array,
    policies: Array,
    insuranceCompanies: Array,
    users: Array,
    statusOptions: Array,
});

const form = useForm({
    customer_id: props.claim.customer_id,
    policy_id: props.claim.policy_id ?? '',
    insurance_company_id: props.claim.insurance_company_id ?? '',
    file_number: props.claim.file_number,
    claim_type: props.claim.claim_type ?? '',
    amount: props.claim.amount != null ? String(props.claim.amount) : '',
    paid_amount: props.claim.paid_amount != null ? String(props.claim.paid_amount) : '0',
    status: props.claim.status,
    customer_notice_notes: props.claim.customer_notice_notes ?? '',
    internal_notes: props.claim.internal_notes ?? '',
    handler_user_id: props.claim.handler_user_id ?? '',
});

function submit() {
    form
        .transform((data) => ({
            ...data,
            amount: data.amount === '' ? null : data.amount,
            paid_amount: data.paid_amount === '' ? 0 : data.paid_amount,
        }))
        .put(route('claims.update', props.claim.id));
}
</script>

<template>
    <Head title="Hasar düzenle" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Hasar düzenle</h1>
            </div>
        </template>

        <div class="mx-auto max-w-2xl">
            <form
                class="space-y-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                @submit.prevent="submit"
            >
                <div>
                    <InputLabel value="Müşteri" />
                    <select
                        v-model="form.customer_id"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option v-for="c in customers" :key="c.id" :value="c.id">
                            {{ c.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.customer_id" />
                </div>
                <div>
                    <InputLabel value="Poliçe" />
                    <select
                        v-model="form.policy_id"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="">—</option>
                        <option v-for="p in policies" :key="p.id" :value="p.id">
                            {{ p.policy_number }}
                        </option>
                    </select>
                    <InputError :message="form.errors.policy_id" />
                </div>
                <div>
                    <InputLabel value="Sigorta şirketi" />
                    <select
                        v-model="form.insurance_company_id"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="">—</option>
                        <option v-for="ic in insuranceCompanies" :key="ic.id" :value="ic.id">
                            {{ ic.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.insurance_company_id" />
                </div>
                <div>
                    <InputLabel value="Dosya no" />
                    <input
                        v-model="form.file_number"
                        type="text"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError :message="form.errors.file_number" />
                </div>
                <div>
                    <InputLabel value="Hasar türü" />
                    <input
                        v-model="form.claim_type"
                        type="text"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError :message="form.errors.claim_type" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Hasar tutarı (TRY)" />
                        <input
                            v-model="form.amount"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError :message="form.errors.amount" />
                    </div>
                    <div>
                        <InputLabel value="Ödenen (TRY)" />
                        <input
                            v-model="form.paid_amount"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError :message="form.errors.paid_amount" />
                    </div>
                </div>
                <div>
                    <InputLabel value="Durum" />
                    <select
                        v-model="form.status"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <InputError :message="form.errors.status" />
                </div>
                <div>
                    <InputLabel value="Müşteri notu" />
                    <textarea
                        v-model="form.customer_notice_notes"
                        rows="2"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError :message="form.errors.customer_notice_notes" />
                </div>
                <div>
                    <InputLabel value="İç not" />
                    <textarea
                        v-model="form.internal_notes"
                        rows="2"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError :message="form.errors.internal_notes" />
                </div>
                <div>
                    <InputLabel value="Sorumlu" />
                    <select
                        v-model="form.handler_user_id"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="">—</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">
                            {{ u.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.handler_user_id" />
                </div>
                <div class="flex gap-3">
                    <PrimaryButton :disabled="form.processing" class="rounded-xl">
                        Güncelle
                    </PrimaryButton>
                    <Link
                        :href="route('claims.show', claim.id)"
                        class="inline-flex items-center rounded-xl px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        İptal
                    </Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
