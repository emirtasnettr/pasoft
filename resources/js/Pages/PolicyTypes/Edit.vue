<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { POLICY_TYPE_ICON_OPTIONS } from '@/lib/policyTypeIcons';
import { formatMoneyTr } from '@/lib/financeLabels';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    policyType: Object,
});

const form = useForm({
    name: props.policyType.name,
    slug: props.policyType.slug,
    description: props.policyType.description ?? '',
    category: props.policyType.category ?? '',
    color: props.policyType.color || '#6366f1',
    icon: props.policyType.icon || 'rectangle-stack',
    renewal_reminder_days: props.policyType.renewal_reminder_days ?? 30,
    is_active: !!props.policyType.is_active,
});

function submit() {
    form.put(route('policy-types.update', props.policyType.id));
}

const showAddModal = ref(false);
const editingId = ref(null);
/** @type {import('vue').Ref<Record<number, { rate: string; min: string }>>} */
const rowDrafts = ref({});

const addForm = useForm({
    insurance_company_id: '',
    commission_rate: '',
    min_commission: '',
});

watch(
    () => props.policyType.company_links,
    () => {
        editingId.value = null;
        rowDrafts.value = {};
    },
    { deep: true },
);

function openAdd() {
    addForm.reset();
    addForm.clearErrors();
    showAddModal.value = true;
}

function submitAdd() {
    addForm
        .transform((data) => ({
            policy_type_id: props.policyType.id,
            insurance_company_id: data.insurance_company_id,
            commission_rate: data.commission_rate === '' ? null : Number(data.commission_rate),
            min_commission: data.min_commission === '' ? null : Number(data.min_commission),
        }))
        .post(route('company-policy-types.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showAddModal.value = false;
            },
        });
}

function setDraftField(rowId, field, value) {
    const cur = rowDrafts.value[rowId] ?? { rate: '', min: '' };
    rowDrafts.value = { ...rowDrafts.value, [rowId]: { ...cur, [field]: value } };
}

function getDraft(row) {
    return rowDrafts.value[row.id] ?? { rate: '', min: '' };
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
            onFinish: () => {
                editingId.value = null;
                delete rowDrafts.value[row.id];
            },
        },
    );
}

function removeRow(row) {
    if (!confirm(`“${row.name}” firması ile eşleşmeyi kaldırmak istiyor musunuz?`)) {
        return;
    }
    router.delete(route('company-policy-types.destroy', row.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Poliçe türü düzenle" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Poliçe türü düzenle</h1>
                <p
                    v-if="policyType.policies_count > 0"
                    class="mt-1 text-sm text-amber-600 dark:text-amber-400"
                >
                    {{ policyType.policies_count }} poliçe bu türe bağlı; slug değişikliği mevcut kayıtları
                    etkilemez ancak raporlarda dikkatli olun.
                </p>
            </div>
        </template>

        <div class="mx-auto max-w-3xl space-y-8">
            <form
                class="space-y-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                @submit.prevent="submit"
            >
                <div>
                    <InputLabel value="Görünen ad" />
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>
                <div>
                    <InputLabel value="Slug" />
                    <input
                        v-model="form.slug"
                        type="text"
                        required
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 font-mono text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError class="mt-2" :message="form.errors.slug" />
                </div>
                <div>
                    <InputLabel value="Açıklama" />
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>
                <div>
                    <InputLabel value="Kategori" />
                    <input
                        v-model="form.category"
                        type="text"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError class="mt-2" :message="form.errors.category" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Renk" />
                        <div class="mt-1 flex items-center gap-2">
                            <input
                                v-model="form.color"
                                type="color"
                                class="h-10 w-14 cursor-pointer rounded-lg border border-slate-200 dark:border-slate-600"
                            />
                            <input
                                v-model="form.color"
                                type="text"
                                class="min-w-0 flex-1 rounded-xl border border-slate-200 px-3 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.color" />
                    </div>
                    <div>
                        <InputLabel value="İkon" />
                        <select
                            v-model="form.icon"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option v-for="opt in POLICY_TYPE_ICON_OPTIONS" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.icon" />
                    </div>
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
                    <InputError class="mt-2" :message="form.errors.renewal_reminder_days" />
                </div>
                <div class="flex items-center gap-2">
                    <input
                        id="pt-edit-active"
                        v-model="form.is_active"
                        type="checkbox"
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800"
                    />
                    <label for="pt-edit-active" class="text-sm text-slate-700 dark:text-slate-300">Aktif</label>
                </div>
                <div class="flex gap-3 pt-2">
                    <PrimaryButton :disabled="form.processing" class="rounded-xl">
                        Tür bilgisini güncelle
                    </PrimaryButton>
                    <Link
                        :href="route('policy-types.index')"
                        class="inline-flex items-center rounded-xl px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        Listeye dön
                    </Link>
                </div>
            </form>

            <section
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Sigorta firmaları ve komisyon</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Bu poliçe türü için hangi firmada hangi komisyon geçerli — satır bazında yönetilir.
                        </p>
                    </div>
                    <button
                        v-if="(policyType.attachable_companies || []).length"
                        type="button"
                        class="shrink-0 rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-500"
                        @click="openAdd"
                    >
                        Yeni firma
                    </button>
                </div>

                <div class="mt-4 overflow-hidden rounded-xl border border-slate-200/80 dark:border-slate-700">
                    <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-slate-800">
                        <thead class="bg-slate-50/80 dark:bg-slate-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                    Sigorta firması
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                    Komisyon %
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                    Min ₺
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                    Durum
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                                    İşlem
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-if="!(policyType.company_links || []).length">
                                <td colspan="5" class="px-4 py-10 text-center text-slate-500">
                                    Henüz firma eklenmemiş. Yukarıdan ekleyin.
                                </td>
                            </tr>
                            <tr
                                v-for="row in policyType.company_links"
                                :key="row.id"
                                class="transition hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900 dark:text-white">{{ row.name }}</div>
                                    <div v-if="row.code" class="font-mono text-xs text-slate-500">{{ row.code }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <input
                                        v-if="editingId === row.id"
                                        :value="getDraft(row).rate"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        class="w-24 rounded-lg border border-slate-200 px-2 py-1.5 dark:border-slate-600 dark:bg-slate-800"
                                        @input="setDraftField(row.id, 'rate', $event.target.value)"
                                    />
                                    <span v-else class="tabular-nums">{{ row.commission_rate }}%</span>
                                </td>
                                <td class="px-4 py-3">
                                    <input
                                        v-if="editingId === row.id"
                                        :value="getDraft(row).min"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="w-28 rounded-lg border border-slate-200 px-2 py-1.5 dark:border-slate-600 dark:bg-slate-800"
                                        @input="setDraftField(row.id, 'min', $event.target.value)"
                                    />
                                    <span v-else class="tabular-nums text-slate-600 dark:text-slate-400">
                                        {{ row.min_commission != null ? formatMoneyTr(row.min_commission) : '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                        :class="
                                            row.company_is_active
                                                ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300'
                                                : 'bg-slate-100 text-slate-600 dark:bg-slate-700/40'
                                        "
                                    >
                                        {{ row.company_is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <template v-if="editingId === row.id">
                                            <button
                                                type="button"
                                                class="text-xs font-semibold text-violet-600 hover:underline"
                                                @click="saveRow(row)"
                                            >
                                                Kaydet
                                            </button>
                                            <button
                                                type="button"
                                                class="text-xs text-slate-500"
                                                @click="editingId = null"
                                            >
                                                Vazgeç
                                            </button>
                                        </template>
                                        <template v-else>
                                            <button
                                                type="button"
                                                class="text-xs text-slate-600 hover:text-indigo-600"
                                                @click="startEdit(row)"
                                            >
                                                Düzenle
                                            </button>
                                            <button
                                                type="button"
                                                class="text-xs text-rose-600 hover:underline"
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
        </div>

        <div
            v-if="showAddModal"
            class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
            @click.self="showAddModal = false"
        >
            <div
                class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900"
                @click.stop
            >
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Firma ekle</h3>
                <form class="mt-4 space-y-4" @submit.prevent="submitAdd">
                    <div>
                        <InputLabel value="Sigorta firması" />
                        <select
                            v-model="addForm.insurance_company_id"
                            required
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option value="" disabled>Seçin</option>
                            <option v-for="c in policyType.attachable_companies" :key="c.id" :value="c.id">
                                {{ c.name }} {{ c.code ? `(${c.code})` : '' }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="addForm.errors.insurance_company_id" />
                    </div>
                    <div>
                        <InputLabel value="Komisyon (%)" />
                        <input
                            v-model="addForm.commission_rate"
                            type="number"
                            required
                            step="0.01"
                            min="0"
                            max="100"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError class="mt-2" :message="addForm.errors.commission_rate" />
                    </div>
                    <div>
                        <InputLabel value="Min komisyon (₺)" />
                        <input
                            v-model="addForm.min_commission"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError class="mt-2" :message="addForm.errors.min_commission" />
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
    </AuthenticatedLayout>
</template>
