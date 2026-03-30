<script setup>
import FlashToast from '@/Components/FlashToast.vue';
import PaToastHost from '@/Components/PaToastHost.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    ArrowLeftOnRectangleIcon,
    Bars3Icon,
    BellIcon,
    BuildingLibraryIcon,
    BuildingOffice2Icon,
    CalendarDaysIcon,
    ChartBarIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    ClipboardDocumentListIcon,
    Cog6ToothIcon,
    CreditCardIcon,
    RectangleStackIcon,
    ShieldCheckIcon,
    UserGroupIcon,
    UserIcon,
} from '@heroicons/vue/24/outline';

const collapsed = ref(false);
const mobileOpen = ref(false);
const page = usePage();

const navGroups = [
    {
        key: 'pipeline',
        items: [
            { name: 'Özet', href: 'dashboard', routeName: 'dashboard', icon: ChartBarIcon },
            { name: 'Müşteriler', href: 'customers.index', routeName: 'customers.*', icon: UserGroupIcon },
            { name: 'Leadler', href: 'leads.index', routeName: 'leads.index', icon: ClipboardDocumentListIcon },
            { name: 'Kanban', href: 'leads.kanban', routeName: 'leads.kanban', icon: ClipboardDocumentListIcon },
        ],
    },
    {
        key: 'policies',
        items: [
            { name: 'Poliçeler', href: 'policies.index', routeName: 'policies.*', icon: ShieldCheckIcon },
            { name: 'Tahsilat', href: 'payments.index', routeName: 'payments.*', icon: CreditCardIcon },
            { name: 'Hasarlar', href: 'claims.index', routeName: 'claims.*', icon: BuildingOffice2Icon },
        ],
    },
    {
        key: 'ops',
        items: [
            { name: 'Görevler', href: 'tasks.index', routeName: 'tasks.*', icon: ClipboardDocumentListIcon },
            { name: 'Takvim', href: 'tasks.calendar', routeName: 'tasks.calendar', icon: CalendarDaysIcon },
            { name: 'Raporlar', href: 'reports.index', routeName: 'reports.index', icon: ChartBarIcon },
        ],
    },
    {
        key: 'defs',
        label: 'Tanımlar',
        items: [
            {
                name: 'Tanımlar merkezi',
                href: 'definitions.index',
                routeName: 'definitions.index',
                icon: Cog6ToothIcon,
            },
            {
                name: 'Sigorta firmaları',
                href: 'insurance-companies.index',
                routeName: 'insurance-companies.*',
                icon: BuildingLibraryIcon,
            },
            {
                name: 'Poliçe türleri',
                href: 'policy-types.index',
                routeName: 'policy-types.*',
                icon: RectangleStackIcon,
            },
        ],
    },
];

function routeActive(pattern) {
    return route().current(pattern);
}

function linkClass(item) {
    const active = routeActive(item.routeName);
    if (active) {
        return 'bg-gradient-to-r from-indigo-600/14 via-violet-600/10 to-indigo-600/5 text-indigo-900 shadow-sm ring-1 ring-inset ring-indigo-500/20 dark:from-indigo-500/22 dark:via-violet-500/14 dark:to-indigo-500/5 dark:text-indigo-100 dark:ring-indigo-400/25';
    }
    return 'text-slate-600 hover:bg-slate-100/90 dark:text-slate-400 dark:hover:bg-slate-800/80';
}

function iconClass(item) {
    const active = routeActive(item.routeName);
    if (active) {
        return 'text-indigo-600 dark:text-indigo-300';
    }
    return 'text-slate-400 transition-colors group-hover:text-slate-600 dark:group-hover:text-slate-300';
}

const userInitial = computed(() => {
    const n = page.props.auth?.user?.name ?? '?';
    return n.charAt(0).toUpperCase();
});

const appSettings = computed(() => page.props.appSettings ?? {});
</script>

<template>
    <div>
        <FlashToast />
        <PaToastHost />

        <Head>
            <link v-if="appSettings.favicon_url" rel="icon" :href="appSettings.favicon_url" />
        </Head>

        <div
            class="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100/80 text-slate-900 antialiased dark:from-slate-950 dark:to-slate-950 dark:text-slate-100"
        >
            <div
                v-show="mobileOpen"
                class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-md transition-opacity lg:hidden"
                @click="mobileOpen = false"
            />

            <aside
                :class="[
                    'fixed inset-y-0 left-0 z-50 flex flex-col border-r border-slate-200/70 bg-white/95 shadow-lg shadow-slate-900/[0.06] backdrop-blur-xl transition-all duration-300 dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-none',
                    collapsed ? 'w-[4.5rem]' : 'w-64',
                    mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                ]"
            >
                <div
                    class="flex h-16 items-center gap-2 border-b border-slate-100/90 px-4 dark:border-slate-800"
                >
                    <Link
                        :href="route('dashboard')"
                        class="flex min-w-0 flex-1 items-center gap-3 rounded-xl p-1 transition hover:bg-slate-50 dark:hover:bg-slate-800/60"
                    >
                        <img
                            v-if="appSettings.logo_url"
                            :src="appSettings.logo_url"
                            :alt="appSettings.site_title || 'PoliçeAsist'"
                            class="h-10 w-auto max-w-[140px] shrink-0 object-contain"
                        />
                        <div
                            v-else
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white shadow-lg shadow-indigo-500/30"
                        >
                            PA
                        </div>
                    </Link>
                    <button
                        type="button"
                        class="hidden rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 active:scale-95 dark:hover:bg-slate-800 lg:inline-flex"
                        @click="collapsed = !collapsed"
                    >
                        <ChevronLeftIcon v-if="!collapsed" class="h-5 w-5" />
                        <ChevronRightIcon v-else class="h-5 w-5" />
                    </button>
                </div>

                <nav class="flex-1 overflow-y-auto px-2 py-4">
                    <template v-for="(group, gi) in navGroups" :key="group.key">
                        <div
                            v-if="gi > 0"
                            class="mx-2 my-3 border-t border-slate-100 dark:border-slate-800/80"
                            role="separator"
                        />
                        <p
                            v-if="!collapsed && group.label"
                            class="mb-2 mt-1 px-3 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                        >
                            {{ group.label }}
                        </p>
                        <div class="space-y-0.5">
                            <Link
                                v-for="item in group.items"
                                :key="item.href"
                                :href="route(item.href)"
                                class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 ease-out active:scale-[0.99]"
                                :class="linkClass(item)"
                                @click="mobileOpen = false"
                            >
                                <component
                                    :is="item.icon"
                                    class="h-5 w-5 shrink-0 transition-transform duration-200 group-hover:scale-105"
                                    :class="iconClass(item)"
                                />
                                <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
                            </Link>
                        </div>
                    </template>
                </nav>

                <div class="border-t border-slate-100 p-3 dark:border-slate-800">
                    <div
                        v-if="!collapsed"
                        class="rounded-2xl border border-slate-100/80 bg-gradient-to-br from-slate-50 to-white p-3 text-xs text-slate-600 shadow-sm dark:border-slate-800 dark:from-slate-800/60 dark:to-slate-900 dark:text-slate-400"
                    >
                        <p class="font-semibold text-slate-800 dark:text-slate-200">
                            Yenileme hatırlatması
                        </p>
                        <p class="mt-1 leading-relaxed">
                            Yaklaşan poliçeler için bildirimleri kuyruktan yönetin.
                        </p>
                    </div>
                </div>
            </aside>

            <div
                :class="[
                    'min-h-screen transition-[padding] duration-300',
                    collapsed ? 'lg:pl-[4.5rem]' : 'lg:pl-64',
                ]"
            >
                <header
                    class="sticky top-0 z-30 flex min-h-16 items-center justify-between gap-2 border-b border-slate-200/70 bg-white/75 px-3 py-2.5 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/75 sm:gap-3 sm:px-4 sm:py-3 lg:px-10"
                >
                    <div class="flex min-w-0 flex-1 items-center gap-2 sm:gap-3">
                        <button
                            type="button"
                            class="inline-flex shrink-0 rounded-xl p-2 text-slate-600 transition hover:bg-slate-100 active:scale-95 dark:text-slate-300 dark:hover:bg-slate-800 lg:hidden"
                            @click="mobileOpen = true"
                        >
                            <Bars3Icon class="h-6 w-6" />
                        </button>
                        <div
                            v-if="$slots.header"
                            class="min-w-0 flex-1 break-words [overflow-wrap:anywhere]"
                        >
                            <slot name="header" />
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-1.5 sm:gap-3">
                        <Dropdown align="right" width="64" content-classes="py-2 bg-white dark:bg-slate-800">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="relative inline-flex rounded-xl border border-slate-200/80 bg-white/90 p-2.5 text-slate-500 shadow-sm transition hover:scale-105 hover:border-slate-300 hover:text-slate-700 active:scale-95 dark:border-slate-700 dark:bg-slate-900/90 dark:hover:border-slate-600"
                                    aria-haspopup="true"
                                    aria-label="Bildirimler ve hızlı erişim"
                                >
                                    <BellIcon class="h-5 w-5" />
                                    <span
                                        class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 ring-2 ring-white dark:ring-slate-900"
                                    />
                                </button>
                            </template>
                            <template #content>
                                <div class="px-1">
                                    <p
                                        class="border-b border-slate-100 px-3 pb-2 pt-1 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:border-slate-700 dark:text-slate-500"
                                    >
                                        Hızlı erişim
                                    </p>
                                    <Link
                                        :href="
                                            route('policies.index', {
                                                expires_within_days: 30,
                                            })
                                        "
                                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700/60"
                                    >
                                        <ShieldCheckIcon class="h-5 w-5 shrink-0 text-indigo-500" />
                                        <span>
                                            <span class="font-medium">Yaklaşan poliçeler</span>
                                            <span class="block text-xs text-slate-500 dark:text-slate-400">
                                                Önümüzdeki 30 gün içinde bitecek
                                            </span>
                                        </span>
                                    </Link>
                                    <Link
                                        :href="route('tasks.index')"
                                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700/60"
                                    >
                                        <ClipboardDocumentListIcon class="h-5 w-5 shrink-0 text-violet-500" />
                                        <span>
                                            <span class="font-medium">Görevler</span>
                                            <span class="block text-xs text-slate-500 dark:text-slate-400">
                                                Takip listesi
                                            </span>
                                        </span>
                                    </Link>
                                    <Link
                                        :href="route('tasks.calendar')"
                                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700/60"
                                    >
                                        <CalendarDaysIcon class="h-5 w-5 shrink-0 text-sky-500" />
                                        <span>
                                            <span class="font-medium">Takvim</span>
                                            <span class="block text-xs text-slate-500 dark:text-slate-400">
                                                Randevu ve görevler
                                            </span>
                                        </span>
                                    </Link>
                                    <p class="border-t border-slate-100 px-3 pb-2 pt-3 text-xs leading-relaxed text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                        Tam bildirim merkezi yakında. Şimdilik yenileme ve operasyon için
                                        yukarıdaki kısayolları kullanın.
                                    </p>
                                </div>
                            </template>
                        </Dropdown>

                        <Dropdown align="right" width="56">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="flex items-center gap-2 rounded-2xl border border-slate-200/80 bg-white/90 py-1.5 pl-1.5 pr-3 shadow-sm transition hover:scale-[1.02] hover:border-slate-300 active:scale-[0.98] dark:border-slate-700 dark:bg-slate-900/90 dark:hover:border-slate-600"
                                >
                                    <span
                                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 text-sm font-semibold text-white shadow-md"
                                    >
                                        {{ userInitial }}
                                    </span>
                                    <span
                                        class="hidden max-w-[10rem] truncate text-left text-sm font-medium text-slate-700 dark:text-slate-200 sm:block"
                                    >
                                        {{ $page.props.auth.user.name }}
                                    </span>
                                </button>
                            </template>
                            <template #content>
                                <div
                                    class="border-b border-slate-100 px-4 py-3 dark:border-slate-700"
                                >
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">
                                        {{ $page.props.auth.user.name }}
                                    </p>
                                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                                        {{ $page.props.auth.user.email }}
                                    </p>
                                    <p
                                        v-if="$page.props.auth.user.role"
                                        class="mt-1 text-xs font-medium text-indigo-600 dark:text-indigo-400"
                                    >
                                        {{ $page.props.auth.user.role.name }}
                                    </p>
                                </div>
                                <DropdownLink :href="route('profile.edit')">
                                    <span class="flex items-center gap-2">
                                        <UserIcon class="h-4 w-4 text-slate-400" />
                                        Profil
                                    </span>
                                </DropdownLink>
                                <DropdownLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                >
                                    <span class="flex items-center gap-2">
                                        <ArrowLeftOnRectangleIcon
                                            class="h-4 w-4 text-slate-400"
                                        />
                                        Çıkış
                                    </span>
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </header>

                <main class="px-4 py-10 lg:px-10 lg:py-12">
                    <Transition name="pa-page" mode="out-in">
                        <div :key="page.url" class="motion-reduce:transition-none">
                            <slot />
                        </div>
                    </Transition>
                </main>
            </div>
        </div>
    </div>
</template>
