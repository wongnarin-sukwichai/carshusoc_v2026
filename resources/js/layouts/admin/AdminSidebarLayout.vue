<script setup lang="ts">
import AdminSidebar from '@/components/AdminSidebar.vue';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
</script>

<template>
    <AppShell variant="static-sidebar" :home-href="route('admin.dashboard')">
        <AdminSidebar />
        <AppContent variant="static-sidebar">
            <slot />
        </AppContent>
    </AppShell>
</template>
