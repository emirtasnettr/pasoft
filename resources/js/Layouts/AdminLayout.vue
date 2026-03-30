<script setup>
import FlashToast from '@/Components/FlashToast.vue';
import PaToastHost from '@/Components/PaToastHost.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    ArrowLeftOnRectangleIcon,
    ChatBubbleLeftRightIcon,
    Cog6ToothIcon,
    UserGroupIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const appSettings = computed(() => page.props.appSettings ?? {});

const settingsActive = computed(() => route().current('admin.settings.edit'));
const usersActive = computed(() => {
    const c = route().current() ?? '';
    return c.startsWith('admin.users');
});
const demoRequestsActive = computed(() => route().current('admin.demo-requests.index'));

function navClass(active) {
    return active
        ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/25'
        : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800';
}
</script>

<template>
    <div>
        <FlashToast />
        <PaToastHost />

        <Head>
            <link v-if="appSettings.favicon_url" rel="icon" :href="appSettings.favicon_url" />
        </Head>

        <div
            class="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100/80 text-slate-900 dark:from-slate-950 dark:to-slate-950 dark:text-slate-100"
        >
            <header
                class="sticky top-0 z-30 border-b border-slate-200/70 bg-white/90 px-4 py-4 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 lg:px-10"
            >
                <div class="mx-auto flex max-w-6xl items-center justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <img
                            v-if="appSettings.logo_url"
                            :src="appSettings.logo_url"
                            alt=""
                            class="h-10 w-auto max-w-[160px] object-contain"
                        />
                        <div
                            v-else
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white shadow-lg"
                        >
                            PA
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link
                            :href="route('admin.settings.edit')"
                            class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition"
                            :class="navClass(settingsActive)"
                        >
                            <Cog6ToothIcon class="h-5 w-5" />
                            <span class="hidden sm:inline">Site ayarları</span>
                        </Link>
                        <Link
                            :href="route('admin.demo-requests.index')"
                            class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition"
                            :class="navClass(demoRequestsActive)"
                        >
                            <ChatBubbleLeftRightIcon class="h-5 w-5" />
                            <span class="hidden sm:inline">Demo talepleri</span>
                        </Link>
                        <Link
                            :href="route('admin.users.index')"
                            class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition"
                            :class="navClass(usersActive)"
                        >
                            <UserGroupIcon class="h-5 w-5" />
                            <span class="hidden sm:inline">Kullanıcılar</span>
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            <ArrowLeftOnRectangleIcon class="h-5 w-5" />
                            <span class="hidden sm:inline">Çıkış</span>
                        </Link>
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-6xl px-4 py-10 lg:px-10 lg:py-12">
                <slot />
            </main>
        </div>
    </div>
</template>
