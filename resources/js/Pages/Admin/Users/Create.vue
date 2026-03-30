<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    roles: Array,
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role_id: props.roles?.[0]?.id ?? '',
    phone: '',
    is_active: true,
});

function submit() {
    form.post(route('admin.users.store'));
}
</script>

<template>
    <Head title="Yeni kullanıcı" />

    <AdminLayout>
        <div class="mx-auto max-w-lg">
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Yeni kullanıcı</h1>

            <form
                class="mt-6 space-y-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                @submit.prevent="submit"
            >
                <div>
                    <InputLabel value="Ad soyad" />
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>
                <div>
                    <InputLabel value="E-posta" />
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>
                <div>
                    <InputLabel value="Telefon" />
                    <input
                        v-model="form.phone"
                        type="text"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError class="mt-2" :message="form.errors.phone" />
                </div>
                <div>
                    <InputLabel value="Rol" />
                    <select
                        v-model="form.role_id"
                        required
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.role_id" />
                </div>
                <div>
                    <InputLabel value="Şifre" />
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="new-password"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>
                <div>
                    <InputLabel value="Şifre tekrar" />
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <input
                        id="create-active"
                        v-model="form.is_active"
                        type="checkbox"
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800"
                    />
                    <label for="create-active" class="text-sm text-slate-700 dark:text-slate-300">Aktif</label>
                </div>
                <div class="flex gap-3 pt-2">
                    <PrimaryButton :disabled="form.processing" class="rounded-xl" type="submit">
                        Oluştur
                    </PrimaryButton>
                    <Link
                        :href="route('admin.users.index')"
                        class="inline-flex items-center rounded-xl px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        Vazgeç
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
