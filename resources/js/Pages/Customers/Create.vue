<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    users: Array,
});

const form = useForm({
    type: 'individual',
    name: '',
    company_name: '',
    national_id: '',
    tax_number: '',
    phone: '',
    email: '',
    segment: 'active',
    assigned_user_id: '',
});

function submit() {
    form.post(route('customers.store'));
}
</script>

<template>
    <Head title="Yeni müşteri" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Yeni müşteri</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Bireysel veya kurumsal kayıt oluşturun.
                </p>
            </div>
        </template>

        <div class="mx-auto max-w-2xl">
            <form
                class="space-y-6 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                @submit.prevent="submit"
            >
                <div>
                    <InputLabel value="Tür" />
                    <select
                        v-model="form.type"
                        class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="individual">Bireysel</option>
                        <option value="corporate">Kurumsal</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.type" />
                </div>

                <div>
                    <InputLabel :value="form.type === 'corporate' ? 'Yetkili adı' : 'Ad Soyad'" />
                    <input
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div v-if="form.type === 'corporate'">
                    <InputLabel value="Firma adı" />
                    <input
                        v-model="form.company_name"
                        type="text"
                        class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError class="mt-2" :message="form.errors.company_name" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="TC Kimlik No" />
                        <input
                            v-model="form.national_id"
                            type="text"
                            class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError class="mt-2" :message="form.errors.national_id" />
                    </div>
                    <div>
                        <InputLabel value="Vergi no" />
                        <input
                            v-model="form.tax_number"
                            type="text"
                            class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError class="mt-2" :message="form.errors.tax_number" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Telefon" />
                        <input
                            v-model="form.phone"
                            type="text"
                            class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError class="mt-2" :message="form.errors.phone" />
                    </div>
                    <div>
                        <InputLabel value="E-posta" />
                        <input
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Segment" />
                        <select
                            v-model="form.segment"
                            class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option value="vip">VIP</option>
                            <option value="active">Aktif</option>
                            <option value="passive">Pasif</option>
                            <option value="potential">Potansiyel</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.segment" />
                    </div>
                    <div>
                        <InputLabel value="Atanan temsilci" />
                        <select
                            v-model="form.assigned_user_id"
                            class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option value="">—</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">
                                {{ u.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.assigned_user_id" />
                    </div>
                </div>

                <div class="flex gap-3">
                    <PrimaryButton
                        :disabled="form.processing"
                        class="rounded-xl"
                    >
                        Kaydet
                    </PrimaryButton>
                    <Link
                        :href="route('customers.index')"
                        class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        Vazgeç
                    </Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
