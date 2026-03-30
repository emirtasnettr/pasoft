<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';

defineProps({
    form: { type: Object, required: true },
    users: { type: Array, default: () => [] },
    customers: { type: Array, default: () => [] },
    leads: { type: Array, default: () => [] },
    policies: { type: Array, default: () => [] },
    showPolicy: { type: Boolean, default: true },
});
</script>

<template>
    <div class="space-y-4">
        <div>
            <InputLabel value="Başlık" />
            <input
                v-model="form.title"
                type="text"
                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
            />
            <InputError :message="form.errors.title" />
        </div>
        <div>
            <InputLabel value="Açıklama" />
            <textarea
                v-model="form.description"
                rows="3"
                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
            />
            <InputError :message="form.errors.description" />
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <InputLabel value="Tür" />
                <select
                    v-model="form.type"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                >
                    <option value="call">Arama</option>
                    <option value="visit">Ziyaret</option>
                    <option value="proposal">Teklif</option>
                    <option value="follow_up">Takip</option>
                    <option value="other">Diğer</option>
                </select>
                <InputError :message="form.errors.type" />
            </div>
            <div>
                <InputLabel value="Öncelik" />
                <select
                    v-model="form.priority"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                >
                    <option value="low">Düşük</option>
                    <option value="medium">Orta</option>
                    <option value="high">Yüksek</option>
                </select>
                <InputError :message="form.errors.priority" />
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <InputLabel value="Durum" />
                <select
                    v-model="form.status"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                >
                    <option value="pending">Bekliyor</option>
                    <option value="in_progress">Devam ediyor</option>
                    <option value="done">Tamamlandı</option>
                    <option value="cancelled">İptal</option>
                </select>
                <InputError :message="form.errors.status" />
            </div>
            <div>
                <InputLabel value="Vade" />
                <input
                    v-model="form.due_at"
                    type="datetime-local"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                />
                <InputError :message="form.errors.due_at" />
            </div>
        </div>
        <div>
            <InputLabel value="Hatırlatma" />
            <input
                v-model="form.remind_at"
                type="datetime-local"
                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
            />
            <InputError :message="form.errors.remind_at" />
        </div>
        <div>
            <InputLabel value="Atanan" />
            <select
                v-model="form.assigned_user_id"
                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
            >
                <option value="">—</option>
                <option v-for="u in users" :key="u.id" :value="u.id">
                    {{ u.name }}
                </option>
            </select>
            <InputError :message="form.errors.assigned_user_id" />
        </div>
        <div>
            <InputLabel value="Müşteri" />
            <select
                v-model="form.customer_id"
                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
            >
                <option value="">—</option>
                <option v-for="c in customers" :key="c.id" :value="c.id">
                    {{ c.name }}
                </option>
            </select>
            <InputError :message="form.errors.customer_id" />
        </div>
        <div>
            <InputLabel value="Lead" />
            <select
                v-model="form.lead_id"
                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
            >
                <option value="">—</option>
                <option v-for="l in leads" :key="l.id" :value="l.id">
                    {{ l.title }}
                </option>
            </select>
            <InputError :message="form.errors.lead_id" />
        </div>
        <div v-if="showPolicy">
            <InputLabel value="Poliçe" />
            <select
                v-model="form.policy_id"
                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
            >
                <option value="">—</option>
                <option v-for="p in policies" :key="p.id" :value="p.id">
                    {{ p.policy_number }}
                    <template v-if="p.customer"> — {{ p.customer.name }}</template>
                </option>
            </select>
            <InputError :message="form.errors.policy_id" />
        </div>
    </div>
</template>
