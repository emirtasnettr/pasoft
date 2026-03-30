<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    roles: Array,
    can_edit_role: { type: Boolean, default: true },
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role_id: props.user.role_id,
    phone: props.user.phone ?? '',
    is_active: !!props.user.is_active,
});

function submit() {
    form.put(route('admin.users.update', props.user.id));
}
</script>

<template>
    <Head title="Kullanıcı düzenle" />

    <AdminLayout>
        <div class="mx-auto max-w-lg">
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Kullanıcı düzenle</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ user.email }}</p>

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
                        :disabled="!can_edit_role"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                    </select>
                    <p v-if="!can_edit_role" class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                        Kendi rolünüzü buradan değiştiremezsiniz.
                    </p>
                    <InputError class="mt-2" :message="form.errors.role_id" />
                </div>
                <div>
                    <InputLabel value="Yeni şifre (opsiyonel)" />
                    <input
                        v-model="form.password"
                        type="password"
                        autocomplete="new-password"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>
                <div>
                    <InputLabel value="Yeni şifre tekrar" />
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <input
                        id="edit-active"
                        v-model="form.is_active"
                        type="checkbox"
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800"
                    />
                    <label for="edit-active" class="text-sm text-slate-700 dark:text-slate-300">Aktif</label>
                </div>
                <div class="flex gap-3 pt-2">
                    <PrimaryButton :disabled="form.processing" class="rounded-xl" type="submit">
                        Kaydet
                    </PrimaryButton>
                    <Link
                        :href="route('admin.users.index')"
                        class="inline-flex items-center rounded-xl px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        Listeye dön
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
