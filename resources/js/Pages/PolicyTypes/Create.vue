<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { POLICY_TYPE_ICON_OPTIONS } from '@/lib/policyTypeIcons';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

function slugify(value) {
    return value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/ğ/g, 'g')
        .replace(/ü/g, 'u')
        .replace(/ş/g, 's')
        .replace(/ı/g, 'i')
        .replace(/ö/g, 'o')
        .replace(/ç/g, 'c')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

const form = useForm({
    name: '',
    slug: '',
    description: '',
    category: '',
    color: '#6366f1',
    icon: 'rectangle-stack',
    renewal_reminder_days: 30,
    is_active: true,
});

let slugManual = false;

watch(
    () => form.name,
    (n) => {
        if (!slugManual) {
            form.slug = slugify(n || '');
        }
    },
);

function onSlugInput() {
    slugManual = true;
}

function submit() {
    form.post(route('policy-types.store'));
}
</script>

<template>
    <Head title="Yeni poliçe türü" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Yeni poliçe türü</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Slug küçük harf, rakam ve tire (ör. tamamlayici-saglik).
                </p>
            </div>
        </template>

        <div class="mx-auto max-w-xl">
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
                    <InputLabel value="Slug (sistem anahtarı)" />
                    <input
                        v-model="form.slug"
                        type="text"
                        required
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 font-mono text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        @input="onSlugInput"
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
                    <InputLabel value="Kategori (opsiyonel)" />
                    <input
                        v-model="form.category"
                        type="text"
                        placeholder="ör. arac, saglik, konut"
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
                                pattern="^#[0-9A-Fa-f]{6}$"
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
                        id="pt-active"
                        v-model="form.is_active"
                        type="checkbox"
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800"
                    />
                    <label for="pt-active" class="text-sm text-slate-700 dark:text-slate-300">Aktif</label>
                </div>
                <div class="flex gap-3 pt-2">
                    <PrimaryButton :disabled="form.processing" class="rounded-xl">
                        Kaydet
                    </PrimaryButton>
                    <Link
                        :href="route('policy-types.index')"
                        class="inline-flex items-center rounded-xl px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        Vazgeç
                    </Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
