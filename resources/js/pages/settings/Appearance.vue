<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';

import SettingsLayout from '@/layouts/settings/Layout.vue';
import UserLayout from '@/layouts/user/UserLayout.vue';

defineOptions({ layout: UserLayout });
</script>

<template>
    <SettingsLayout>
        <Head title="Appearance settings" />

        <div class="space-y-6">
            <HeadingSmall title="Appearance settings" description="Update your account's appearance settings" />
            <AppearanceTabs />
        </div>
    </SettingsLayout>
</template>
