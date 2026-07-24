<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import UserHeaderMenu from '@/components/UserHeaderMenu.vue';
import UserSidebar from '@/components/UserSidebar.vue';
import { type SharedData, type User } from '@/types';
import { usePage } from '@inertiajs/vue3';

const page = usePage<SharedData>();
const user = page.props.auth.user as User;
</script>

<template>
    <AppShell variant="static-sidebar" :home-href="route('dashboard')">
        <template #header-end>
            <UserHeaderMenu :user="user" />
        </template>

        <UserSidebar />
        <AppContent variant="static-sidebar">
            <slot />
        </AppContent>
    </AppShell>
</template>
