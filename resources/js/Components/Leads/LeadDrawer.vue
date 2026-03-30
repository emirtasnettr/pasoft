<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import {
    STAGE_LABELS,
    STAGE_ORDER,
    TEMP_EMOJI,
    TEMP_LABELS,
    activityLeadLabel,
    digitsPhone,
    stageBadgeClass,
    tempBadgeClass,
    waLink,
} from '@/lib/leadLabels';
import { Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowTopRightOnSquareIcon,
    ClipboardDocumentListIcon,
    DevicePhoneMobileIcon,
    PhoneIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    leadDetail: { type: Object, default: null },
    filters: { type: Object, default: () => ({}) },
    users: { type: Array, default: () => [] },
    /** 'index' | 'kanban' — kısmi yenileme anahtarları için */
    context: { type: String, default: 'index' },
});

const noteForm = useForm({ body: '' });
const taskForm = useForm({
    title: '',
    description: '',
    type: 'call',
    due_at: '',
    remind_at: '',
    status: 'pending',
    priority: 'medium',
    assigned_user_id: '',
    customer_id: '',
    lead_id: '',
    policy_id: '',
});

const assignedUserId = ref('');

const open = computed(() => !!props.leadDetail);

const reloadOnly = computed(() => {
    if (props.context === 'kanban') {
        return ['leadDetail', 'filters', 'columns', 'kpi', 'flash'];
    }
    return ['leadDetail', 'filters', 'leads', 'kpi', 'flash'];
});

const listRoute = computed(() =>
    props.context === 'kanban' ? 'leads.kanban' : 'leads.index',
);

watch(
    () => props.leadDetail?.id,
    () => {
        noteForm.reset();
        noteForm.clearErrors();
        if (props.leadDetail) {
            assignedUserId.value =
                props.leadDetail.assigned_user_id != null
                    ? String(props.leadDetail.assigned_user_id)
                    : '';
            taskForm.lead_id = props.leadDetail.id;
            taskForm.title = `${props.leadDetail.title} — takip`;
            taskForm.description = '';
            taskForm.type = 'call';
            taskForm.customer_id = props.leadDetail.customer_id ?? '';
            taskForm.assigned_user_id = props.leadDetail.assigned_user_id ?? '';
            taskForm.clearErrors();
        }
    },
);

function closeDrawer() {
    const next = { ...props.filters };
    delete next.lead;
    router.get(route(listRoute.value), next, {
        replace: true,
        preserveScroll: true,
        preserveState: true,
        only: ['leadDetail', 'filters', 'flash'],
    });
}

function patchStage(stage) {
    if (!props.leadDetail) {
        return;
    }
    router.patch(
        route('leads.stage', props.leadDetail.id),
        { stage },
        {
            preserveScroll: true,
            only: reloadOnly.value,
        },
    );
}

function postTouch() {
    if (!props.leadDetail) {
        return;
    }
    router.post(
        route('leads.touch', props.leadDetail.id),
        {},
        {
            preserveScroll: true,
            only: reloadOnly.value,
        },
    );
}

function persistAssign() {
    const l = props.leadDetail;
    if (!l) {
        return;
    }
    router.put(
        route('leads.update', l.id),
        {
            title: l.title,
            email: l.email ?? null,
            phone: l.phone ?? null,
            source: l.source ?? null,
            stage: l.stage,
            estimated_value: l.estimated_value ?? null,
            assigned_user_id: assignedUserId.value || null,
            customer_id: l.customer_id ?? null,
            next_follow_up_at: l.next_follow_up_at
                ? l.next_follow_up_at.slice(0, 16)
                : null,
            notes: l.crm_notes ?? null,
        },
        {
            preserveScroll: true,
            only: reloadOnly.value,
        },
    );
}

function submitNote() {
    if (!props.leadDetail) {
        return;
    }
    noteForm.post(route('leads.notes.store', props.leadDetail.id), {
        preserveScroll: true,
        only: reloadOnly.value,
        onSuccess: () => noteForm.reset(),
    });
}

function submitTask() {
    taskForm
        .transform((data) => ({
            ...data,
            assigned_user_id: data.assigned_user_id || null,
            customer_id: data.customer_id || null,
            lead_id: data.lead_id || null,
            policy_id: data.policy_id || null,
        }))
        .post(route('tasks.store'), {
            preserveScroll: true,
            only: reloadOnly.value,
        });
}

function formatTs(iso) {
    if (!iso) {
        return '—';
    }
    return iso.replace('T', ' ').slice(0, 16);
}

function initials(name) {
    if (!name) {
        return '?';
    }
    const p = name.trim().split(/\s+/);
    if (p.length === 1) {
        return p[0].slice(0, 2).toUpperCase();
    }
    return (p[0][0] + p[p.length - 1][0]).toUpperCase();
}

const policyCreateUrl = computed(() => {
    if (!props.leadDetail) {
        return '#';
    }
    return route('policies.create', { lead_id: props.leadDetail.id });
});
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
                        v-if="open && leadDetail"
                        class="flex h-full w-full max-w-lg flex-col rounded-l-2xl border-l border-slate-200/90 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-950"
                        @click.stop
                    >
                        <header
                            class="flex shrink-0 items-start justify-between gap-3 border-b border-slate-100 px-6 py-5 dark:border-slate-800"
                        >
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide ring-1 ring-inset"
                                        :class="stageBadgeClass(leadDetail.stage)"
                                    >
                                        {{ STAGE_LABELS[leadDetail.stage] ?? leadDetail.stage }}
                                    </span>
                                    <span
                                        class="inline-flex items-center gap-1 rounded-lg px-2 py-0.5 text-xs font-bold"
                                        :class="tempBadgeClass(leadDetail.temperature)"
                                    >
                                        <span>{{ TEMP_EMOJI[leadDetail.temperature] ?? '🌤️' }}</span>
                                        {{ TEMP_LABELS[leadDetail.temperature] ?? leadDetail.temperature }}
                                    </span>
                                </div>
                                <h2
                                    class="mt-2 text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                                >
                                    {{ leadDetail.title }}
                                </h2>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    #{{ leadDetail.id }} · Son aktivite:
                                    {{ formatTs(leadDetail.last_activity_at) }}
                                </p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span
                                        v-for="ins in leadDetail.insight_badges || []"
                                        :key="ins.text"
                                        class="inline-flex max-w-full truncate rounded-xl px-2.5 py-1 text-[11px] font-semibold"
                                        :class="
                                            ins.type === 'hot'
                                                ? 'bg-orange-100 text-orange-900 dark:bg-orange-950/40 dark:text-orange-200'
                                                : ins.type === 'stale'
                                                  ? 'bg-amber-100 text-amber-900 dark:bg-amber-950/40 dark:text-amber-200'
                                                  : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
                                        "
                                    >
                                        {{ ins.text }}
                                    </span>
                                </div>
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
                            <section class="space-y-5">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Lead skoru
                                    </p>
                                    <div class="mt-2 flex items-center gap-3">
                                        <div
                                            class="h-3 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                                        >
                                            <div
                                                class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-500 transition-all duration-500"
                                                :style="{ width: `${leadDetail.score ?? 0}%` }"
                                            />
                                        </div>
                                        <span
                                            class="text-sm font-bold tabular-nums text-slate-800 dark:text-slate-200"
                                        >
                                            {{ leadDetail.score ?? 0 }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Son aktivite ve aşamaya göre otomatik hesaplanır.
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <a
                                        v-if="leadDetail.phone"
                                        :href="'tel:' + digitsPhone(leadDetail.phone)"
                                        class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100"
                                    >
                                        <PhoneIcon class="h-4 w-4" />
                                        Ara
                                    </a>
                                    <a
                                        v-if="leadDetail.phone"
                                        :href="waLink(leadDetail.phone)"
                                        target="_blank"
                                        rel="noopener"
                                        class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-800 hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200"
                                    >
                                        <DevicePhoneMobileIcon class="h-4 w-4" />
                                        WhatsApp
                                    </a>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800"
                                        @click="postTouch"
                                    >
                                        Temas kaydet
                                    </button>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                            Telefon
                                        </p>
                                        <p class="mt-1 text-sm font-medium text-slate-800 dark:text-slate-100">
                                            <a
                                                v-if="leadDetail.phone"
                                                :href="'tel:' + digitsPhone(leadDetail.phone)"
                                                class="text-indigo-600 hover:underline dark:text-indigo-400"
                                            >
                                                {{ leadDetail.phone }}
                                            </a>
                                            <span v-else class="text-slate-400">—</span>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                            E-posta
                                        </p>
                                        <p
                                            class="mt-1 truncate text-sm font-medium text-slate-800 dark:text-slate-100"
                                        >
                                            {{ leadDetail.email || '—' }}
                                        </p>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                            Kaynak
                                        </p>
                                        <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                                            {{ leadDetail.source || '—' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label
                                            class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                                        >
                                            Aşama
                                        </label>
                                        <select
                                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                            :value="leadDetail.stage"
                                            @change="patchStage($event.target.value)"
                                        >
                                            <option v-for="s in STAGE_ORDER" :key="s" :value="s">
                                                {{ STAGE_LABELS[s] }}
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                                        >
                                            Temsilci
                                        </label>
                                        <select
                                            v-model="assignedUserId"
                                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                            @change="persistAssign"
                                        >
                                            <option value="">Atanmadı</option>
                                            <option v-for="u in users" :key="u.id" :value="String(u.id)">
                                                {{ u.name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div
                                    v-if="leadDetail.assigned_user"
                                    class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-900/40"
                                >
                                    <span
                                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white shadow"
                                    >
                                        {{ initials(leadDetail.assigned_user.name) }}
                                    </span>
                                    <div>
                                        <p class="text-xs font-semibold uppercase text-slate-400">
                                            Atanan temsilci
                                        </p>
                                        <p class="font-semibold text-slate-900 dark:text-white">
                                            {{ leadDetail.assigned_user.name }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2 border-t border-slate-100 pt-6 dark:border-slate-800">
                                    <Link
                                        :href="policyCreateUrl"
                                        class="inline-flex items-center gap-2 rounded-xl border border-violet-200 bg-violet-50 px-4 py-2.5 text-sm font-semibold text-violet-900 hover:bg-violet-100 dark:border-violet-900 dark:bg-violet-950/40 dark:text-violet-100"
                                    >
                                        <ClipboardDocumentListIcon class="h-4 w-4" />
                                        Teklif oluştur
                                    </Link>
                                    <Link
                                        :href="route('tasks.index')"
                                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800"
                                    >
                                        <ArrowTopRightOnSquareIcon class="h-4 w-4" />
                                        Görevlere git
                                    </Link>
                                </div>

                                <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-900/30">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                        Hızlı görev
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Bu lead ile ilişkili yeni görev oluşturur.
                                    </p>
                                    <form class="mt-4 space-y-3" @submit.prevent="submitTask">
                                        <div>
                                            <InputLabel value="Başlık" />
                                            <input
                                                v-model="taskForm.title"
                                                type="text"
                                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                            />
                                            <InputError :message="taskForm.errors.title" />
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <InputLabel value="Tür" />
                                                <select
                                                    v-model="taskForm.type"
                                                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                                >
                                                    <option value="call">Arama</option>
                                                    <option value="visit">Ziyaret</option>
                                                    <option value="proposal">Teklif</option>
                                                    <option value="follow_up">Takip</option>
                                                    <option value="other">Diğer</option>
                                                </select>
                                            </div>
                                            <div>
                                                <InputLabel value="Öncelik" />
                                                <select
                                                    v-model="taskForm.priority"
                                                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                                >
                                                    <option value="low">Düşük</option>
                                                    <option value="medium">Orta</option>
                                                    <option value="high">Yüksek</option>
                                                </select>
                                            </div>
                                        </div>
                                        <PrimaryButton
                                            type="submit"
                                            class="w-full rounded-xl"
                                            :disabled="taskForm.processing"
                                        >
                                            Göreve dönüştür
                                        </PrimaryButton>
                                    </form>
                                </div>
                            </section>

                            <section class="mt-10 border-t border-slate-100 pt-8 dark:border-slate-800">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Notlar</h3>
                                <form class="mt-4 space-y-2" @submit.prevent="submitNote">
                                    <textarea
                                        v-model="noteForm.body"
                                        rows="3"
                                        placeholder="Not yazın…"
                                        class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                    <InputError :message="noteForm.errors.body" />
                                    <PrimaryButton
                                        type="submit"
                                        class="rounded-xl"
                                        :disabled="noteForm.processing"
                                    >
                                        Not ekle
                                    </PrimaryButton>
                                </form>
                                <ul class="mt-6 space-y-4">
                                    <li
                                        v-for="n in leadDetail.lead_notes || []"
                                        :key="n.id"
                                        class="relative border-l-2 border-indigo-200 pl-4 dark:border-indigo-800"
                                    >
                                        <p class="text-xs text-slate-500">
                                            {{ formatTs(n.created_at) }}
                                            <span v-if="n.user"> · {{ n.user.name }}</span>
                                        </p>
                                        <p
                                            class="mt-1 whitespace-pre-wrap text-sm text-slate-700 dark:text-slate-200"
                                        >
                                            {{ n.body }}
                                        </p>
                                    </li>
                                    <li
                                        v-if="!(leadDetail.lead_notes || []).length"
                                        class="text-sm text-slate-400"
                                    >
                                        Henüz not yok.
                                    </li>
                                </ul>
                            </section>

                            <section class="mt-10 border-t border-slate-100 pt-8 dark:border-slate-800">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                    Aktivite geçmişi
                                </h3>
                                <ul class="mt-4 space-y-4">
                                    <li
                                        v-for="a in leadDetail.activities || []"
                                        :key="a.id"
                                        class="relative border-l-2 border-slate-200 pl-4 dark:border-slate-700"
                                    >
                                        <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                                            {{ activityLeadLabel(a.action) }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-slate-500">
                                            {{ formatTs(a.created_at) }}
                                            <span v-if="a.user"> · {{ a.user.name }}</span>
                                        </p>
                                        <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                                            {{ a.description }}
                                        </p>
                                    </li>
                                    <li
                                        v-if="!(leadDetail.activities || []).length"
                                        class="text-sm text-slate-400"
                                    >
                                        Kayıt yok.
                                    </li>
                                </ul>
                            </section>

                            <section
                                v-if="leadDetail.crm_notes"
                                class="mt-10 border-t border-slate-100 pt-8 dark:border-slate-800"
                            >
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                    CRM notu (alan)
                                </h3>
                                <p
                                    class="mt-2 whitespace-pre-wrap text-sm text-slate-600 dark:text-slate-300"
                                >
                                    {{ leadDetail.crm_notes }}
                                </p>
                            </section>
                        </div>
                    </aside>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
