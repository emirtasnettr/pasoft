<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    settings: Object,
});

function clonePlans(plans) {
    return JSON.parse(JSON.stringify(plans ?? []));
}

const form = useForm({
    site_title: props.settings.site_title ?? '',
    site_description: props.settings.site_description ?? '',
    logo: null,
    favicon: null,
    hero_screenshot: null,
    remove_logo: false,
    remove_favicon: false,
    remove_hero_screenshot: false,
    pricing_plans: clonePlans(props.settings.pricing_plans),
});

const logoPreview = ref(null);
const faviconPreview = ref(null);
const heroScreenshotPreview = ref(null);

function onLogoInput(e) {
    const f = e.target.files?.[0];
    form.logo = f ?? null;
    logoPreview.value = f ? URL.createObjectURL(f) : null;
}

function onFaviconInput(e) {
    const f = e.target.files?.[0];
    form.favicon = f ?? null;
    faviconPreview.value = f ? URL.createObjectURL(f) : null;
}

function onHeroScreenshotInput(e) {
    const f = e.target.files?.[0];
    form.hero_screenshot = f ?? null;
    heroScreenshotPreview.value = f ? URL.createObjectURL(f) : null;
}

function submit() {
    form.post(route('admin.settings.update'), {
        forceFormData: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Site ayarları" />

    <AdminLayout>
        <div class="mx-auto max-w-2xl">
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Site ayarları</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Logo, favicon, ana sayfa hero görseli ve fiyat kartları buradan yönetilir.
            </p>

            <form
                class="mt-8 space-y-6 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                @submit.prevent="submit"
            >
                <div>
                    <InputLabel value="Site başlığı" />
                    <input
                        v-model="form.site_title"
                        type="text"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        placeholder="Örn. PoliçeAsist"
                    />
                    <InputError class="mt-2" :message="form.errors.site_title" />
                </div>

                <div>
                    <InputLabel value="Site açıklaması (meta)" />
                    <textarea
                        v-model="form.site_description"
                        rows="3"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        placeholder="Arama motorları ve önizlemeler için kısa açıklama"
                    />
                    <InputError class="mt-2" :message="form.errors.site_description" />
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Logo" />
                        <div v-if="settings.logo_url || logoPreview" class="mt-2 flex items-center gap-3">
                            <img
                                :src="logoPreview || settings.logo_url"
                                alt=""
                                class="h-14 w-auto max-w-[180px] rounded-lg border border-slate-200 object-contain dark:border-slate-700"
                            />
                        </div>
                        <input
                            type="file"
                            accept="image/*"
                            class="mt-2 block w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 dark:text-slate-400"
                            @change="onLogoInput"
                        />
                        <label
                            v-if="settings.logo_url"
                            class="mt-2 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400"
                        >
                            <input v-model="form.remove_logo" type="checkbox" class="rounded border-slate-300" />
                            Mevcut logoyu kaldır
                        </label>
                        <InputError class="mt-2" :message="form.errors.logo" />
                    </div>
                    <div>
                        <InputLabel value="Favicon" />
                        <div v-if="settings.favicon_url || faviconPreview" class="mt-2">
                            <img
                                :src="faviconPreview || settings.favicon_url"
                                alt=""
                                class="h-10 w-10 rounded border border-slate-200 object-contain dark:border-slate-700"
                            />
                        </div>
                        <input
                            type="file"
                            accept="image/*,.ico"
                            class="mt-2 block w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 dark:text-slate-400"
                            @change="onFaviconInput"
                        />
                        <label
                            v-if="settings.favicon_url"
                            class="mt-2 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400"
                        >
                            <input v-model="form.remove_favicon" type="checkbox" class="rounded border-slate-300" />
                            Mevcut favicon’u kaldır
                        </label>
                        <InputError class="mt-2" :message="form.errors.favicon" />
                    </div>
                </div>

                <div class="border-t border-slate-200 pt-6 dark:border-slate-700">
                    <InputLabel value="Ana sayfa hero görseli (dashboard ekran görüntüsü)" />
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        PNG, JPEG veya WebP. Landing’de metnin sağında gösterilir.
                    </p>
                    <ul
                        class="mt-2 list-inside list-disc space-y-0.5 text-sm text-slate-600 dark:text-slate-400"
                    >
                        <li>Maksimum genişlik: <span class="font-medium tabular-nums text-slate-800 dark:text-slate-200">3840 px</span></li>
                        <li>Maksimum yükseklik: <span class="font-medium tabular-nums text-slate-800 dark:text-slate-200">2160 px</span></li>
                        <li>Maksimum dosya boyutu: <span class="font-medium tabular-nums text-slate-800 dark:text-slate-200">5 MB</span></li>
                    </ul>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-500">
                        Önerilen: en az 1200 px genişlikte ekran görüntüsü.
                    </p>
                    <div
                        v-if="settings.hero_screenshot_url || heroScreenshotPreview"
                        class="mt-3 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700"
                    >
                        <img
                            :src="heroScreenshotPreview || settings.hero_screenshot_url"
                            alt=""
                            class="max-h-64 w-full object-cover object-top"
                        />
                    </div>
                    <input
                        type="file"
                        accept="image/png,image/jpeg,image/webp"
                        class="mt-2 block w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 dark:text-slate-400"
                        @change="onHeroScreenshotInput"
                    />
                    <label
                        v-if="settings.hero_screenshot_url"
                        class="mt-2 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400"
                    >
                        <input v-model="form.remove_hero_screenshot" type="checkbox" class="rounded border-slate-300" />
                        Mevcut hero görselini kaldır
                    </label>
                    <InputError class="mt-2" :message="form.errors.hero_screenshot" />
                </div>

                <div class="border-t border-slate-200 pt-8 dark:border-slate-700">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">Esnek fiyatlandırma (ana sayfa)</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Dört lisans kartı için dönem adı, görünen fiyat metni ve isteğe bağlı rozet.
                    </p>

                    <div
                        v-for="(plan, i) in form.pricing_plans"
                        :key="i"
                        class="mt-5 rounded-xl border border-slate-200/90 bg-slate-50/50 p-4 dark:border-slate-700 dark:bg-slate-800/40"
                    >
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kart {{ i + 1 }}</p>
                        <div class="mt-3 grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <InputLabel :value="`Dönem adı`" />
                                <input
                                    v-model="plan.period"
                                    type="text"
                                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                />
                                <InputError class="mt-2" :message="form.errors[`pricing_plans.${i}.period`]" />
                            </div>
                            <div>
                                <InputLabel :value="`Fiyat / teklif metni`" />
                                <input
                                    v-model="plan.price_label"
                                    type="text"
                                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    placeholder="Örn. ₺4.900 / ay veya Özel teklif"
                                />
                                <InputError class="mt-2" :message="form.errors[`pricing_plans.${i}.price_label`]" />
                            </div>
                            <div>
                                <InputLabel value="Rozet (isteğe bağlı)" />
                                <input
                                    v-model="plan.badge"
                                    type="text"
                                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    placeholder="Örn. En avantajlı"
                                />
                                <InputError class="mt-2" :message="form.errors[`pricing_plans.${i}.badge`]" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                    <input
                                        v-model="plan.highlight"
                                        type="checkbox"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    Bu kartı vurgula (çerçeve)
                                </label>
                                <InputError class="mt-2" :message="form.errors[`pricing_plans.${i}.highlight`]" />
                            </div>
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.pricing_plans" />
                </div>

                <div class="flex gap-3 pt-2">
                    <PrimaryButton :disabled="form.processing" class="rounded-xl" type="submit">
                        Kaydet
                    </PrimaryButton>
                    <SecondaryButton
                        type="button"
                        class="rounded-xl"
                        :disabled="form.processing"
                        @click="
                            form.reset();
                            form.clearErrors();
                            logoPreview = null;
                            faviconPreview = null;
                            heroScreenshotPreview = null;
                            form.pricing_plans = clonePlans(props.settings.pricing_plans);
                        "
                    >
                        Değişiklikleri sıfırla
                    </SecondaryButton>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
