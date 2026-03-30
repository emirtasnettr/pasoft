<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import FlashToast from '@/Components/FlashToast.vue';
import PaToastHost from '@/Components/PaToastHost.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    demoLogin: {
        type: Object,
        default: () => ({ enabled: false, roles: [] }),
    },
});

const page = usePage();
const appSettings = computed(() => page.props.appSettings ?? {});

const demoModalOpen = ref(false);
const emailInput = ref(null);

const demoEnabled = computed(
    () => props.demoLogin?.enabled && (props.demoLogin?.roles?.length ?? 0) > 0,
);

function openDemoModal() {
    demoModalOpen.value = true;
}

function closeDemoModal() {
    demoModalOpen.value = false;
}

function quickLogin(slug) {
    router.post(route('login.demo', slug), {}, {
        preserveScroll: true,
        onFinish: () => {
            closeDemoModal();
        },
    });
}

function toast(message, variant = 'success') {
    if (!message || typeof window === 'undefined') {
        return;
    }
    window.dispatchEvent(
        new CustomEvent('pa-toast', { detail: { message, variant } }),
    );
}

watch(
    () => props.status,
    (v) => {
        if (v) {
            toast(v, 'success');
        }
    },
    { immediate: true },
);

function notifyFormErrors(errors) {
    for (const v of Object.values(errors)) {
        const msg = Array.isArray(v) ? v[0] : v;
        if (msg) {
            toast(msg, 'error');
            return;
        }
    }
}

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
        onError: (errors) => notifyFormErrors(errors),
    });
};

function onKeydown(e) {
    if (e.key === 'Escape' && demoModalOpen.value) {
        closeDemoModal();
    }
}

onMounted(() => {
    emailInput.value?.focus();
    window.addEventListener('keydown', onKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <div class="min-h-screen font-sans antialiased">
        <Head title="Giriş">
            <meta
                v-if="appSettings.site_description"
                name="description"
                :content="appSettings.site_description"
            />
            <link v-if="appSettings.favicon_url" rel="icon" :href="appSettings.favicon_url" />
        </Head>

        <FlashToast />
        <PaToastHost />

        <div class="flex min-h-screen flex-col lg:flex-row">
            <!-- Sol: marka -->
            <div
                class="relative flex min-h-[42vh] flex-col justify-center overflow-hidden px-8 py-12 lg:min-h-screen lg:w-[46%] lg:px-14 lg:py-16"
            >
                <div
                    class="absolute inset-0 bg-gradient-to-br from-[#021f4a] via-[#064AAD] to-[#031a38]"
                />
                <div
                    class="pointer-events-none absolute -left-24 top-10 h-72 w-72 rounded-full bg-[#064AAD]/40 blur-3xl"
                />
                <div
                    class="pointer-events-none absolute -right-16 bottom-16 h-80 w-80 rounded-full bg-[#1CBF65]/30 blur-3xl"
                />
                <div
                    class="pointer-events-none absolute inset-0 opacity-[0.12]"
                    style="
                        background-image: radial-gradient(
                            circle at 1px 1px,
                            rgb(255 255 255 / 0.35) 1px,
                            transparent 0
                        );
                        background-size: 28px 28px;
                    "
                />

                <div class="relative z-10 mx-auto w-full max-w-lg">
                    <div class="flex items-center gap-3">
                        <img
                            v-if="appSettings.logo_url"
                            :src="appSettings.logo_url"
                            :alt="appSettings.site_title || 'PoliçeAsist'"
                            class="h-12 w-auto max-w-[200px] object-contain brightness-0 invert drop-shadow-lg"
                        />
                        <div
                            v-else
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 p-2 shadow-lg ring-1 ring-white/20 backdrop-blur-sm"
                        >
                            <ApplicationLogo class="h-full w-full text-white" />
                        </div>
                    </div>

                    <h1
                        class="mt-8 text-balance text-3xl font-semibold tracking-tight text-white sm:text-4xl"
                    >
                        Sigorta süreçlerinizi tek panelden yönetin
                    </h1>
                    <p class="mt-4 max-w-md text-pretty text-base leading-relaxed text-blue-100/90">
                        Poliçe, müşteri ve tahsilat yönetimi tek yerde
                    </p>

                    <div
                        class="mt-10 hidden items-center gap-6 border-t border-white/10 pt-8 text-sm text-blue-100/85 lg:flex"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex h-2 w-2 rounded-full bg-[#1CBF65] shadow-[0_0_12px_rgba(28,191,101,0.85)]"
                            />
                            Güvenli oturum
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex h-2 w-2 rounded-full bg-white/80 shadow-[0_0_12px_rgba(255,255,255,0.45)]"
                            />
                            Kurumsal altyapı
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sağ: form -->
            <div
                class="relative flex flex-1 flex-col justify-center bg-gradient-to-b from-slate-50 via-white to-slate-100/90 px-5 py-12 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 lg:px-12"
            >
                <div
                    class="pointer-events-none absolute right-0 top-0 h-64 w-64 rounded-full bg-[#064AAD]/15 blur-3xl dark:bg-[#064AAD]/20"
                />
                <div
                    class="pointer-events-none absolute bottom-0 left-0 h-48 w-48 rounded-full bg-[#1CBF65]/15 blur-3xl dark:bg-[#1CBF65]/10"
                />

                <div class="relative z-10 mx-auto w-full max-w-md">
                    <div class="mb-8 lg:hidden">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                            Giriş
                        </p>
                        <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">
                            Hesabınıza giriş yapın
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200/80 bg-white/90 p-8 shadow-xl shadow-slate-900/[0.06] ring-1 ring-slate-900/[0.04] backdrop-blur-sm dark:border-slate-700/80 dark:bg-slate-900/80 dark:shadow-black/40 dark:ring-white/[0.06]"
                    >
                        <div class="hidden lg:block">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                Hoş geldiniz
                            </p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">
                                Hesabınıza giriş yapın
                            </h2>
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                                E-posta ve şifrenizle güvenli şekilde devam edin.
                            </p>
                        </div>

                        <form class="mt-8 space-y-5" @submit.prevent="submit">
                            <div>
                                <label
                                    for="email"
                                    class="block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    E-posta
                                </label>
                                <input
                                    id="email"
                                    ref="emailInput"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    autocomplete="username"
                                    class="mt-2 block w-full rounded-xl border border-slate-200/90 bg-white px-4 py-3.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-[#064AAD] focus:outline-none focus:ring-4 focus:ring-[#064AAD]/15 dark:border-slate-600 dark:bg-slate-800/80 dark:text-white dark:placeholder:text-slate-500 dark:focus:border-[#4d8fd9] dark:focus:ring-[#064AAD]/25"
                                    placeholder="ornek@sirket.com"
                                />
                            </div>

                            <div>
                                <label
                                    for="password"
                                    class="block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Şifre
                                </label>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    required
                                    autocomplete="current-password"
                                    class="mt-2 block w-full rounded-xl border border-slate-200/90 bg-white px-4 py-3.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-[#064AAD] focus:outline-none focus:ring-4 focus:ring-[#064AAD]/15 dark:border-slate-600 dark:bg-slate-800/80 dark:text-white dark:placeholder:text-slate-500 dark:focus:border-[#4d8fd9] dark:focus:ring-[#064AAD]/25"
                                    placeholder="••••••••"
                                />
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <label class="flex cursor-pointer items-center gap-2.5">
                                    <input
                                        v-model="form.remember"
                                        type="checkbox"
                                        class="h-4 w-4 rounded-md border-slate-300 text-[#064AAD] focus:ring-[#064AAD]/30 dark:border-slate-600 dark:bg-slate-800"
                                    />
                                    <span class="text-sm text-slate-600 dark:text-slate-400">Beni hatırla</span>
                                </label>
                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-sm font-medium text-[#064AAD] transition hover:text-[#0558c9] dark:text-[#4d8fd9] dark:hover:text-[#6eb3f0]"
                                >
                                    Şifremi unuttum
                                </Link>
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="group relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-xl bg-gradient-to-r from-[#064AAD] to-[#0558c9] px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-[#064AAD]/30 transition duration-200 hover:scale-[1.02] hover:shadow-xl hover:shadow-[#064AAD]/40 focus:outline-none focus:ring-4 focus:ring-[#064AAD]/30 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:scale-100"
                            >
                                <svg
                                    v-if="form.processing"
                                    class="h-5 w-5 shrink-0 animate-spin text-white/90"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    />
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    />
                                </svg>
                                <span>{{ form.processing ? 'Giriş yapılıyor…' : 'Giriş yap' }}</span>
                            </button>
                        </form>

                        <p
                            v-if="demoEnabled"
                            class="mt-8 border-t border-slate-100 pt-6 text-center dark:border-slate-700/80"
                        >
                            <button
                                type="button"
                                class="text-sm font-medium text-slate-500 underline decoration-slate-300 underline-offset-4 transition hover:text-[#064AAD] hover:decoration-[#064AAD]/50 dark:text-slate-400 dark:decoration-slate-600 dark:hover:text-[#4d8fd9]"
                                @click="openDemoModal"
                            >
                                Demo hesaplarla giriş yap
                            </button>
                        </p>
                    </div>

                    <p class="mt-8 text-center text-xs text-slate-500 dark:text-slate-500">
                        © {{ new Date().getFullYear() }}
                        {{ appSettings.site_title || 'PoliçeAsist' }}. Tüm hakları saklıdır.
                    </p>
                </div>
            </div>
        </div>

        <!-- Demo hesaplar modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="demoModalOpen && demoEnabled"
                    class="fixed inset-0 z-[200] flex items-end justify-center bg-slate-900/60 p-4 backdrop-blur-sm sm:items-center"
                    role="presentation"
                    @click.self="closeDemoModal"
                >
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="translate-y-4 opacity-0 sm:scale-95"
                        enter-to-class="translate-y-0 opacity-100 sm:scale-100"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="translate-y-0 opacity-100 sm:scale-100"
                        leave-to-class="translate-y-4 opacity-0 sm:scale-95"
                    >
                        <div
                            v-if="demoModalOpen"
                            role="dialog"
                            aria-modal="true"
                            aria-labelledby="demo-modal-title"
                            class="w-full max-w-md rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900"
                            @click.stop
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3
                                        id="demo-modal-title"
                                        class="text-lg font-semibold text-slate-900 dark:text-white"
                                    >
                                        Demo hesaplar
                                    </h3>
                                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                        Yalnızca
                                        <code
                                            class="rounded-md bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-slate-800"
                                        >local</code>
                                        ortamında. Şifre:
                                        <code
                                            class="rounded-md bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-slate-800"
                                        >password</code>
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300"
                                    aria-label="Kapat"
                                    @click="closeDemoModal"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="mt-5 flex flex-col gap-2">
                                <button
                                    v-for="r in demoLogin.roles"
                                    :key="r.slug"
                                    type="button"
                                    class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-left text-sm font-medium text-slate-800 transition hover:border-[#064AAD]/40 hover:bg-[#064AAD]/5 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-100 dark:hover:border-[#4d8fd9]/40 dark:hover:bg-[#064AAD]/10"
                                    @click="quickLogin(r.slug)"
                                >
                                    <span>{{ r.label }}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ r.email }}</span>
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
