<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import FlashToast from '@/Components/FlashToast.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PaToastHost from '@/Components/PaToastHost.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
        default: false,
    },
    canRegister: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const appSettings = computed(() => page.props.appSettings ?? {});

function toast(message, variant = 'success') {
    if (!message || typeof window === 'undefined') {
        return;
    }
    window.dispatchEvent(new CustomEvent('pa-toast', { detail: { message, variant } }));
}

watch(
    () => page.props.flash?.success,
    (v) => {
        if (v) {
            toast(v, 'success');
        }
    },
    { immediate: true },
);

const form = useForm({
    company_name: '',
    contact_name: '',
    email: '',
    phone: '',
    message: '',
});

function submit() {
    form.post(route('demo.request.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <Head title="Demo talep">
            <meta
                v-if="appSettings.site_description"
                name="description"
                :content="appSettings.site_description"
            />
            <link v-if="appSettings.favicon_url" rel="icon" :href="appSettings.favicon_url" />
        </Head>

        <FlashToast />
        <PaToastHost />

        <!-- Üst bar -->
        <header
            class="sticky top-0 z-50 border-b border-slate-200/90 bg-white/90 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/90"
        >
            <div class="mx-auto flex h-14 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link href="/" class="flex shrink-0 items-center gap-2">
                    <img
                        v-if="appSettings.logo_url"
                        :src="appSettings.logo_url"
                        alt=""
                        class="h-8 w-auto max-w-[140px] object-contain"
                    />
                    <div
                        v-else
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-[#064AAD] to-[#053d8f] p-1 shadow-md"
                    >
                        <ApplicationLogo class="h-full w-full text-white" />
                    </div>
                </Link>
                <div class="flex items-center gap-2 text-sm">
                    <Link
                        v-if="canLogin"
                        :href="route('login')"
                        class="rounded-full px-3 py-1.5 font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        Giriş
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-full bg-gradient-to-r from-[#064AAD] to-[#0558c9] px-4 py-1.5 font-semibold text-white shadow-md shadow-[#064AAD]/25"
                    >
                        Kayıt ol
                    </Link>
                </div>
            </div>
        </header>

        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 lg:grid-cols-2 lg:gap-16 lg:px-8 lg:py-16">
            <!-- Sol: hero -->
            <div class="flex flex-col justify-center">
                <p
                    class="inline-flex w-fit items-center gap-2 rounded-full border border-[#064AAD]/20 bg-[#064AAD]/5 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-[#064AAD] dark:border-[#064AAD]/40 dark:bg-[#064AAD]/15 dark:text-[#4d8fd9]"
                >
                    Demo
                </p>
                <h1 class="mt-6 text-balance text-3xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-4xl lg:text-5xl">
                    Ürünü canlı görün, ekibinize özel teklif alın
                </h1>
                <p class="mt-5 max-w-lg text-pretty text-lg leading-relaxed text-slate-600 dark:text-slate-400">
                    Kısa bir form ile demo talebinizi iletin. Uzmanımız ihtiyaçlarınıza göre PoliçeAsist’i birlikte
                    keşfetsin; süreç, entegrasyon ve fiyatlandırma için size dönüş yapalım.
                </p>
                <ul class="mt-8 space-y-3 text-sm text-slate-700 dark:text-slate-300">
                    <li class="flex items-start gap-2">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[#1CBF65]" />
                        Canlı ortamda modüller ve ekranlar
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[#1CBF65]" />
                        İş hacminize uygun lisans seçenekleri
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[#1CBF65]" />
                        Güvenli veri ve kurumsal kullanım
                    </li>
                </ul>
                <Link
                    href="/"
                    class="mt-10 inline-flex w-fit items-center gap-2 text-sm font-semibold text-[#064AAD] transition hover:text-[#053d8f] dark:text-[#4d8fd9]"
                >
                    ← Ana sayfaya dön
                </Link>
            </div>

            <!-- Sağ: form -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xl shadow-slate-900/[0.06] dark:border-slate-700 dark:bg-slate-900"
            >
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Demo talep formu</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Zorunlu alanlar işaretlidir. Bilgileriniz yalnızca demo planlaması için kullanılır.
                </p>

                <form class="mt-6 space-y-4" @submit.prevent="submit">
                    <div>
                        <InputLabel for="company_name" value="Şirket / acente adı *" />
                        <input
                            id="company_name"
                            v-model="form.company_name"
                            type="text"
                            required
                            class="mt-1 block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-[#064AAD] focus:outline-none focus:ring-4 focus:ring-[#064AAD]/15 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError class="mt-1" :message="form.errors.company_name" />
                    </div>
                    <div>
                        <InputLabel for="contact_name" value="Yetkili adı soyadı *" />
                        <input
                            id="contact_name"
                            v-model="form.contact_name"
                            type="text"
                            required
                            class="mt-1 block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-[#064AAD] focus:outline-none focus:ring-4 focus:ring-[#064AAD]/15 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError class="mt-1" :message="form.errors.contact_name" />
                    </div>
                    <div>
                        <InputLabel for="email" value="E-posta *" />
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="email"
                            class="mt-1 block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-[#064AAD] focus:outline-none focus:ring-4 focus:ring-[#064AAD]/15 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>
                    <div>
                        <InputLabel for="phone" value="Telefon" />
                        <input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            autocomplete="tel"
                            class="mt-1 block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-[#064AAD] focus:outline-none focus:ring-4 focus:ring-[#064AAD]/15 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        />
                        <InputError class="mt-1" :message="form.errors.phone" />
                    </div>
                    <div>
                        <InputLabel for="message" value="Mesajınız (isteğe bağlı)" />
                        <textarea
                            id="message"
                            v-model="form.message"
                            rows="4"
                            class="mt-1 block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-[#064AAD] focus:outline-none focus:ring-4 focus:ring-[#064AAD]/15 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            placeholder="Örn. kullanıcı sayısı, mevcut sistem, öncelikli modüller…"
                        />
                        <InputError class="mt-1" :message="form.errors.message" />
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-xl bg-gradient-to-r from-[#064AAD] to-[#0558c9] py-3.5 text-sm font-semibold text-white shadow-lg shadow-[#064AAD]/30 transition hover:scale-[1.01] disabled:opacity-60"
                    >
                        {{ form.processing ? 'Gönderiliyor…' : 'Demo talep et' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
