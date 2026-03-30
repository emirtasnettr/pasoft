<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    policy: Object,
    customers: Array,
    policyTypes: Array,
    insuranceCompanies: Array,
    users: Array,
});

const form = useForm({
    customer_id: props.policy.customer_id,
    policy_type_id: props.policy.policy_type_id,
    insurance_company_id: props.policy.insurance_company_id,
    policy_number: props.policy.policy_number,
    starts_at: props.policy.starts_at?.slice?.(0, 10) ?? props.policy.starts_at,
    ends_at: props.policy.ends_at?.slice?.(0, 10) ?? props.policy.ends_at,
    premium_amount: props.policy.premium_amount,
    commission_amount: props.policy.commission_amount,
    commission_collected: props.policy.commission_collected,
    premium_payment_status: props.policy.premium_payment_status,
    coverage_details: props.policy.coverage_details ?? '',
    renewal_status: props.policy.renewal_status,
    renewal_pipeline_stage: props.policy.renewal_pipeline_stage ?? '',
    renewal_reminder_days: props.policy.renewal_reminder_days,
    owner_user_id: props.policy.owner_user_id ?? '',
    pdf: null,
});

function submit() {
    form.put(route('policies.update', props.policy.id), {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Poliçe düzenle" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Poliçe düzenle</h1>
            </div>
        </template>

        <div class="mx-auto max-w-3xl">
            <form
                class="space-y-5 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                @submit.prevent="submit"
            >
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <InputLabel value="Müşteri" />
                        <select
                            v-model="form.customer_id"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option v-for="c in customers" :key="c.id" :value="c.id">
                                {{ c.company_name || c.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.customer_id" />
                    </div>
                    <div>
                        <InputLabel value="Poliçe türü" />
                        <select
                            v-model="form.policy_type_id"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option v-for="t in policyTypes" :key="t.id" :value="t.id">
                                {{ t.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.policy_type_id" />
                    </div>
                    <div>
                        <InputLabel value="Sigorta şirketi" />
                        <select
                            v-model="form.insurance_company_id"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option v-for="ic in insuranceCompanies" :key="ic.id" :value="ic.id">
                                {{ ic.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.insurance_company_id" />
                    </div>
                    <div class="sm:col-span-2">
                        <InputLabel value="Poliçe numarası" />
                        <input
                            v-model="form.policy_number"
                            type="text"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError :message="form.errors.policy_number" />
                    </div>
                    <div>
                        <InputLabel value="Başlangıç" />
                        <input
                            v-model="form.starts_at"
                            type="date"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError :message="form.errors.starts_at" />
                    </div>
                    <div>
                        <InputLabel value="Bitiş" />
                        <input
                            v-model="form.ends_at"
                            type="date"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError :message="form.errors.ends_at" />
                    </div>
                    <div>
                        <InputLabel value="Prim (₺)" />
                        <input
                            v-model="form.premium_amount"
                            type="number"
                            step="0.01"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError :message="form.errors.premium_amount" />
                    </div>
                    <div>
                        <InputLabel value="Komisyon (₺)" />
                        <input
                            v-model="form.commission_amount"
                            type="number"
                            step="0.01"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError :message="form.errors.commission_amount" />
                    </div>
                    <div>
                        <InputLabel value="Tahsil edilen komisyon (₺)" />
                        <input
                            v-model="form.commission_collected"
                            type="number"
                            step="0.01"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError :message="form.errors.commission_collected" />
                    </div>
                    <div>
                        <InputLabel value="Prim tahsilat" />
                        <select
                            v-model="form.premium_payment_status"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option value="pending">Bekliyor</option>
                            <option value="partial">Kısmi</option>
                            <option value="collected">Tahsil edildi</option>
                        </select>
                        <InputError :message="form.errors.premium_payment_status" />
                    </div>
                    <div>
                        <InputLabel value="Yenileme" />
                        <select
                            v-model="form.renewal_status"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option value="active">Aktif</option>
                            <option value="pending_renewal">Yenileme bekliyor</option>
                            <option value="renewed">Yenilendi</option>
                            <option value="not_renewed">Yenilenmedi</option>
                        </select>
                        <InputError :message="form.errors.renewal_status" />
                    </div>
                    <div class="sm:col-span-2">
                        <InputLabel value="Yenileme pipeline aşaması" />
                        <input
                            v-model="form.renewal_pipeline_stage"
                            type="text"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError :message="form.errors.renewal_pipeline_stage" />
                    </div>
                    <div class="sm:col-span-2">
                        <InputLabel value="Teminat detayları" />
                        <textarea
                            v-model="form.coverage_details"
                            rows="4"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError :message="form.errors.coverage_details" />
                    </div>
                    <div>
                        <InputLabel value="Sorumlu" />
                        <select
                            v-model="form.owner_user_id"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option value="">—</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">
                                {{ u.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.owner_user_id" />
                    </div>
                    <div>
                        <InputLabel value="Yeni PDF (opsiyonel)" />
                        <input
                            type="file"
                            accept="application/pdf"
                            class="mt-1 block w-full text-sm"
                            @input="form.pdf = $event.target.files[0]"
                        />
                        <InputError :message="form.errors.pdf" />
                    </div>
                </div>
                <div class="flex gap-3">
                    <PrimaryButton :disabled="form.processing" class="rounded-xl">
                        Güncelle
                    </PrimaryButton>
                    <Link
                        :href="route('policies.show', policy.id)"
                        class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        İptal
                    </Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
