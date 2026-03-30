<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import LeadDrawer from '@/Components/Leads/LeadDrawer.vue';
import LeadKanban from '@/Components/Leads/LeadKanban.vue';
import LeadKpiBar from '@/Components/Leads/LeadKpiBar.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ListBulletIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    columns: Object,
    users: Array,
    customers: Array,
    kpi: Object,
    leadDetail: Object,
    filters: Object,
});

const stageOrder = ['new', 'contacted', 'proposal', 'meeting', 'won', 'lost'];
const stageLabels = {
    new: 'Yeni',
    contacted: 'İletişime geçildi',
    proposal: 'Teklif',
    meeting: 'Görüşme',
    won: 'Kazanıldı',
    lost: 'Kaybedildi',
};

const open = ref(false);
const form = useForm({
    title: '',
    email: '',
    phone: '',
    source: '',
    stage: 'new',
    estimated_value: '',
    assigned_user_id: '',
    customer_id: '',
    next_follow_up_at: '',
    notes: '',
});

function submitLead() {
    form.post(route('leads.store'), {
        preserveScroll: true,
        only: ['columns', 'kpi', 'flash'],
        onSuccess: () => {
            form.reset();
            open.value = false;
        },
    });
}

function openLeadDrawer(id) {
    router.get(
        route('leads.kanban'),
        { ...props.filters, lead: id },
        {
            preserveScroll: true,
            preserveState: true,
            only: ['leadDetail', 'filters', 'flash'],
        },
    );
}
</script>

<template>
    <Head title="Lead Kanban" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-lg font-semibold text-slate-900 dark:text-white">
                        Satış pipeline
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Sürükleyerek aşama değiştirin; karta tıklayınca detay paneli açılır.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="route('leads.index')"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        <ListBulletIcon class="h-4 w-4" />
                        Liste görünümü
                    </Link>
                    <PrimaryButton type="button" class="rounded-xl" @click="open = true">
                        Yeni lead
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-[1600px] space-y-8">
            <LeadKpiBar v-if="kpi" :kpi="kpi" />
            <LeadKanban :columns="columns" @open-lead="openLeadDrawer" />
        </div>

        <LeadDrawer
            :lead-detail="leadDetail"
            :filters="filters"
            :users="users"
            :customers="customers"
            context="kanban"
        />

        <Teleport to="body">
            <div
                v-if="open"
                class="fixed inset-0 z-[60] flex justify-end bg-slate-900/40 backdrop-blur-sm"
                @click.self="open = false"
            >
                <div
                    class="h-full w-full max-w-md overflow-y-auto border-l border-slate-200 bg-white p-6 shadow-2xl transition-transform duration-300 dark:border-slate-800 dark:bg-slate-950"
                >
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                            Yeni lead
                        </h2>
                        <button
                            type="button"
                            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
                            @click="open = false"
                        >
                            ✕
                        </button>
                    </div>
                    <form class="mt-6 space-y-4" @submit.prevent="submitLead">
                        <div>
                            <InputLabel value="Başlık" />
                            <input
                                v-model="form.title"
                                type="text"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                            <InputError :message="form.errors.title" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <InputLabel value="E-posta" />
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                />
                                <InputError :message="form.errors.email" />
                            </div>
                            <div>
                                <InputLabel value="Telefon" />
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                />
                                <InputError :message="form.errors.phone" />
                            </div>
                        </div>
                        <div>
                            <InputLabel value="Kaynak" />
                            <input
                                v-model="form.source"
                                type="text"
                                placeholder="Google Ads, referans..."
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                            <InputError :message="form.errors.source" />
                        </div>
                        <div>
                            <InputLabel value="Tahmini değer" />
                            <input
                                v-model="form.estimated_value"
                                type="number"
                                step="0.01"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                            <InputError :message="form.errors.estimated_value" />
                        </div>
                        <div>
                            <InputLabel value="Aşama" />
                            <select
                                v-model="form.stage"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            >
                                <option v-for="s in stageOrder" :key="s" :value="s">
                                    {{ stageLabels[s] }}
                                </option>
                            </select>
                            <InputError :message="form.errors.stage" />
                        </div>
                        <div>
                            <InputLabel value="Temsilci" />
                            <select
                                v-model="form.assigned_user_id"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            >
                                <option value="">—</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">
                                    {{ u.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.assigned_user_id" />
                        </div>
                        <div>
                            <InputLabel value="Müşteri (opsiyonel)" />
                            <select
                                v-model="form.customer_id"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            >
                                <option value="">—</option>
                                <option v-for="c in customers" :key="c.id" :value="c.id">
                                    {{ c.company_name || c.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.customer_id" />
                        </div>
                        <div>
                            <InputLabel value="Sonraki takip" />
                            <input
                                v-model="form.next_follow_up_at"
                                type="datetime-local"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                            <InputError :message="form.errors.next_follow_up_at" />
                        </div>
                        <div>
                            <InputLabel value="Notlar" />
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                            <InputError :message="form.errors.notes" />
                        </div>
                        <PrimaryButton :disabled="form.processing" class="w-full rounded-xl">
                            Oluştur
                        </PrimaryButton>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
