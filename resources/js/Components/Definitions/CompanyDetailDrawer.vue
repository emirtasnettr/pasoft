<script setup>
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatMoneyTr } from '@/lib/financeLabels';
import { Link, router, useForm } from '@inertiajs/vue3';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { computed, ref, watch } from 'vue';

function setDraftField(rowId, field, value) {
    const cur = rowDrafts.value[rowId] ?? { rate: '', min: '' };
    rowDrafts.value = { ...rowDrafts.value, [rowId]: { ...cur, [field]: value } };
}

const props = defineProps({
    companyDetail: { type: Object, default: null },
    filters: { type: Object, default: () => ({}) },
});

const open = computed(() => !!props.companyDetail);

const showAddModal = ref(false);
const editingId = ref(null);
/** @type {import('vue').Ref<Record<number, { rate: string; min: string }>>} */
const rowDrafts = ref({});

const addForm = useForm({
    policy_type_id: '',
    commission_rate: '',
    min_commission: '',
});

watch(
    () => props.companyDetail?.id,
    () => {
        addForm.reset();
        addForm.clearErrors();
        showAddModal.value = false;
        editingId.value = null;
        rowDrafts.value = {};
    },
);

function closeDrawer() {
    const next = { ...props.filters };
    delete next.company;
    router.get(route('insurance-companies.index'), next, {
        replace: true,
        preserveScroll: true,
        preserveState: true,
        only: ['companyDetail'],
    });
}

function openAdd() {
    addForm.reset();
    addForm.clearErrors();
    showAddModal.value = true;
}

function submitAdd() {
    if (!props.companyDetail) {
        return;
    }
    addForm
        .transform((data) => ({
            insurance_company_id: props.companyDetail.id,
            policy_type_id: data.policy_type_id,
            commission_rate: data.commission_rate === '' ? null : Number(data.commission_rate),
            min_commission: data.min_commission === '' ? null : Number(data.min_commission),
        }))
        .post(route('company-policy-types.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showAddModal.value = false;
            },
            only: ['companyDetail', 'companies'],
        });
}

function saveRow(row) {
    const d = rowDrafts.value[row.id];
    if (!d) {
        return;
    }
    router.patch(
        route('company-policy-types.update', row.id),
        {
            commission_rate: d.rate === '' ? 0 : Number(d.rate),
            min_commission: d.min === '' ? null : Number(d.min),
        },
        {
            preserveScroll: true,
            only: ['companyDetail', 'companies'],
            onFinish: () => {
                editingId.value = null;
                delete rowDrafts.value[row.id];
            },
        },
    );
}

function startEdit(row) {
    editingId.value = row.id;
    rowDrafts.value = {
        ...rowDrafts.value,
        [row.id]: {
            rate: String(row.commission_rate),
            min: row.min_commission != null ? String(row.min_commission) : '',
        },
    };
}

function removeRow(row) {
    if (!confirm(`“${row.name}” türü ile eşleşmeyi kaldırmak istiyor musunuz?`)) {
        return;
    }
    router.delete(route('company-policy-types.destroy', row.id), {
        preserveScroll: true,
        only: ['companyDetail', 'companies'],
    });
}

function getDraft(row) {
    return rowDrafts.value[row.id] ?? { rate: '', min: '' };
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-300 ease-out"
            leave-active-class="transition-opacity duration-200 ease-in"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-[70] flex justify-end bg-slate-900/50 backdrop-blur-md"
                @click.self="closeDrawer"
            >
                <Transition
                    enter-active-class="transition-transform duration-300 ease-out"
                    leave-active-class="transition-transform duration-200 ease-in"
                    enter-from-class="translate-x-full"
                    leave-to-class="translate-x-full"
                >
                    <aside
                        v-if="open && companyDetail"
                        class="flex h-full w-full max-w-xl flex-col border-l border-slate-200/90 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-950"
                        @click.stop
                    >
                        <header
                            class="flex shrink-0 items-start justify-between gap-3 border-b border-slate-100 px-6 py-5 dark:border-slate-800"
                        >
                            <div class="flex min-w-0 flex-1 gap-4">
                                <div
                                    class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800"
                                >
                                    <img
                                        v-if="companyDetail.logo_url"
                                        :src="companyDetail.logo_url"
                                        alt=""
                                        class="h-full w-full object-contain p-1"
                                    />
                                    <span v-else class="text-lg font-bold text-slate-400">
                                        {{ companyDetail.name?.slice(0, 1) }}
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                                        {{ companyDetail.name }}
                                    </h2>
                                    <p class="mt-1 font-mono text-sm text-slate-500">
                                        {{ companyDetail.code || '—' }}
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 dark:hover:bg-slate-800"
                                aria-label="Kapat"
                                @click="closeDrawer"
                            >
                                <XMarkIcon class="h-6 w-6" />
                            </button>
                        </header>

                        <div class="flex-1 overflow-y-auto px-6 py-6">
                            <section
                                class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-900/40"
                            >
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Genel bilgiler
                                </p>
                                <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                                    <div>
                                        <dt class="text-slate-500">Yetkili</dt>
                                        <dd class="font-medium text-slate-900 dark:text-white">
                                            {{ companyDetail.contact_person || '—' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-500">Telefon</dt>
                                        <dd>{{ companyDetail.contact_phone || '—' }}</dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-slate-500">E-posta</dt>
                                        <dd>{{ companyDetail.contact_email || '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-500">Durum</dt>
                                        <dd>
                                            <span
                                                class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                                :class="
                                                    companyDetail.is_active
                                                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300'
                                                        : 'bg-slate-100 text-slate-600 dark:bg-slate-700/40 dark:text-slate-300'
                                                "
                                            >
                                                {{ companyDetail.is_active ? 'Aktif' : 'Pasif' }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </section>

                            <section class="mt-8">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                            Desteklenen poliçe türleri
                                        </h3>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            Komisyon her firma + tür çifti için tanımlanır.
                                        </p>
                                    </div>
                                    <button
                                        v-if="(companyDetail.attachable_policy_types || []).length"
                                        type="button"
                                        class="shrink-0 rounded-xl bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                                        @click="openAdd"
                                    >
                                        Yeni tür
                                    </button>
                                </div>

                                <div
                                    class="mt-3 overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800"
                                >
                                    <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-slate-800">
                                        <thead class="bg-slate-50/80 dark:bg-slate-800/50">
                                            <tr>
                                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500">
                                                    Poliçe türü
                                                </th>
                                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500">
                                                    Komisyon %
                                                </th>
                                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500">
                                                    Min ₺
                                                </th>
                                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500">
                                                    Durum
                                                </th>
                                                <th class="px-3 py-2.5 text-right text-xs font-semibold text-slate-500">
                                                    İşlem
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                            <tr v-if="!(companyDetail.supported_policy_types || []).length">
                                                <td colspan="5" class="px-3 py-8 text-center text-slate-500">
                                                    Henüz tür eklenmemiş.
                                                </td>
                                            </tr>
                                            <tr
                                                v-for="row in companyDetail.supported_policy_types"
                                                :key="row.id"
                                                class="transition hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                                            >
                                                <td class="px-3 py-2.5 font-medium text-slate-900 dark:text-white">
                                                    {{ row.name }}
                                                </td>
                                                <td class="px-3 py-2.5">
                                                    <input
                                                        v-if="editingId === row.id"
                                                        :value="getDraft(row).rate"
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        max="100"
                                                        class="w-20 rounded-lg border border-slate-200 px-2 py-1 text-sm dark:border-slate-600 dark:bg-slate-800"
                                                        @input="setDraftField(row.id, 'rate', $event.target.value)"
                                                    />
                                                    <span v-else class="tabular-nums">{{ row.commission_rate }}%</span>
                                                </td>
                                                <td class="px-3 py-2.5">
                                                    <input
                                                        v-if="editingId === row.id"
                                                        :value="getDraft(row).min"
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        placeholder="—"
                                                        class="w-24 rounded-lg border border-slate-200 px-2 py-1 text-sm dark:border-slate-600 dark:bg-slate-800"
                                                        @input="setDraftField(row.id, 'min', $event.target.value)"
                                                    />
                                                    <span v-else class="tabular-nums text-slate-600 dark:text-slate-400">
                                                        {{ row.min_commission != null ? formatMoneyTr(row.min_commission) : '—' }}
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2.5">
                                                    <span
                                                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                                        :class="
                                                            row.type_is_active
                                                                ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300'
                                                                : 'bg-slate-100 text-slate-600 dark:bg-slate-700/40'
                                                        "
                                                    >
                                                        {{ row.type_is_active ? 'Aktif' : 'Pasif' }}
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2.5 text-right">
                                                    <div class="flex justify-end gap-1">
                                                        <template v-if="editingId === row.id">
                                                            <button
                                                                type="button"
                                                                class="rounded-lg px-2 py-1 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/40"
                                                                @click="saveRow(row)"
                                                            >
                                                                Kaydet
                                                            </button>
                                                            <button
                                                                type="button"
                                                                class="rounded-lg px-2 py-1 text-xs text-slate-500"
                                                                @click="editingId = null"
                                                            >
                                                                Vazgeç
                                                            </button>
                                                        </template>
                                                        <template v-else>
                                                            <button
                                                                type="button"
                                                                class="rounded-lg px-2 py-1 text-xs text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800"
                                                                @click="startEdit(row)"
                                                            >
                                                                Düzenle
                                                            </button>
                                                            <button
                                                                type="button"
                                                                class="rounded-lg px-2 py-1 text-xs text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40"
                                                                @click="removeRow(row)"
                                                            >
                                                                Sil
                                                            </button>
                                                        </template>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </section>

                            <section class="mt-8">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Performans</h3>
                                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                                    <div
                                        class="rounded-xl border border-slate-100 bg-white p-3 dark:border-slate-800 dark:bg-slate-900/50"
                                    >
                                        <p class="text-xs text-slate-500">Poliçe</p>
                                        <p class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                                            {{ companyDetail.performance.policies_count }}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-xl border border-slate-100 bg-white p-3 dark:border-slate-800 dark:bg-slate-900/50"
                                    >
                                        <p class="text-xs text-slate-500">Prim ciro</p>
                                        <p class="mt-1 text-lg font-bold tabular-nums text-slate-900 dark:text-white">
                                            {{ formatMoneyTr(companyDetail.performance.premium_volume) }}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-xl border border-slate-100 bg-white p-3 dark:border-slate-800 dark:bg-slate-900/50"
                                    >
                                        <p class="text-xs text-slate-500">Ort. komisyon</p>
                                        <p class="mt-1 text-lg font-bold text-violet-700 dark:text-violet-300">
                                            {{
                                                companyDetail.performance.avg_commission_percent != null
                                                    ? `${companyDetail.performance.avg_commission_percent} %`
                                                    : '—'
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </section>

                            <section class="mt-8">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Entegrasyon</h3>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                        :class="
                                            companyDetail.api_enabled
                                                ? 'bg-sky-100 text-sky-900 ring-sky-600/20 dark:bg-sky-950/50 dark:text-sky-200'
                                                : 'bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-300'
                                        "
                                    >
                                        API {{ companyDetail.api_enabled ? 'aktif' : 'kapalı' }}
                                    </span>
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                        :class="
                                            companyDetail.quote_integration_enabled
                                                ? 'bg-emerald-100 text-emerald-900 ring-emerald-600/20 dark:bg-emerald-950/50 dark:text-emerald-200'
                                                : 'bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-300'
                                        "
                                    >
                                        Teklif entegrasyonu
                                        {{ companyDetail.quote_integration_enabled ? 'var' : 'yok' }}
                                    </span>
                                </div>
                            </section>

                            <div class="mt-8 flex flex-wrap gap-2">
                                <Link
                                    :href="route('insurance-companies.edit', companyDetail.id)"
                                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:hover:bg-slate-700"
                                >
                                    Tam düzenleme
                                </Link>
                                <SecondaryButton type="button" class="rounded-xl" @click="closeDrawer">
                                    Kapat
                                </SecondaryButton>
                            </div>
                        </div>
                    </aside>
                </Transition>
            </div>
        </Transition>

        <!-- Add policy type modal -->
        <div
            v-if="showAddModal && companyDetail"
            class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
            @click.self="showAddModal = false"
        >
            <div
                class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900"
                @click.stop
            >
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Poliçe türü ekle</h3>
                <form class="mt-4 space-y-4" @submit.prevent="submitAdd">
                    <div>
                        <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Tür</label>
                        <select
                            v-model="addForm.policy_type_id"
                            required
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        >
                            <option value="" disabled>Seçin</option>
                            <option v-for="t in companyDetail.attachable_policy_types" :key="t.id" :value="t.id">
                                {{ t.name }}
                            </option>
                        </select>
                        <InputError class="mt-1" :message="addForm.errors.policy_type_id" />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Komisyon (%)</label>
                        <input
                            v-model="addForm.commission_rate"
                            type="number"
                            required
                            step="0.01"
                            min="0"
                            max="100"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError class="mt-1" :message="addForm.errors.commission_rate" />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Min komisyon (₺)</label>
                        <input
                            v-model="addForm.min_commission"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError class="mt-1" :message="addForm.errors.min_commission" />
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <SecondaryButton type="button" class="rounded-xl" @click="showAddModal = false">
                            Vazgeç
                        </SecondaryButton>
                        <PrimaryButton type="submit" class="rounded-xl" :disabled="addForm.processing">
                            Ekle
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>
