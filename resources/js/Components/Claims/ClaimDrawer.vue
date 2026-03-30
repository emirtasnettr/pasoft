<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatClaimDateTime, formatClaimMoney, insightBadgeClass } from '@/lib/claimLabels';
import { Link, router, useForm } from '@inertiajs/vue3';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    claimDetail: { type: Object, default: null },
    filters: { type: Object, default: () => ({}) },
    statusOptions: { type: Array, default: () => [] },
});

const noteForm = useForm({ body: '' });
const fileForm = useForm({ file: null, category: '' });
const paidForm = useForm({ paid_amount: '' });

const statusDraft = ref('');

watch(
    () => props.claimDetail?.id,
    (id) => {
        noteForm.reset();
        noteForm.clearErrors();
        fileForm.reset();
        fileForm.clearErrors();
        paidForm.clearErrors();
        if (props.claimDetail) {
            statusDraft.value = props.claimDetail.status;
            paidForm.paid_amount =
                props.claimDetail.paid_amount != null ? String(props.claimDetail.paid_amount) : '';
        }
    },
    { immediate: true },
);

const open = computed(() => !!props.claimDetail);

function closeDrawer() {
    const next = { ...props.filters };
    delete next.claim;
    router.get(route('claims.index'), next, {
        replace: true,
        preserveScroll: true,
        preserveState: true,
        only: ['claimDetail'],
    });
}

function patchStatus() {
    if (!props.claimDetail) {
        return;
    }
    router.patch(
        route('claims.status', props.claimDetail.id),
        { status: statusDraft.value },
        {
            preserveScroll: true,
            only: ['claims', 'claimDetail', 'kpi'],
        },
    );
}

function savePaidAmount() {
    if (!props.claimDetail) {
        return;
    }
    paidForm
        .transform((data) => ({
            status: props.claimDetail.status,
            paid_amount: data.paid_amount === '' ? null : data.paid_amount,
        }))
        .patch(route('claims.status', props.claimDetail.id), {
            preserveScroll: true,
            only: ['claims', 'claimDetail', 'kpi'],
            onSuccess: () => paidForm.clearErrors(),
        });
}

function submitNote() {
    if (!props.claimDetail) {
        return;
    }
    noteForm.post(route('claims.notes.store', props.claimDetail.id), {
        preserveScroll: true,
        only: ['claimDetail'],
        onSuccess: () => noteForm.reset(),
    });
}

function onFilePick(e) {
    const f = e.target.files?.[0];
    fileForm.file = f ?? null;
}

function submitFile() {
    if (!props.claimDetail || !fileForm.file) {
        return;
    }
    fileForm.post(route('claims.documents.store', props.claimDetail.id), {
        preserveScroll: true,
        forceFormData: true,
        only: ['claimDetail'],
        onSuccess: () => {
            fileForm.reset();
            const el = document.getElementById('claim-doc-input');
            if (el) {
                el.value = '';
            }
        },
    });
}

function removeDocument(doc) {
    if (!confirm('Dosyayı kaldırmak istiyor musunuz?')) {
        return;
    }
    router.delete(route('claims.documents.destroy', doc.id), {
        preserveScroll: true,
        only: ['claimDetail'],
    });
}

function timelineDotClass(action) {
    if (action === 'opened' || action === 'created') {
        return 'bg-sky-500';
    }
    if (action === 'status_changed') {
        return 'bg-violet-500';
    }
    if (action === 'document_added' || action === 'document_removed') {
        return 'bg-amber-500';
    }
    if (action === 'note_added') {
        return 'bg-emerald-500';
    }
    return 'bg-slate-400';
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
                        v-if="open && claimDetail"
                        class="flex h-full w-full max-w-xl flex-col border-l border-slate-200/90 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-950"
                        @click.stop
                    >
                        <header
                            class="flex shrink-0 items-start justify-between gap-3 border-b border-slate-100 px-6 py-5 dark:border-slate-800"
                        >
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset"
                                        :class="claimDetail.status_badge_class"
                                    >
                                        {{ claimDetail.status_label }}
                                    </span>
                                    <span
                                        v-for="(b, i) in claimDetail.insight_badges"
                                        :key="i"
                                        class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-semibold ring-1 ring-inset"
                                        :class="insightBadgeClass(b.type)"
                                    >
                                        {{ b.text }}
                                    </span>
                                </div>
                                <h2 class="mt-2 text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                                    {{ claimDetail.file_number }}
                                </h2>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    {{ claimDetail.claim_type || 'Hasar dosyası' }} · #{{ claimDetail.id }}
                                </p>
                            </div>
                            <button
                                type="button"
                                class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 active:scale-95 dark:hover:bg-slate-800"
                                aria-label="Kapat"
                                @click="closeDrawer"
                            >
                                <XMarkIcon class="h-6 w-6" />
                            </button>
                        </header>

                        <div class="flex-1 overflow-y-auto px-6 py-6">
                            <section class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Genel bilgiler
                                </p>
                                <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                                    <div>
                                        <dt class="text-slate-500">Müşteri</dt>
                                        <dd class="font-medium text-slate-900 dark:text-white">
                                            <Link
                                                v-if="claimDetail.customer"
                                                :href="route('customers.show', claimDetail.customer.id)"
                                                class="text-indigo-600 hover:underline dark:text-indigo-400"
                                            >
                                                {{ claimDetail.customer.display_name }}
                                            </Link>
                                            <span v-else>—</span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-500">Poliçe</dt>
                                        <dd class="font-medium text-slate-900 dark:text-white">
                                            <Link
                                                v-if="claimDetail.policy"
                                                :href="route('policies.show', claimDetail.policy.id)"
                                                class="text-indigo-600 hover:underline dark:text-indigo-400"
                                            >
                                                {{ claimDetail.policy.policy_number }}
                                            </Link>
                                            <span v-else>—</span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-500">Sigorta şirketi</dt>
                                        <dd class="font-medium text-slate-900 dark:text-white">
                                            {{ claimDetail.insurance_company?.name ?? '—' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-500">Son işlem</dt>
                                        <dd class="text-slate-700 dark:text-slate-300">
                                            {{ formatClaimDateTime(claimDetail.last_activity_at) }}
                                        </dd>
                                    </div>
                                </dl>
                            </section>

                            <section class="mt-8">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Süreç zaman çizelgesi</h3>
                                <ul class="relative mt-4 space-y-0 border-l border-slate-200 pl-4 dark:border-slate-700">
                                    <li
                                        v-for="(ev, ti) in claimDetail.timeline"
                                        :key="'tl-' + ti + '-' + ev.created_at"
                                        class="relative pb-6 last:pb-0"
                                    >
                                        <span
                                            class="absolute -left-[21px] mt-1.5 h-2.5 w-2.5 rounded-full ring-4 ring-white dark:ring-slate-950"
                                            :class="timelineDotClass(ev.action)"
                                        />
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                            {{ ev.label }}
                                        </p>
                                        <p
                                            v-if="ev.description"
                                            class="mt-0.5 text-sm text-slate-600 dark:text-slate-400"
                                        >
                                            {{ ev.description }}
                                        </p>
                                        <p v-if="ev.meta?.from && ev.meta?.to" class="mt-1 text-xs text-slate-500">
                                            {{ ev.meta.from }} → {{ ev.meta.to }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ formatClaimDateTime(ev.created_at) }}
                                            <span v-if="ev.user"> · {{ ev.user.name }}</span>
                                        </p>
                                    </li>
                                </ul>
                            </section>

                            <section class="mt-8">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Ödeme</h3>
                                <div class="mt-3 grid gap-3 rounded-2xl border border-slate-100 bg-white p-4 dark:border-slate-800 dark:bg-slate-900/50 sm:grid-cols-3">
                                    <div>
                                        <p class="text-xs text-slate-500">Hasar tutarı</p>
                                        <p class="mt-1 font-semibold tabular-nums text-slate-900 dark:text-white">
                                            {{ formatClaimMoney(claimDetail.amount) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500">Ödenen</p>
                                        <p class="mt-1 font-semibold tabular-nums text-emerald-700 dark:text-emerald-300">
                                            {{ formatClaimMoney(claimDetail.paid_amount) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500">Bekleyen</p>
                                        <p class="mt-1 font-semibold tabular-nums text-amber-700 dark:text-amber-300">
                                            {{ formatClaimMoney(claimDetail.pending_amount) }}
                                        </p>
                                    </div>
                                </div>
                                <form class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end" @submit.prevent="savePaidAmount">
                                    <div class="flex-1">
                                        <InputLabel value="Ödenen tutarı güncelle" />
                                        <input
                                            v-model="paidForm.paid_amount"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                        />
                                        <InputError :message="paidForm.errors.paid_amount" />
                                    </div>
                                    <PrimaryButton type="submit" class="rounded-xl" :disabled="paidForm.processing">
                                        Kaydet
                                    </PrimaryButton>
                                </form>
                            </section>

                            <section class="mt-8">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Durum güncelle</h3>
                                <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-end">
                                    <div class="flex-1">
                                        <select
                                            v-model="statusDraft"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                        >
                                            <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                                                {{ opt.label }}
                                            </option>
                                        </select>
                                    </div>
                                    <PrimaryButton type="button" class="rounded-xl" @click="patchStatus">
                                        Uygula
                                    </PrimaryButton>
                                </div>
                            </section>

                            <section class="mt-8">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Evraklar</h3>
                                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-end">
                                    <div class="flex-1">
                                        <InputLabel value="Kategori (opsiyonel)" />
                                        <input
                                            v-model="fileForm.category"
                                            type="text"
                                            placeholder="örn. ekspertiz"
                                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                        />
                                    </div>
                                    <div class="flex-1">
                                        <InputLabel value="Dosya" />
                                        <input
                                            id="claim-doc-input"
                                            type="file"
                                            class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-slate-400 dark:file:bg-slate-800 dark:file:text-indigo-300"
                                            @change="onFilePick"
                                        />
                                        <InputError :message="fileForm.errors.file" />
                                    </div>
                                    <PrimaryButton
                                        type="button"
                                        class="rounded-xl"
                                        :disabled="fileForm.processing || !fileForm.file"
                                        @click="submitFile"
                                    >
                                        Yükle
                                    </PrimaryButton>
                                </div>
                                <ul class="mt-4 space-y-2">
                                    <li
                                        v-for="d in claimDetail.documents"
                                        :key="d.id"
                                        class="flex items-center justify-between gap-2 rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-2 text-sm dark:border-slate-800 dark:bg-slate-900/50"
                                    >
                                        <a
                                            :href="d.url"
                                            target="_blank"
                                            rel="noopener"
                                            class="min-w-0 flex-1 truncate font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                                        >
                                            {{ d.original_name }}
                                        </a>
                                        <button
                                            type="button"
                                            class="shrink-0 text-xs font-semibold text-rose-600 hover:underline dark:text-rose-400"
                                            @click="removeDocument(d)"
                                        >
                                            Kaldır
                                        </button>
                                    </li>
                                    <li
                                        v-if="!claimDetail.documents?.length"
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Henüz evrak yok.
                                    </li>
                                </ul>
                            </section>

                            <section class="mt-8">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Notlar</h3>
                                <form class="mt-3 space-y-2" @submit.prevent="submitNote">
                                    <textarea
                                        v-model="noteForm.body"
                                        rows="3"
                                        placeholder="Not yazın…"
                                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                    <InputError :message="noteForm.errors.body" />
                                    <PrimaryButton type="submit" class="rounded-xl" :disabled="noteForm.processing">
                                        Not ekle
                                    </PrimaryButton>
                                </form>
                                <ul class="mt-6 space-y-4">
                                    <li
                                        v-for="n in claimDetail.notes"
                                        :key="n.id"
                                        class="relative border-l-2 border-indigo-200 pl-4 dark:border-indigo-800"
                                    >
                                        <p class="text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                                            {{ n.body }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ n.user?.name }} · {{ formatClaimDateTime(n.created_at) }}
                                        </p>
                                    </li>
                                    <li
                                        v-if="!claimDetail.notes?.length"
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Henüz not yok.
                                    </li>
                                </ul>
                            </section>

                            <section class="mt-8 rounded-2xl border border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Aksiyonlar</h3>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <a
                                        v-if="claimDetail.customer?.phone"
                                        :href="'tel:' + claimDetail.customer.phone"
                                        class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500"
                                    >
                                        Ara
                                    </a>
                                    <SecondaryButton
                                        v-else
                                        type="button"
                                        disabled
                                        class="rounded-xl opacity-50"
                                    >
                                        Telefon yok
                                    </SecondaryButton>
                                    <Link
                                        :href="route('claims.edit', claimDetail.id)"
                                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:hover:bg-slate-700"
                                    >
                                        Tam düzenleme
                                    </Link>
                                </div>
                            </section>
                        </div>
                    </aside>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
