<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const appSettings = computed(() => page.props.appSettings ?? {});

const toc = [
    { id: 'baslangic', label: 'Başlangıç ve roller' },
    { id: 'arayuz', label: 'Arayüz ve sol menü' },
    { id: 'ozet', label: 'Özet (gösterge paneli)' },
    { id: 'musteriler', label: 'Müşteriler' },
    { id: 'leadler', label: 'Leadler ve Kanban' },
    { id: 'policeler', label: 'Poliçeler' },
    { id: 'tahsilat', label: 'Tahsilat ve ödemeler' },
    { id: 'hasarlar', label: 'Hasar dosyaları' },
    { id: 'gorevler', label: 'Görevler ve takvim' },
    { id: 'raporlar', label: 'Raporlar' },
    { id: 'tanimlar', label: 'Tanımlar' },
    { id: 'sistem', label: 'Sistem yönetimi (admin)' },
    { id: 'profil', label: 'Profil ve hesap' },
];

function prefersReducedMotion() {
    if (typeof window === 'undefined' || !window.matchMedia) {
        return false;
    }
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function onTocClick(e, href) {
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
    <div class="min-h-screen bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <Head title="Kullanım kılavuzu">
            <meta
                v-if="appSettings.site_description"
                name="description"
                content="PoliçeAsist sigorta CRM kullanım kılavuzu: modüller, menüler ve günlük işlemler."
            />
            <link v-if="appSettings.favicon_url" rel="icon" :href="appSettings.favicon_url" />
        </Head>

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
                <Link
                    href="/"
                    class="rounded-full bg-gradient-to-r from-[#064AAD] to-[#0558c9] px-4 py-1.5 text-sm font-semibold text-white shadow-md shadow-[#064AAD]/25 transition hover:scale-[1.02] hover:shadow-lg hover:shadow-[#064AAD]/35 active:scale-[0.98]"
                >
                    Ana Sayfaya Dön
                </Link>
            </div>
        </header>

        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 lg:grid-cols-2 lg:gap-16 lg:px-8 lg:py-16">
            <div class="flex flex-col justify-start lg:sticky lg:top-24 lg:self-start">
                <p
                    class="inline-flex w-fit items-center gap-2 rounded-full border border-[#064AAD]/20 bg-[#064AAD]/5 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-[#064AAD] dark:border-[#064AAD]/40 dark:bg-[#064AAD]/15 dark:text-[#4d8fd9]"
                >
                    Kılavuz
                </p>
                <h1 class="mt-6 text-balance text-3xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-4xl lg:text-5xl">
                    PoliçeAsist kullanım kılavuzu
                </h1>
                <p class="mt-5 max-w-lg text-pretty text-lg leading-relaxed text-slate-600 dark:text-slate-400">
                    Bu sayfada yazılımın ana özelliklerini, sol menüde nerede olduklarını ve tipik işlemleri nasıl
                    yapacağınızı özetledik. CRM’e giriş yaptıktan sonra aynı isimlerle menüde bulabilirsiniz.
                </p>
                <nav
                    class="mt-8 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                    aria-label="İçindekiler"
                >
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        İçindekiler
                    </p>
                    <ul class="mt-3 space-y-1 text-sm">
                        <li v-for="item in toc" :key="item.id">
                            <a
                                :href="`#${item.id}`"
                                class="block rounded-lg px-2 py-1.5 text-slate-700 transition hover:bg-[#064AAD]/10 hover:text-[#064AAD] dark:text-slate-300 dark:hover:bg-[#064AAD]/20 dark:hover:text-[#4d8fd9]"
                                @click="onTocClick($event, `#${item.id}`)"
                            >
                                {{ item.label }}
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xl shadow-slate-900/[0.06] dark:border-slate-700 dark:bg-slate-900 sm:p-8"
            >
                <div class="space-y-14 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                    <section id="baslangic" class="scroll-mt-24">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Başlangıç ve roller</h2>
                        <p class="mt-3">
                            <strong class="text-slate-800 dark:text-slate-200">Giriş:</strong> Hesabınızla oturum açtıktan
                            sonra rolünüze göre yönlendirilirsiniz.
                        </p>
                        <ul class="mt-3 list-inside list-disc space-y-2">
                            <li>
                                <strong class="text-slate-800 dark:text-slate-200">Sistem yöneticisi</strong> kullanıcıları
                                CRM panellerine değil; <strong>yönetim paneline</strong> (site ayarları, kullanıcılar, demo
                                talepleri) yönlendirilir.
                            </li>
                            <li>
                                <strong class="text-slate-800 dark:text-slate-200">Admin, satış ve operasyon</strong>
                                rolleri sol menüdeki <strong>Özet, Müşteriler, Leadler, Poliçeler</strong> vb. modüllere
                                erişir.
                            </li>
                        </ul>
                    </section>

                    <section id="arayuz" class="scroll-mt-24">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Arayüz ve sol menü</h2>
                        <p class="mt-3">
                            CRM ekranında sol tarafta daraltılabilir bir menü bulunur. Üstte logo alanına tıklayarak
                            <strong class="text-slate-800 dark:text-slate-200">özet sayfasına</strong> dönebilirsiniz.
                            Mobilde menüyü üst köşedeki ikonla açıp kapatabilirsiniz.
                        </p>
                        <p class="mt-3">
                            Sayfa başlıkları ve sağ üstteki kullanıcı menüsünden
                            <strong class="text-slate-800 dark:text-slate-200">profil</strong> ve çıkış seçeneklerine
                            ulaşırsınız.
                        </p>
                    </section>

                    <section id="ozet" class="scroll-mt-24">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Özet (gösterge paneli)</h2>
                        <p class="mt-3">
                            Menü: <strong class="text-slate-800 dark:text-slate-200">Özet</strong> —
                            <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-slate-800">/dashboard</code>
                        </p>
                        <p class="mt-3">
                            Operasyonunuzun genel görünümü: açık leadler, yaklaşan görevler, poliçe / tahsilat / hasar
                            özetleri gibi kartlar burada toplanır. Detaya gitmek için ilgili sayfaya yönlendiren
                            bağlantıları kullanın.
                        </p>
                    </section>

                    <section id="musteriler" class="scroll-mt-24">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Müşteriler</h2>
                        <p class="mt-3">
                            Menü: <strong class="text-slate-800 dark:text-slate-200">Müşteriler</strong> — liste ve yeni
                            kayıt için üstteki eylemleri kullanın.
                        </p>
                        <ul class="mt-3 list-inside list-disc space-y-2">
                            <li>
                                <strong class="text-slate-800 dark:text-slate-200">Yeni müşteri:</strong> listeden
                                “Yeni” / oluştur akışı ile bireysel veya kurumsal müşteri açın; iletişim ve adres
                                bilgilerini müşteri detayından tamamlayın.
                            </li>
                            <li>
                                <strong class="text-slate-800 dark:text-slate-200">Detay sayfası:</strong> notlar,
                                belge yükleme, aktivite ve bağlı poliçeler gibi bilgiler tek profilde toplanır.
                            </li>
                        </ul>
                    </section>

                    <section id="leadler" class="scroll-mt-24">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Leadler ve Kanban</h2>
                        <p class="mt-3">
                            Menü: <strong class="text-slate-800 dark:text-slate-200">Leadler</strong> (liste görünümü) ve
                            <strong class="text-slate-800 dark:text-slate-200">Kanban</strong> (aşama sütunları).
                        </p>
                        <ul class="mt-3 list-inside list-disc space-y-2">
                            <li>
                                Listeden yeni lead oluşturun; kaydı açarak aşama, not ve iletişim bilgilerini güncelleyin.
                            </li>
                            <li>
                                Kanban’da kartları sürükleyerek aşama değiştirebilirsiniz; pipeline görünümü satış
                                takibini hızlandırır.
                            </li>
                        </ul>
                    </section>

                    <section id="policeler" class="scroll-mt-24">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Poliçeler</h2>
                        <p class="mt-3">
                            Menü: <strong class="text-slate-800 dark:text-slate-200">Poliçeler</strong> — müşteriye bağlı
                            poliçe kayıtları, yenileme ve taksit planı burada yönetilir.
                        </p>
                        <ul class="mt-3 list-inside list-disc space-y-2">
                            <li>
                                Yeni poliçe oluştururken müşteri, şirket, poliçe türü, başlangıç / bitiş ve prim bilgilerini
                                girin.
                            </li>
                            <li>
                                <strong class="text-slate-800 dark:text-slate-200">Taksit planı</strong> ile ödeme
                                vadelerini tanımlayın; tahsilat modülüyle uyumludur.
                            </li>
                            <li>
                                <strong class="text-slate-800 dark:text-slate-200">Yenileme</strong> ve hatırlatma
                                işlemleri poliçe detayındaki ilgili bölümlerden yapılır; not ve belge ekleyebilirsiniz.
                            </li>
                        </ul>
                    </section>

                    <section id="tahsilat" class="scroll-mt-24">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Tahsilat ve ödemeler</h2>
                        <p class="mt-3">
                            Menü: <strong class="text-slate-800 dark:text-slate-200">Tahsilat</strong> — bekleyen ve
                            yapılan ödemeleri listeler; yeni ödeme kaydı oluşturarak taksitlerle ilişkilendirebilirsiniz.
                        </p>
                        <p class="mt-3">
                            Ödeme girişinde poliçe / taksit seçimi ve tutar bilgilerini kontrol edin; liste ekranından
                            filtreleme ile borç durumunu izleyin.
                        </p>
                    </section>

                    <section id="hasarlar" class="scroll-mt-24">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Hasar dosyaları</h2>
                        <p class="mt-3">
                            Menü: <strong class="text-slate-800 dark:text-slate-200">Hasarlar</strong> — hasar kaydı
                            oluşturup poliçe ile ilişkilendirin; durum güncellemesi, not ve evrak ekleri dosya üzerinden
                            yönetilir.
                        </p>
                    </section>

                    <section id="gorevler" class="scroll-mt-24">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Görevler ve takvim</h2>
                        <p class="mt-3">
                            Menü: <strong class="text-slate-800 dark:text-slate-200">Görevler</strong> ve
                            <strong class="text-slate-800 dark:text-slate-200">Takvim</strong> — ekip işlerini tarih ve
                            öncelikle planlayın; toplu işlemler ve ek dosyalar görev detayından kullanılabilir.
                        </p>
                    </section>

                    <section id="raporlar" class="scroll-mt-24">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Raporlar</h2>
                        <p class="mt-3">
                            Menü: <strong class="text-slate-800 dark:text-slate-200">Raporlar</strong> — satış, poliçe,
                            tahsilat ve operasyon metriklerini özet görünümlerle incelersiniz. Dönem ve filtre seçenekleri
                            ekrandaki kontrollere göre değişebilir.
                        </p>
                    </section>

                    <section id="tanimlar" class="scroll-mt-24">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Tanımlar</h2>
                        <p class="mt-3">
                            Menü grubu: <strong class="text-slate-800 dark:text-slate-200">Tanımlar</strong> —
                            <strong class="text-slate-800 dark:text-slate-200">Tanımlar merkezi</strong> sigorta şirketi
                            ile poliçe türü eşleşmelerini ve komisyonları yapılandırmanızı sağlar.
                        </p>
                        <ul class="mt-3 list-inside list-disc space-y-2">
                            <li>
                                <strong class="text-slate-800 dark:text-slate-200">Sigorta firmaları</strong> ve
                                <strong class="text-slate-800 dark:text-slate-200">Poliçe türleri</strong> listelerinden
                                kayıt ekleyip düzenleyin; poliçe oluştururken bu tanımlar seçim listelerinde görünür.
                            </li>
                        </ul>
                    </section>

                    <section id="sistem" class="scroll-mt-24">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Sistem yönetimi (admin)</h2>
                        <p class="mt-3">
                            Yalnızca <strong class="text-slate-800 dark:text-slate-200">sistem yöneticisi</strong> rolü:
                            site başlığı, logo, landing içerikleri, fiyatlandırma planları, demo talepleri listesi ve
                            kullanıcı yönetimi bu panelden yapılır. Adres yapısı genelde
                            <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-slate-800">/admin</code> altında
                            başlar.
                        </p>
                    </section>

                    <section id="profil" class="scroll-mt-24">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Profil ve hesap</h2>
                        <p class="mt-3">
                            Sağ üstteki kullanıcı menüsünden <strong class="text-slate-800 dark:text-slate-200">Profil</strong>
                            sayfasına giderek ad, e-posta ve şifre güncellemesi yapabilir; hesabınızı güvenli şekilde
                            kapatabilirsiniz.
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </div>
</template>
