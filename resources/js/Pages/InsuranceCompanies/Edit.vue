<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    company: Object,
});

const form = useForm({
    name: props.company.name,
    code: props.company.code ?? '',
    logo: null,
    contact_phone: props.company.contact_phone ?? '',
    contact_email: props.company.contact_email ?? '',
    contact_person: props.company.contact_person ?? '',
    is_active: !!props.company.is_active,
    api_enabled: !!props.company.api_enabled,
    quote_integration_enabled: !!props.company.quote_integration_enabled,
});

function submit() {
    form.post(route('insurance-companies.update', props.company.id), {
        forceFormData: true,
        _method: 'put',
    });
}
</script>

<template>
    <Head title="Firma düzenle" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Firma düzenle</h1>
            </div>
        </template>

        <div class="mx-auto max-w-xl">
            <form
                class="space-y-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                @submit.prevent="submit"
            >
                <div v-if="company.logo_url" class="flex items-center gap-3">
                    <img
                        :src="company.logo_url"
                        alt=""
                        class="h-14 w-14 rounded-xl border border-slate-200 object-contain p-1 dark:border-slate-700"
                    />
                    <p class="text-xs text-slate-500">Mevcut logo. Yeni dosya seçerek değiştirin.</p>
                </div>
                <div>
                    <InputLabel value="Firma adı" />
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>
                <div>
                    <InputLabel value="Kısa kod" />
                    <input
                        v-model="form.code"
                        type="text"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm uppercase dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError class="mt-2" :message="form.errors.code" />
                </div>
                <div>
                    <InputLabel value="Yeni logo (opsiyonel)" />
                    <input
                        type="file"
                        accept="image/*"
                        class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 dark:text-slate-400"
                        @input="form.logo = $event.target.files[0]"
                    />
                    <InputError class="mt-2" :message="form.errors.logo" />
                </div>
                <div>
                    <InputLabel value="Yetkili kişi" />
                    <input
                        v-model="form.contact_person"
                        type="text"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError class="mt-2" :message="form.errors.contact_person" />
                </div>
                <div>
                    <InputLabel value="Telefon" />
                    <input
                        v-model="form.contact_phone"
                        type="text"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError class="mt-2" :message="form.errors.contact_phone" />
                </div>
                <div>
                    <InputLabel value="E-posta" />
                    <input
                        v-model="form.contact_email"
                        type="email"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError class="mt-2" :message="form.errors.contact_email" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="flex items-center gap-2">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800"
                        />
                        <span class="text-sm text-slate-700 dark:text-slate-300">Aktif</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input
                            v-model="form.api_enabled"
                            type="checkbox"
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800"
                        />
                        <span class="text-sm text-slate-700 dark:text-slate-300">API entegrasyonu aktif</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input
                            v-model="form.quote_integration_enabled"
                            type="checkbox"
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800"
                        />
                        <span class="text-sm text-slate-700 dark:text-slate-300">Teklif entegrasyonu var</span>
                    </label>
                </div>
                <div class="flex gap-3 pt-2">
                    <PrimaryButton :disabled="form.processing" class="rounded-xl">
                        Güncelle
                    </PrimaryButton>
                    <Link
                        :href="route('insurance-companies.index')"
                        class="inline-flex items-center rounded-xl px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        Listeye dön
                    </Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
