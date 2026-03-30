<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const appSettings = computed(() => page.props.appSettings ?? {});
</script>

<template>
    <Head>
        <meta
            v-if="appSettings.site_description"
            name="description"
            :content="appSettings.site_description"
        />
        <link v-if="appSettings.favicon_url" rel="icon" :href="appSettings.favicon_url" />
    </Head>

    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0 dark:bg-gray-900"
    >
        <div>
            <Link href="/">
                <img
                    v-if="appSettings.logo_url"
                    :src="appSettings.logo_url"
                    alt=""
                    class="mx-auto h-20 w-auto max-w-[220px] object-contain"
                />
                <ApplicationLogo v-else class="mx-auto h-20 w-20 fill-current text-gray-500" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-gray-800"
        >
            <slot />
        </div>
    </div>
</template>
