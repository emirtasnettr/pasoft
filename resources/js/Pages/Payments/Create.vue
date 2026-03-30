<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    customers: Array,
    policies: Array,
    prefillCustomerId: { type: [Number, String], default: null },
});

const form = useForm({
    customer_id: '',
    policy_id: '',
    installment_id: '',
    amount: '',
    method: 'transfer',
    paid_at: new Date().toISOString().slice(0, 16),
    reference: '',
    notes: '',
});

watch(
    () => props.prefillCustomerId,
    (id) => {
        if (id != null && id !== '') {
            form.customer_id = String(id);
        }
    },
    { immediate: true },
);

const pendingInstallments = computed(() => {
    const p = props.policies?.find((x) => String(x.id) === String(form.policy_id));
    return p?.installments ?? [];
});

watch(
    () => form.policy_id,
    () => {
        form.installment_id = '';
    },
);

function submit() {
    form.post(route('payments.store'));
}
</script>

<template>
    <Head title="Tahsilat" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Tahsilat kaydı</h1>
            </div>
        </template>

        <div class="mx-auto max-w-xl">
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
                        <option value="" disabled>Seçin</option>
                        <option v-for="c in customers" :key="c.id" :value="c.id">
                            {{ c.company_name || c.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.customer_id" />
                </div>
                <div>
                    <InputLabel value="Poliçe (opsiyonel)" />
                    <select
                        v-model="form.policy_id"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="">—</option>
                        <option v-for="p in policies" :key="p.id" :value="p.id">
                            {{ p.policy_number }} — {{ p.customer?.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.policy_id" />
                </div>
                <div v-if="pendingInstallments.length">
                    <InputLabel value="Taksit (ödenmemiş)" />
                    <select
                        v-model="form.installment_id"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="">— Genel tahsilat</option>
                        <option v-for="i in pendingInstallments" :key="i.id" :value="i.id">
                            #{{ i.sequence }} · vade {{ i.due_date }} · {{ Number(i.amount).toLocaleString('tr-TR') }} ₺
                        </option>
                    </select>
                    <InputError :message="form.errors.installment_id" />
                </div>
                <div>
                    <InputLabel value="Tutar (₺)" />
                    <input
                        v-model="form.amount"
                        type="number"
                        step="0.01"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError :message="form.errors.amount" />
                </div>
                <div>
                    <InputLabel value="Yöntem" />
                    <select
                        v-model="form.method"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="cash">Nakit</option>
                        <option value="card">Kredi kartı</option>
                        <option value="transfer">Havale</option>
                    </select>
                    <InputError :message="form.errors.method" />
                </div>
                <div>
                    <InputLabel value="Ödeme zamanı" />
                    <input
                        v-model="form.paid_at"
                        type="datetime-local"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError :message="form.errors.paid_at" />
                </div>
                <div>
                    <InputLabel value="Referans" />
                    <input
                        v-model="form.reference"
                        type="text"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError :message="form.errors.reference" />
                </div>
                <div>
                    <InputLabel value="Not" />
                    <textarea
                        v-model="form.notes"
                        rows="2"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError :message="form.errors.notes" />
                </div>
                <div class="flex gap-3">
                    <PrimaryButton :disabled="form.processing" class="rounded-xl">
                        Kaydet
                    </PrimaryButton>
                    <Link
                        :href="route('payments.index')"
                        class="inline-flex items-center rounded-xl px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        Vazgeç
                    </Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
