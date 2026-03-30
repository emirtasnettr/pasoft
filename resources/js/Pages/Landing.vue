<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import {
    ArrowPathIcon,
    BanknotesIcon,
    Bars3Icon,
    BellAlertIcon,
    ChartBarSquareIcon,
    ChatBubbleLeftRightIcon,
    CheckIcon,
    CurrencyDollarIcon,
    DocumentTextIcon,
    PresentationChartLineIcon,
    ShieldCheckIcon,
    UserGroupIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

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
const brand = computed(() => appSettings.value.site_title || 'PoliçeAsist');

const pricingPlans = computed(() => appSettings.value.pricing_plans ?? []);

const heroScreenshotUrl = computed(() => appSettings.value.hero_screenshot_url ?? null);

const pricingPackageFeatures = [
    'Sınırsız Poliçe Ekleme',
    'Sınırsız Müşteri Ekleme',
    '50 GB Dosya Yükleme',
    'Tüm Özelliklere Sınırsız Erişim',
    'Kısıtlama Yok!',
];

const modules = [
    {
        title: 'Müşteri Yönetimi',
        desc: 'Kurumsal ve bireysel müşteri kayıtları, iletişim geçmişi ve belgeler tek profilde.',
        icon: UserGroupIcon,
    },
    {
        title: 'Lead & Satış',
        desc: 'Pipeline, kanban ve takip ile fırsatları kaçırmadan satışa dönüştürün.',
        icon: ChartBarSquareIcon,
    },
    {
        title: 'Poliçe Yönetimi',
        desc: 'Poliçe yaşam döngüsü, yenileme ve hatırlatmalar tek ekranda.',
        icon: DocumentTextIcon,
    },
    {
        title: 'Tahsilat & Finans',
        desc: 'Taksit planları, tahsilat durumu ve nakit akışı görünürlüğü.',
        icon: BanknotesIcon,
    },
    {
        title: 'Hasar Yönetimi',
        desc: 'Dosya takibi, durum güncellemeleri ve doküman yönetimi.',
        icon: ShieldCheckIcon,
    },
    {
        title: 'Raporlama',
        desc: 'Performans ve operasyon metrikleriyle ölçülebilir kararlar.',
        icon: PresentationChartLineIcon,
    },
];

const navItems = [
    { href: '#problem', label: 'Ürün', isPage: false },
    { href: '#modules', label: 'Modüller', isPage: false },
    { href: '#pricing', label: 'Fiyatlandırma', isPage: false },
    { href: '/demo', label: 'Demo talep', isPage: true },
];

const mobileNavOpen = ref(false);

function closeMobileNav() {
    mobileNavOpen.value = false;
}

function prefersReducedMotion() {
    if (typeof window === 'undefined' || !window.matchMedia) {
        return false;
    }
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function onLogoClick(e) {
    closeMobileNav();
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) {
        return;
    }
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: prefersReducedMotion() ? 'auto' : 'smooth' });
}

onMounted(() => {
    if (!prefersReducedMotion()) {
        document.documentElement.style.scrollBehavior = 'smooth';
    }
});

onUnmounted(() => {
    document.documentElement.style.scrollBehavior = '';
});

/** Sayfa içi anchor: yumuşak kaydırma; scroll-mt-* ile üst bar payı alınır */
function onInPageNavClick(e, href) {
    if (!href?.startsWith('#') || href.length < 2) {
        return;
    }
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) {
        return;
    }
    const id = href.slice(1);
    const el = document.getElementById(id);
    if (!el) {
        return;
    }
    e.preventDefault();
    el.scrollIntoView({
        behavior: prefersReducedMotion() ? 'auto' : 'smooth',
        block: 'start',
    });
    if (window.history?.replaceState) {
        window.history.replaceState(null, '', href);
    }
}
</script>

<template>
    <div class="bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <Head :title="`${brand} — Sigorta CRM`">
            <meta
                v-if="appSettings.site_description"
                name="description"
                :content="appSettings.site_description"
            />
            <link v-if="appSettings.favicon_url" rel="icon" :href="appSettings.favicon_url" />
        </Head>

        <!-- Üst bar -->
        <header
            class="sticky top-0 z-50 border-b border-slate-200/90 bg-white/90 shadow-sm shadow-slate-900/[0.04] backdrop-blur-xl dark:border-slate-800/90 dark:bg-slate-950/90 dark:shadow-black/40"
        >
            <div class="px-4 sm:px-6 lg:px-8">
                <div
                    class="relative mx-auto flex h-[4.25rem] max-w-6xl items-center justify-between gap-3"
                >
                <a
                    href="/"
                    class="relative z-10 flex min-w-0 shrink-0 items-center"
                    aria-label="Ana sayfa"
                    @click="onLogoClick"
                >
                    <img
                        v-if="appSettings.logo_url"
                        :src="appSettings.logo_url"
                        alt=""
                        class="h-9 w-auto max-w-[160px] object-contain"
                    />
                    <div
                        v-else
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-[#064AAD] to-[#053d8f] p-1.5 shadow-lg shadow-[#064AAD]/30"
                    >
                        <ApplicationLogo class="h-full w-full text-white" />
                    </div>
                </a>

                <!-- Masaüstü: ortalanmış pill menü -->
                <nav
                    class="absolute left-1/2 top-1/2 hidden -translate-x-1/2 -translate-y-1/2 md:block"
                    aria-label="Sayfa bölümleri"
                >
                    <div
                        class="flex items-center gap-0.5 rounded-full border border-slate-200/90 bg-gradient-to-b from-white to-slate-50/95 p-1 shadow-inner shadow-slate-900/[0.06] ring-1 ring-slate-900/[0.03] dark:border-slate-700/90 dark:from-slate-800/95 dark:to-slate-900 dark:ring-white/[0.04]"
                    >
                        <template v-for="item in navItems" :key="item.href + item.label">
                            <Link
                                v-if="item.isPage"
                                :href="item.href"
                                class="whitespace-nowrap rounded-full px-4 py-2 text-[13px] font-semibold tracking-wide text-slate-600 transition-all duration-200 hover:bg-[#064AAD] hover:text-white hover:shadow-md hover:shadow-[#064AAD]/25 active:scale-[0.98] dark:text-slate-300 dark:hover:bg-[#064AAD] dark:hover:text-white"
                            >
                                {{ item.label }}
                            </Link>
                            <a
                                v-else
                                :href="item.href"
                                class="whitespace-nowrap rounded-full px-4 py-2 text-[13px] font-semibold tracking-wide text-slate-600 transition-all duration-200 hover:bg-[#064AAD] hover:text-white hover:shadow-md hover:shadow-[#064AAD]/25 active:scale-[0.98] dark:text-slate-300 dark:hover:bg-[#064AAD] dark:hover:text-white"
                                @click="onInPageNavClick($event, item.href)"
                            >
                                {{ item.label }}
                            </a>
                        </template>
                    </div>
                </nav>

                <div class="relative z-10 flex shrink-0 items-center gap-1.5 sm:gap-2">
                    <Link
                        v-if="!$page.props.auth?.user"
                        :href="route('demo.request')"
                        class="inline-flex whitespace-nowrap rounded-full bg-gradient-to-r from-[#064AAD] to-[#0558c9] px-2.5 py-2 text-xs font-semibold text-white shadow-md shadow-[#064AAD]/30 sm:hidden"
                    >
                        Ücretsiz Dene
                    </Link>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200/90 bg-white p-2.5 text-slate-600 shadow-sm transition hover:border-[#064AAD]/30 hover:bg-slate-50 hover:text-[#064AAD] md:hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-[#064AAD]/50 dark:hover:bg-slate-800"
                        :aria-expanded="mobileNavOpen"
                        aria-controls="landing-mobile-nav"
                        aria-label="Menüyü aç veya kapat"
                        @click="mobileNavOpen = !mobileNavOpen"
                    >
                        <Bars3Icon v-if="!mobileNavOpen" class="h-6 w-6" />
                        <XMarkIcon v-else class="h-6 w-6" />
                    </button>

                    <div class="hidden items-center gap-2 sm:flex sm:gap-3">
                        <Link
                            v-if="canLogin && !$page.props.auth?.user"
                            :href="route('login')"
                            class="rounded-full px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800"
                        >
                            Giriş
                        </Link>
                        <Link
                            v-if="canLogin && $page.props.auth?.user"
                            :href="route('dashboard')"
                            class="rounded-full px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800"
                        >
                            Panele git
                        </Link>
                        <Link
                            v-if="!$page.props.auth?.user"
                            :href="route('demo.request')"
                            class="rounded-full bg-gradient-to-r from-[#064AAD] to-[#0558c9] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#064AAD]/30 transition hover:scale-[1.02] hover:shadow-xl hover:shadow-[#064AAD]/40 active:scale-[0.98]"
                        >
                            Ücretsiz dene
                        </Link>
                    </div>
                </div>
                </div>
            </div>

            <!-- Mobil menü -->
            <div
                id="landing-mobile-nav"
                class="border-t border-slate-200/90 bg-gradient-to-b from-white to-slate-50/95 px-4 dark:border-slate-800 dark:from-slate-950 dark:to-slate-900 sm:px-6 lg:px-8 md:hidden"
                :class="mobileNavOpen ? 'block' : 'hidden'"
            >
                <nav class="mx-auto max-w-6xl space-y-1 py-4" aria-label="Mobil sayfa bölümleri">
                    <template v-for="item in navItems" :key="`m-${item.href}`">
                        <Link
                            v-if="item.isPage"
                            :href="item.href"
                            class="flex items-center justify-between rounded-2xl border border-transparent px-4 py-3.5 text-sm font-semibold text-slate-700 transition hover:border-[#064AAD]/20 hover:bg-[#064AAD]/5 hover:text-[#064AAD] active:bg-[#064AAD]/10 dark:text-slate-200 dark:hover:bg-[#064AAD]/15 dark:hover:text-white"
                            @click="closeMobileNav"
                        >
                            {{ item.label }}
                            <span class="text-slate-400 dark:text-slate-500" aria-hidden="true">→</span>
                        </Link>
                        <a
                            v-else
                            :href="item.href"
                            class="flex items-center justify-between rounded-2xl border border-transparent px-4 py-3.5 text-sm font-semibold text-slate-700 transition hover:border-[#064AAD]/20 hover:bg-[#064AAD]/5 hover:text-[#064AAD] active:bg-[#064AAD]/10 dark:text-slate-200 dark:hover:bg-[#064AAD]/15 dark:hover:text-white"
                            @click="
                                (e) => {
                                    onInPageNavClick(e, item.href);
                                    closeMobileNav();
                                }
                            "
                        >
                            {{ item.label }}
                            <span class="text-slate-400 dark:text-slate-500" aria-hidden="true">→</span>
                        </a>
                    </template>
                    <div class="border-t border-slate-200/80 pt-3 dark:border-slate-800">
                        <Link
                            v-if="canLogin && !$page.props.auth?.user"
                            :href="route('login')"
                            class="flex w-full items-center justify-center rounded-2xl border border-slate-200 py-3 text-sm font-semibold text-slate-800 dark:border-slate-700 dark:text-slate-100"
                            @click="closeMobileNav"
                        >
                            Giriş
                        </Link>
                        <Link
                            v-if="canLogin && $page.props.auth?.user"
                            :href="route('dashboard')"
                            class="mt-2 flex w-full items-center justify-center rounded-2xl border border-slate-200 py-3 text-sm font-semibold text-slate-800 dark:border-slate-700 dark:text-slate-100"
                            @click="closeMobileNav"
                        >
                            Panele git
                        </Link>
                    </div>
                </nav>
            </div>
        </header>

        <!-- Hero -->
        <section
            id="hero"
            class="relative overflow-hidden bg-gradient-to-br from-[#021f4a] via-[#064AAD] to-[#031a38] px-4 pb-20 pt-16 sm:px-6 sm:pt-24 lg:px-8 lg:pb-28"
        >
            <div
                class="pointer-events-none absolute -left-32 top-0 h-96 w-96 rounded-full bg-[#064AAD]/40 blur-3xl"
            />
            <div
                class="pointer-events-none absolute -right-24 bottom-0 h-80 w-80 rounded-full bg-[#1CBF65]/30 blur-3xl"
            />
            <div
                class="pointer-events-none absolute inset-0 opacity-[0.15]"
                style="
                    background-image: radial-gradient(
                        circle at 1px 1px,
                        rgb(255 255 255 / 0.25) 1px,
                        transparent 0
                    );
                    background-size: 28px 28px;
                "
            />

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="flex flex-col items-center gap-12 lg:flex-row lg:items-center lg:gap-14">
                    <div class="w-full flex-1 text-center lg:max-w-xl lg:text-left xl:max-w-2xl">
                        <p
                            class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1.5 text-xs font-medium uppercase tracking-wide text-blue-100/95 lg:inline-flex"
                        >
                            <span class="h-1.5 w-1.5 rounded-full bg-[#1CBF65] shadow-[0_0_12px_rgba(28,191,101,0.85)]" />
                            Sigorta CRM
                        </p>
                        <h1
                            class="mt-6 text-balance text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-[2.75rem] xl:text-5xl"
                        >
                            Sigorta süreçlerinizi tek panelden yönetin
                        </h1>
                        <p
                            class="mx-auto mt-6 max-w-2xl text-pretty text-lg leading-relaxed text-blue-100/95 sm:text-xl lg:mx-0"
                        >
                            Poliçe, müşteri, tahsilat ve hasar yönetimini tek bir CRM ile yönetin.
                        </p>
                        <div
                            class="mt-10 flex flex-col items-stretch justify-center gap-4 sm:flex-row sm:flex-wrap sm:justify-center lg:justify-start"
                        >
                            <Link
                                v-if="!$page.props.auth?.user"
                                :href="route('demo.request')"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-white px-8 py-4 text-base font-semibold text-[#064AAD] shadow-xl shadow-black/20 transition hover:scale-[1.02] hover:bg-slate-50 sm:w-auto"
                            >
                                Ücretsiz dene
                            </Link>
                            <Link
                                v-else-if="$page.props.auth?.user"
                                :href="route('dashboard')"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-white px-8 py-4 text-base font-semibold text-[#064AAD] shadow-xl shadow-black/20 transition hover:scale-[1.02] hover:bg-slate-50 sm:w-auto"
                            >
                                Panele git
                            </Link>
                            <Link
                                :href="route('demo.request')"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-white/20 bg-white/5 px-8 py-4 text-base font-semibold text-white backdrop-blur-sm transition hover:border-white/30 hover:bg-white/10 sm:w-auto"
                            >
                                Demo talep et
                            </Link>
                        </div>
                    </div>

                    <div class="w-full flex-1 lg:flex lg:min-w-0 lg:max-w-[min(100%,32rem)] lg:justify-end xl:max-w-[36rem]">
                        <div class="relative w-full">
                            <div
                                v-if="heroScreenshotUrl"
                                class="relative w-full overflow-hidden rounded-2xl border border-white/20 bg-white/[0.04] shadow-2xl shadow-black/40 ring-1 ring-white/10"
                            >
                                <img
                                    :src="heroScreenshotUrl"
                                    alt=""
                                    class="h-auto w-full object-cover object-top"
                                    loading="eager"
                                    decoding="async"
                                />
                                <!-- Mobil: görselin alt çerçevesi gibi tam genişlik şerit -->
                                <div
                                    class="absolute inset-x-0 bottom-0 z-10 border-t-2 border-white/35 bg-gradient-to-br from-[#1CBF65] via-[#19b35d] to-[#148a4a] px-4 py-2.5 text-center shadow-[0_-8px_24px_rgba(0,0,0,0.2)] ring-1 ring-inset ring-white/20 lg:hidden"
                                >
                                    <span
                                        class="text-sm font-extrabold uppercase tracking-wide text-white drop-shadow-sm"
                                    >
                                        İlk Ay Ücretsiz
                                    </span>
                                </div>
                            </div>
                            <div
                                v-else
                                class="relative aspect-[16/10] w-full max-w-md overflow-hidden rounded-2xl border border-dashed border-white/20 bg-white/[0.03] lg:max-w-none"
                                aria-hidden="true"
                            >
                                <div
                                    class="absolute inset-x-0 bottom-0 z-10 border-t-2 border-dashed border-white/25 bg-gradient-to-br from-[#1CBF65]/90 via-[#19b35d]/90 to-[#148a4a]/90 px-4 py-2.5 text-center lg:hidden"
                                >
                                    <span class="text-sm font-extrabold uppercase tracking-wide text-white drop-shadow-sm">
                                        İlk Ay Ücretsiz
                                    </span>
                                </div>
                            </div>
                            <!-- Masaüstü: sağ üstte kart rozet -->
                            <div
                                class="pointer-events-none absolute right-4 top-0 z-10 hidden w-auto max-w-none -translate-y-3 lg:block"
                            >
                                <div
                                    class="inline-flex items-center justify-center rounded-xl border-2 border-white/40 bg-gradient-to-br from-[#1CBF65] via-[#19b35d] to-[#148a4a] px-5 py-3 text-center shadow-[0_12px_40px_rgba(28,191,101,0.45)] ring-2 ring-black/10"
                                >
                                    <span
                                        class="text-base font-extrabold uppercase tracking-wide text-white drop-shadow-sm"
                                    >
                                        İlk Ay Ücretsiz
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Problem / Çözüm -->
        <section
            id="problem"
            class="scroll-mt-[5.5rem] border-b border-slate-200 bg-white px-4 py-20 dark:border-slate-800 dark:bg-slate-900 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-6xl">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-4xl">
                        Sık karşılaşılan sorunlar, tek çözüm
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-slate-600 dark:text-slate-400">
                        Dağınık müşteri yönetimi, kaçan yenilemeler ve manuel takip ekiplerinizi yavaşlatır.
                    </p>
                </div>

                <div class="mt-14 grid gap-6 md:grid-cols-3">
                    <div
                        class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800/50"
                    >
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-rose-100 dark:bg-rose-950/50">
                            <UserGroupIcon class="h-6 w-6 text-rose-600 dark:text-rose-400" />
                        </div>
                        <h3 class="mt-4 font-semibold text-slate-900 dark:text-white">Dağınık müşteri yönetimi</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                            Bilgiler e-posta ve Excel arasında kaybolur; ekip görünürlüğü düşer.
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800/50"
                    >
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-950/50">
                            <BellAlertIcon class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                        </div>
                        <h3 class="mt-4 font-semibold text-slate-900 dark:text-white">Kaçan yenilemeler</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                            Hatırlatma olmadan poliçe süreleri risk ve gelir kaybına dönüşür.
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800/50"
                    >
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-200 dark:bg-slate-700">
                            <ArrowPathIcon class="h-6 w-6 text-slate-700 dark:text-slate-300" />
                        </div>
                        <h3 class="mt-4 font-semibold text-slate-900 dark:text-white">Manuel takip</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                            Tekrarlayan işler zaman alır; hata payı ve operasyon yükü artar.
                        </p>
                    </div>
                </div>

                <div
                    class="mt-12 rounded-2xl border border-[#064AAD]/20 bg-gradient-to-br from-[#064AAD]/5 to-[#1CBF65]/10 p-8 text-center shadow-lg shadow-[#064AAD]/10 dark:border-[#064AAD]/30 dark:from-[#064AAD]/15 dark:to-[#1CBF65]/10"
                >
                    <ShieldCheckIcon class="mx-auto h-10 w-10 text-[#064AAD] dark:text-[#4d8fd9]" />
                    <p class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">
                        Çözüm: <span class="text-[#064AAD] dark:text-[#4d8fd9]">{{ brand }}</span>
                    </p>
                    <p class="mx-auto mt-2 max-w-[40rem] text-slate-600 dark:text-slate-400">
                        Tüm süreçleri tek CRM’de birleştirin; müşteri ve poliçe verisini tek kaynakta tutun,
                        otomatik hatırlatmalarla yenilemeleri yakalayın.
                    </p>
                </div>
            </div>
        </section>

        <!-- Modüller -->
        <section id="modules" class="scroll-mt-[5.5rem] bg-slate-50 px-4 py-20 dark:bg-slate-950 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-6xl">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-4xl">
                        Modüller
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-slate-600 dark:text-slate-400">
                        İhtiyacınız olan modüller tek platformda; ekip içi işbirliği ve hızlı aksiyon.
                    </p>
                </div>

                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="m in modules"
                        :key="m.title"
                        class="group rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition hover:border-[#064AAD]/30 hover:shadow-lg hover:shadow-[#064AAD]/10 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-[#064AAD]/40"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-[#064AAD] to-[#1CBF65] text-white shadow-lg shadow-[#064AAD]/30 transition group-hover:scale-105"
                        >
                            <component :is="m.icon" class="h-6 w-6" />
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-slate-900 dark:text-white">
                            {{ m.title }}
                        </h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                            {{ m.desc }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Ürün ekran görüntüsü (mockup) -->
        <section id="product" class="border-y border-slate-200 bg-white px-4 py-20 dark:border-slate-800 dark:bg-slate-900 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-6xl">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-4xl">
                        Ürünü keşfedin
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-slate-600 dark:text-slate-400">
                        Dashboard ve CRM ekranlarıyla operasyonları tek bakışta görün.
                    </p>
                </div>

                <div class="mt-14 grid gap-8 lg:grid-cols-2">
                    <!-- Dashboard mock -->
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-100 shadow-xl dark:border-slate-700 dark:bg-slate-800"
                    >
                        <div class="flex items-center gap-2 border-b border-slate-200/80 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900">
                            <div class="flex gap-1.5">
                                <span class="h-3 w-3 rounded-full bg-rose-400/80" />
                                <span class="h-3 w-3 rounded-full bg-amber-400/80" />
                                <span class="h-3 w-3 rounded-full bg-[#1CBF65]/90" />
                            </div>
                            <span class="ml-2 text-xs font-medium text-slate-500">Dashboard</span>
                        </div>
                        <div class="space-y-4 p-5">
                            <div class="grid grid-cols-3 gap-3">
                                <div
                                    v-for="i in 3"
                                    :key="i"
                                    class="h-20 rounded-xl bg-gradient-to-br from-[#064AAD]/35 to-[#1CBF65]/25"
                                />
                            </div>
                            <div class="h-40 rounded-xl bg-gradient-to-br from-slate-200/80 to-slate-200/40 p-4 dark:from-slate-700/80 dark:to-slate-700/40">
                                <div class="flex h-full items-end justify-between gap-1.5">
                                    <div
                                        v-for="(px, idx) in [48, 72, 56, 88, 64, 76]"
                                        :key="idx"
                                        class="min-w-0 flex-1 rounded-t-md bg-[#064AAD]/85 shadow-sm"
                                        :style="{ height: `${px}px` }"
                                    />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="h-24 rounded-xl bg-white/80 shadow-sm dark:bg-slate-900/80" />
                                <div class="h-24 rounded-xl bg-white/80 shadow-sm dark:bg-slate-900/80" />
                            </div>
                        </div>
                    </div>

                    <!-- CRM mock -->
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-100 shadow-xl dark:border-slate-700 dark:bg-slate-800"
                    >
                        <div class="flex items-center gap-2 border-b border-slate-200/80 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900">
                            <div class="flex gap-1.5">
                                <span class="h-3 w-3 rounded-full bg-rose-400/80" />
                                <span class="h-3 w-3 rounded-full bg-amber-400/80" />
                                <span class="h-3 w-3 rounded-full bg-[#1CBF65]/90" />
                            </div>
                            <span class="ml-2 text-xs font-medium text-slate-500">Müşteri &amp; CRM</span>
                        </div>
                        <div class="space-y-3 p-5">
                            <div class="flex gap-3">
                                <div class="h-10 w-10 shrink-0 rounded-xl bg-gradient-to-br from-[#064AAD] to-[#1CBF65]" />
                                <div class="flex-1 space-y-2">
                                    <div class="h-3 max-w-[240px] rounded bg-slate-300 dark:bg-slate-600" style="width: 60%" />
                                    <div class="h-3 max-w-[160px] rounded bg-slate-200 dark:bg-slate-700" style="width: 40%" />
                                </div>
                            </div>
                            <div class="space-y-2 rounded-xl border border-slate-200/80 bg-white p-3 dark:border-slate-700 dark:bg-slate-900">
                                <div
                                    v-for="j in 4"
                                    :key="j"
                                    class="flex items-center gap-3 border-b border-slate-100 py-2 last:border-0 dark:border-slate-800"
                                >
                                    <div class="h-8 w-8 rounded-lg bg-slate-200 dark:bg-slate-700" />
                                    <div class="flex-1 space-y-1.5">
                                        <div class="h-2.5 rounded bg-slate-200 dark:bg-slate-700" style="width: 80%" />
                                        <div class="h-2 rounded bg-slate-100 dark:bg-slate-800" style="width: 55%" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Özellikler -->
        <section id="features" class="bg-gradient-to-b from-slate-50 to-white px-4 py-20 dark:from-slate-950 dark:to-slate-900 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-6xl">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-4xl">
                        Öne çıkan özellikler
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-slate-600 dark:text-slate-400">
                        Operasyonu hızlandıran, güvenilir ve ölçülebilir yetenekler.
                    </p>
                </div>

                <ul class="mt-14 grid gap-6 sm:grid-cols-2">
                    <li
                        class="flex gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#064AAD]/10 dark:bg-[#064AAD]/20">
                            <BellAlertIcon class="h-6 w-6 text-[#064AAD] dark:text-[#4d8fd9]" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-900 dark:text-white">Otomatik hatırlatma</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                Yenileme ve görevler için zamanında bildirim; kaçan fırsatları azaltın.
                            </p>
                        </div>
                    </li>
                    <li
                        class="flex gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#1CBF65]/10 dark:bg-[#1CBF65]/15">
                            <CurrencyDollarIcon class="h-6 w-6 text-[#148a4a] dark:text-[#4dde8c]" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-900 dark:text-white">Komisyon takibi</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                Gelir ve komisyon görünürlüğüyle finansal kontrolü güçlendirin.
                            </p>
                        </div>
                    </li>
                    <li
                        class="flex gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#1CBF65]/10 dark:bg-[#1CBF65]/15">
                            <BanknotesIcon class="h-6 w-6 text-[#148a4a] dark:text-[#4dde8c]" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-900 dark:text-white">Taksit yönetimi</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                Planlı ödemeler ve tahsilat takibi ile nakit akışını netleştirin.
                            </p>
                        </div>
                    </li>
                    <li
                        class="flex gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#064AAD]/10 dark:bg-[#064AAD]/20">
                            <ChatBubbleLeftRightIcon class="h-6 w-6 text-[#064AAD] dark:text-[#4d8fd9]" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-900 dark:text-white">WhatsApp entegrasyonu</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                Müşteri iletişimini hızlandırın; kayıtları CRM ile ilişkilendirin.
                            </p>
                        </div>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Fiyatlandırma -->
        <section
            id="pricing"
            class="scroll-mt-[5.5rem] border-t border-slate-200 bg-white px-4 py-20 dark:border-slate-800 dark:bg-slate-900 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-6xl">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-4xl">
                        Esnek fiyatlandırma
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-slate-600 dark:text-slate-400">
                        İhtiyacınıza uygun dönem; kurumsal özel teklif için bizimle iletişime geçin.
                    </p>
                </div>

                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="plan in pricingPlans"
                        :key="plan.period"
                        class="relative flex flex-col rounded-2xl border p-6 shadow-sm transition hover:shadow-lg dark:border-slate-800"
                        :class="
                            plan.highlight
                                ? 'border-[#064AAD]/40 bg-gradient-to-b from-[#064AAD]/5 to-white ring-2 ring-[#064AAD]/20 dark:border-[#064AAD]/50 dark:from-[#064AAD]/15 dark:to-slate-900'
                                : 'border-slate-200 bg-slate-50/50 dark:bg-slate-800/80'
                        "
                    >
                        <span
                            v-if="plan.badge"
                            class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-gradient-to-r from-[#064AAD] to-[#0558c9] px-3 py-0.5 text-xs font-semibold text-white shadow"
                        >
                            {{ plan.badge }}
                        </span>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Lisans</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">
                            {{ plan.price_label?.trim() ? plan.price_label : 'Özel teklif' }}
                        </p>
                        <p class="mt-2 text-lg font-semibold text-[#064AAD] dark:text-[#4d8fd9]">
                            {{ plan.period }}
                        </p>
                        <ul class="mt-4 flex-1 space-y-2.5 text-left text-sm">
                            <li
                                v-for="(feature, fi) in pricingPackageFeatures"
                                :key="fi"
                                class="flex gap-2.5"
                                :class="
                                    fi === pricingPackageFeatures.length - 1
                                        ? 'mt-3 rounded-lg border border-[#1CBF65]/30 bg-[#1CBF65]/5 px-2.5 py-2 dark:border-[#1CBF65]/25 dark:bg-[#1CBF65]/10'
                                        : ''
                                "
                            >
                                <CheckIcon
                                    class="mt-0.5 h-4 w-4 shrink-0 text-[#1CBF65] dark:text-[#4dde8c]"
                                    aria-hidden="true"
                                />
                                <span
                                    :class="
                                        fi === pricingPackageFeatures.length - 1
                                            ? 'font-semibold text-[#064AAD] dark:text-[#4d8fd9]'
                                            : 'text-slate-700 dark:text-slate-300'
                                    "
                                >
                                    {{ feature }}
                                </span>
                            </li>
                        </ul>
                        <Link
                            :href="route('demo.request')"
                            class="mt-6 inline-flex w-full items-center justify-center rounded-full bg-gradient-to-r from-[#064AAD] to-[#0558c9] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#064AAD]/30 transition hover:scale-[1.02] hover:shadow-xl hover:shadow-[#064AAD]/40 active:scale-[0.98]"
                        >
                            Ücretsiz dene
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section
            id="demo"
            class="scroll-mt-[5.5rem] bg-gradient-to-r from-[#064AAD] via-[#0558c9] to-[#043d7a] px-4 py-16 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-4xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    Ekibinizi hızlandırmaya hazır mısınız?
                </h2>
                <p class="mt-4 text-lg text-blue-100">
                    Hemen başlayın veya demo ile ürünü yakından görün.
                </p>
                <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row sm:gap-5">
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-white px-8 py-4 text-base font-semibold text-[#064AAD] shadow-xl transition hover:scale-[1.02] hover:bg-slate-50 sm:w-auto"
                    >
                        Hemen başla
                    </Link>
                    <Link
                        v-else-if="canLogin"
                        :href="route('login')"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-white px-8 py-4 text-base font-semibold text-[#064AAD] shadow-xl transition hover:scale-[1.02] hover:bg-slate-50 sm:w-auto"
                    >
                        Giriş yap
                    </Link>
                    <Link
                        :href="route('demo.request')"
                        class="inline-flex w-full items-center justify-center rounded-xl border-2 border-[#1CBF65]/80 bg-[#1CBF65]/15 px-8 py-4 text-base font-semibold text-white backdrop-blur-sm transition hover:border-[#1CBF65] hover:bg-[#1CBF65]/30 sm:w-auto"
                    >
                        Demo al
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-slate-950 px-4 py-14 text-slate-400 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-6xl">
                <div class="flex flex-col gap-10 md:flex-row md:justify-between">
                    <div class="max-w-sm">
                        <div class="flex items-center gap-2">
                            <img
                                v-if="appSettings.logo_url"
                                :src="appSettings.logo_url"
                                alt=""
                                class="h-8 w-auto max-w-[140px] object-contain brightness-0 invert"
                            />
                            <div
                                v-else
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-[#064AAD] to-[#1CBF65] p-1.5 shadow-lg shadow-[#064AAD]/25"
                            >
                                <ApplicationLogo class="h-full w-full text-white" />
                            </div>
                        </div>
                        <p class="mt-4 text-sm leading-relaxed">
                            Sigorta operasyonlarınızı modern bir CRM ile tek panelden yönetin.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-10 sm:grid-cols-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                Ürün
                            </p>
                            <ul class="mt-4 space-y-2 text-sm">
                                <li>
                                    <a
                                        href="#modules"
                                        class="transition hover:text-[#4d8fd9]"
                                        @click="onInPageNavClick($event, '#modules')"
                                    >Modüller</a>
                                </li>
                                <li>
                                    <a
                                        href="#pricing"
                                        class="transition hover:text-[#4d8fd9]"
                                        @click="onInPageNavClick($event, '#pricing')"
                                    >Fiyatlandırma</a>
                                </li>
                                <li>
                                    <Link :href="route('demo.request')" class="transition hover:text-[#4d8fd9]">Demo</Link>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                Hesap
                            </p>
                            <ul class="mt-4 space-y-2 text-sm">
                                <li v-if="canLogin">
                                    <Link :href="route('login')" class="transition hover:text-[#4d8fd9]">Giriş</Link>
                                </li>
                                <li v-if="canRegister">
                                    <Link :href="route('register')" class="transition hover:text-[#4d8fd9]">Kayıt ol</Link>
                                </li>
                                <li>
                                    <Link :href="route('guide')" class="transition hover:text-[#4d8fd9]">
                                        Kullanım kılavuzu
                                    </Link>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                İletişim
                            </p>
                            <ul class="mt-4 space-y-3 text-sm leading-relaxed text-slate-400">
                                <li>
                                    <Link :href="route('demo.request')" class="transition hover:text-[#1CBF65]">
                                        Demo talep formu
                                    </Link>
                                </li>
                                <li>
                                    <span class="text-slate-500">Satış &amp; Destek:</span>
                                    <a
                                        href="https://wa.me/905498384300"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="ml-1 text-slate-300 transition hover:text-[#1CBF65]"
                                        title="WhatsApp ile yazın"
                                    >
                                        +90 549 838 43 00
                                    </a>
                                </li>
                                <li>
                                    <span class="text-slate-500">Mail:</span>
                                    <a
                                        href="mailto:info@policeasist.com.tr"
                                        class="ml-1 text-slate-300 transition hover:text-[#1CBF65]"
                                    >
                                        info@policeasist.com.tr
                                    </a>
                                </li>
                                <li class="text-slate-400">
                                    <span class="text-slate-500">Adres:</span>
                                    Esentepe Mah. Talatpaşa Cad. No: 5 / 1 Şişli/İstanbul
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div
                    class="mt-12 border-t border-slate-800 pt-8 text-center text-xs text-slate-500"
                >
                    © {{ new Date().getFullYear() }} {{ brand }}. Tüm hakları saklıdır.
                </div>
            </div>
        </footer>

        <a
            href="https://wa.me/905498384300"
            target="_blank"
            rel="noopener noreferrer"
            class="fixed bottom-5 right-5 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg shadow-black/20 ring-2 ring-white/30 transition hover:scale-105 hover:bg-[#20bd5a] hover:shadow-xl focus:outline-none focus-visible:ring-4 focus-visible:ring-[#25D366]/50 md:bottom-6 md:right-6"
            aria-label="WhatsApp: +90 549 838 43 00"
            title="WhatsApp ile yazın"
        >
            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path
                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.881 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"
                />
            </svg>
        </a>
    </div>
</template>
