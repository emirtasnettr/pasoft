<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

import { onMounted, watch } from 'vue';

const props = defineProps({
    customers: Array,
    policyTypes: Array,
    insuranceCompanies: Array,
    users: Array,
    prefillFromLead: { type: Object, default: null },
    prefillFromCustomer: { type: Object, default: null },
    policyDefaults: {
        type: Object,
        default: () => ({
            commission_rate: null,
            min_commission: null,
            renewal_reminder_days: null,
        }),
    },
});

const form = useForm({
    customer_id: '',
    policy_type_id: '',
    insurance_company_id: '',
    policy_number: '',
    starts_at: '',
    ends_at: '',
    premium_amount: '',
    commission_amount: '',
    commission_collected: '0',
    premium_payment_status: 'pending',
    coverage_details: '',
    renewal_status: 'active',
    renewal_pipeline_stage: '',
    renewal_reminder_days: 30,
    owner_user_id: '',
    pdf: null,
});

function submit() {
    form.post(route('policies.store'), {
        forceFormData: true,
    });
}

function applyPrefill(p) {
    if (!p) {
        return;
    }
    if (p.customer_id) {
        form.customer_id = String(p.customer_id);
    }
    if (p.coverage_details) {
        form.coverage_details = p.coverage_details;
    }
    if (p.owner_user_id) {
        form.owner_user_id = String(p.owner_user_id);
    }
}

onMounted(() => {
    applyPrefill(props.prefillFromLead);
    applyPrefill(props.prefillFromCustomer);
});

function policyCreateQueryParams() {
    const params = {};
    const u = new URLSearchParams(window.location.search);
    const leadId = u.get('lead_id');
    const customerId = u.get('customer_id');
    if (leadId) {
        params.lead_id = leadId;
    }
    if (customerId) {
        params.customer_id = customerId;
    }
    if (form.insurance_company_id) {
        params.defaults_company = form.insurance_company_id;
    }
    if (form.policy_type_id) {
        params.defaults_type = form.policy_type_id;
    }

    return params;
}

let defaultsTimer;
function schedulePolicyDefaultsFetch() {
    clearTimeout(defaultsTimer);
    defaultsTimer = setTimeout(() => {
        if (!form.insurance_company_id && !form.policy_type_id) {
            return;
        }
        router.get(route('policies.create'), policyCreateQueryParams(), {
            preserveState: true,
            replace: true,
            only: ['policyDefaults'],
        });
    }, 250);
}

watch([() => form.insurance_company_id, () => form.policy_type_id], schedulePolicyDefaultsFetch);

watch(
    () => props.policyDefaults,
    (d) => {
        if (!d) {
            return;
        }
        if (d.renewal_reminder_days != null) {
            form.renewal_reminder_days = Number(d.renewal_reminder_days);
        }
    },
    { deep: true },
);

function syncCommissionFromDefaults() {
    const p = parseFloat(String(form.premium_amount).replace(',', '.'));
    const rate = props.policyDefaults?.commission_rate;
    const minC = props.policyDefaults?.min_commission;
    if (!Number.isFinite(p) || p <= 0 || rate == null) {
        return;
    }
    const fromRate = (p * Number(rate)) / 100;
    const floor = minC != null ? Number(minC) : 0;
    const amount = Math.max(fromRate, floor);
    form.commission_amount = amount.toFixed(2);
}

watch(
    [() => form.premium_amount, () => props.policyDefaults?.commission_rate, () => props.policyDefaults?.min_commission],
    syncCommissionFromDefaults,
);
</script>

<template>
    <Head title="Yeni poliçe" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Yeni poliçe</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    PDF yükleyebilir, taksitleri sonradan poliçe detayından ekleyebilirsiniz. Firma ve poliçe türü
                    seçildiğinde tanımlardaki komisyon ve yenileme süresi otomatik önerilir.
                </p>
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
                            required
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
                        <InputLabel value="Poliçe türü" />
                        <select
                            v-model="form.policy_type_id"
                            required
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option value="" disabled>Seçin</option>
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
                            required
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option value="" disabled>Seçin</option>
                            <option v-for="ic in insuranceCompanies" :key="ic.id" :value="ic.id">
                                {{ ic.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.insurance_company_id" />
                    </div>
                    <div
                        v-if="
                            policyDefaults?.commission_rate != null ||
                            policyDefaults?.min_commission != null ||
                            policyDefaults?.renewal_reminder_days != null
                        "
                        class="sm:col-span-2 rounded-xl border border-indigo-100 bg-indigo-50/80 px-4 py-3 text-xs text-indigo-900 dark:border-indigo-500/25 dark:bg-indigo-950/40 dark:text-indigo-100"
                    >
                        <span v-if="policyDefaults.commission_rate != null" class="block">
                            Pivot komisyon oranı:
                            <strong>{{ policyDefaults.commission_rate }}%</strong>
                            <template v-if="policyDefaults.min_commission != null">
                                · min. <strong>{{ policyDefaults.min_commission }} ₺</strong>
                            </template>
                            — prim girildiğinde komisyon tutarı önerilir (manuel değiştirebilirsiniz).
                        </span>
                        <span v-if="policyDefaults.renewal_reminder_days != null" class="mt-1 block">
                            Tür tanımına göre yenileme hatırlatması:
                            <strong>{{ policyDefaults.renewal_reminder_days }} gün</strong>
                        </span>
                    </div>
                    <div
                        v-if="
                            form.insurance_company_id &&
                            form.policy_type_id &&
                            policyDefaults?.commission_rate == null
                        "
                        class="sm:col-span-2 rounded-xl border border-amber-200 bg-amber-50/90 px-4 py-3 text-xs text-amber-950 dark:border-amber-500/30 dark:bg-amber-950/40 dark:text-amber-100"
                    >
                        Bu firma ve poliçe türü için
                        <strong>komisyon eşleşmesi tanımlı değil</strong>. Tanımlar &gt; firma veya poliçe türü
                        ekranından ekleyin veya komisyonu aşağıya manuel girin.
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
                        <InputLabel value="Prim tahsilat durumu" />
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
                        <InputLabel value="Yenileme durumu" />
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
                    <div>
                        <InputLabel value="Yenileme hatırlatma (gün)" />
                        <input
                            v-model.number="form.renewal_reminder_days"
                            type="number"
                            min="0"
                            max="3650"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError :message="form.errors.renewal_reminder_days" />
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
                        <InputLabel value="Sorumlu kullanıcı" />
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
                        <InputLabel value="PDF poliçe" />
                        <input
                            type="file"
                            accept="application/pdf"
                            class="mt-1 block w-full text-sm text-slate-600"
                            @input="form.pdf = $event.target.files[0]"
                        />
                        <InputError :message="form.errors.pdf" />
                    </div>
                </div>
                <div class="flex gap-3">
                    <PrimaryButton :disabled="form.processing" class="rounded-xl">
                        Kaydet
                    </PrimaryButton>
                    <Link
                        :href="route('policies.index')"
                        class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        Vazgeç
                    </Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
