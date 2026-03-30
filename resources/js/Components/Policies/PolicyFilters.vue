<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

defineProps({
    model: { type: Object, required: true },
    policyTypes: { type: Array, default: () => [] },
    insuranceCompanies: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
});

const emit = defineEmits(['apply', 'reset']);
</script>

<template>
    <form
        class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        @submit.prevent="emit('apply')"
    >
        <div class="grid gap-4 lg:grid-cols-7">
            <div class="lg:col-span-2">
                <label class="text-xs font-semibold text-slate-500">Arama</label>
                <input
                    v-model="model.search"
                    type="search"
                    placeholder="Poliçe no veya müşteri…"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                />
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Poliçe türü</label>
                <select
                    v-model="model.policy_type_id"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                >
                    <option value="">Tümü</option>
                    <option v-for="t in policyTypes" :key="t.id" :value="t.id">
                        {{ t.name }}
                    </option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Sigorta şirketi</label>
                <select
                    v-model="model.insurance_company_id"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                >
                    <option value="">Tümü</option>
                    <option v-for="c in insuranceCompanies" :key="c.id" :value="c.id">
                        {{ c.name }}
                    </option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Yenileme durumu</label>
                <select
                    v-model="model.renewal_status"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                >
                    <option value="">Tümü</option>
                    <option value="active">Devam ediyor</option>
                    <option value="pending_renewal">Bekliyor</option>
                    <option value="renewed">Yenilendi</option>
                    <option value="not_renewed">Yenilenmedi</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Bitişe kalan (en fazla)</label>
                <select
                    v-model="model.expires_within_days"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                >
                    <option value="">—</option>
                    <option value="1">1 gün</option>
                    <option value="3">3 gün</option>
                    <option value="7">7 gün</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Temsilci</label>
                <select
                    v-model="model.owner_user_id"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                >
                    <option value="">Tümü</option>
                    <option v-for="u in users" :key="u.id" :value="u.id">
                        {{ u.name }}
                    </option>
                </select>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
            <PrimaryButton type="submit" class="rounded-xl">Filtrele</PrimaryButton>
            <SecondaryButton type="button" class="rounded-xl" @click="emit('reset')">
                Sıfırla
            </SecondaryButton>
        </div>
    </form>
</template>
