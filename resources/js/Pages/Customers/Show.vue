<script setup>
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChatBubbleLeftIcon, MapPinIcon, PencilSquareIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    customer: Object,
});

const noteForm = useForm({
    body: '',
});

function addNote() {
    noteForm.post(route('customers.notes.store', props.customer.id), {
        preserveScroll: true,
        onSuccess: () => noteForm.reset('body'),
    });
}
</script>

<template>
    <Head :title="customer.company_name || customer.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-lg font-semibold text-slate-900 dark:text-white">
                        {{ customer.company_name || customer.name }}
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ customer.type === 'corporate' ? 'Kurumsal' : 'Bireysel' }} ·
                        {{ customer.segment }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="route('customers.edit', customer.id)"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        <PencilSquareIcon class="h-4 w-4" />
                        Düzenle
                    </Link>
                    <Link
                        :href="route('customers.index')"
                        class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400"
                    >
                        Listeye dön
                    </Link>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-1">
                    <div
                        class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">
                            İletişim
                        </h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div>
                                <dt class="text-slate-500">E-posta</dt>
                                <dd class="font-medium text-slate-800 dark:text-slate-200">
                                    {{ customer.email || '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">Telefon</dt>
                                <dd class="font-medium text-slate-800 dark:text-slate-200">
                                    {{ customer.phone || '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">Temsilci</dt>
                                <dd class="font-medium text-slate-800 dark:text-slate-200">
                                    {{ customer.assigned_user?.name ?? '—' }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h2 class="flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-white">
                            <MapPinIcon class="h-4 w-4 text-indigo-500" />
                            Adresler
                        </h2>
                        <ul class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                            <li
                                v-for="a in customer.addresses"
                                :key="a.id"
                                class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60"
                            >
                                <p class="font-medium text-slate-800 dark:text-slate-100">
                                    {{ a.label || 'Adres' }}
                                    <span
                                        v-if="a.is_primary"
                                        class="ml-1 text-xs text-indigo-600 dark:text-indigo-400"
                                    >(birincil)</span>
                                </p>
                                <p class="mt-1">
                                    {{ a.line1 }} {{ a.line2 }}<br />
                                    {{ a.district }} {{ a.city }} {{ a.postal_code }}
                                </p>
                            </li>
                            <li v-if="!customer.addresses?.length" class="text-slate-500">
                                Kayıtlı adres yok.
                            </li>
                        </ul>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">
                            İletişim kişileri
                        </h2>
                        <ul class="mt-4 space-y-2 text-sm">
                            <li
                                v-for="c in customer.contacts"
                                :key="c.id"
                                class="rounded-xl border border-slate-100 p-3 dark:border-slate-800"
                            >
                                <p class="font-medium">{{ c.name }}</p>
                                <p class="text-xs text-slate-500">{{ c.title }}</p>
                                <p class="text-slate-600 dark:text-slate-300">{{ c.phone }} · {{ c.email }}</p>
                            </li>
                            <li v-if="!customer.contacts?.length" class="text-slate-500">
                                Kayıt yok.
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="space-y-6 lg:col-span-2">
                    <div
                        class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h2 class="flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-white">
                            <ChatBubbleLeftIcon class="h-4 w-4 text-indigo-500" />
                            Not ekle
                        </h2>
                        <form class="mt-4 space-y-3" @submit.prevent="addNote">
                            <textarea
                                v-model="noteForm.body"
                                rows="3"
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                placeholder="Görüşme özeti, risk notu..."
                            />
                            <InputError :message="noteForm.errors.body" />
                            <PrimaryButton
                                type="submit"
                                :disabled="noteForm.processing"
                                class="rounded-xl"
                            >
                                Notu kaydet
                            </PrimaryButton>
                        </form>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">
                            Notlar
                        </h2>
                        <ul class="mt-4 space-y-4">
                            <li
                                v-for="n in customer.notes"
                                :key="n.id"
                                class="rounded-xl bg-slate-50 p-4 text-sm dark:bg-slate-800/50"
                            >
                                <p class="text-slate-800 dark:text-slate-100">{{ n.body }}</p>
                                <p class="mt-2 text-xs text-slate-500">
                                    {{ n.user?.name }} · {{ new Date(n.created_at).toLocaleString('tr-TR') }}
                                </p>
                            </li>
                            <li v-if="!customer.notes?.length" class="text-sm text-slate-500">
                                Henüz not yok.
                            </li>
                        </ul>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">
                            Aktivite geçmişi
                        </h2>
                        <ol class="relative mt-6 border-l border-slate-200 dark:border-slate-700">
                            <li
                                v-for="a in customer.activities"
                                :key="a.id"
                                class="mb-6 ml-4"
                            >
                                <div
                                    class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border border-white bg-indigo-500 dark:border-slate-900"
                                />
                                <p class="text-sm font-medium text-slate-900 dark:text-white">
                                    {{ a.description || a.action }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ a.user?.name }} · {{ new Date(a.created_at).toLocaleString('tr-TR') }}
                                </p>
                            </li>
                        </ol>
                        <p
                            v-if="!customer.activities?.length"
                            class="mt-4 text-sm text-slate-500"
                        >
                            Aktivite yok.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
