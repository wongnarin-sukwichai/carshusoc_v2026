<script setup lang="ts">
import BrandHeader from '@/components/BrandHeader.vue';
import FlashMessage from '@/components/FlashMessage.vue';
import ImpersonationBanner from '@/components/ImpersonationBanner.vue';
import LocaleSwitcher from '@/components/LocaleSwitcher.vue';
import { SidebarProvider } from '@/components/ui/sidebar';
import { onMounted, ref } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar' | 'static-sidebar';
    homeHref?: string;
}

defineProps<Props>();

const isOpen = ref(true);

onMounted(() => {
    isOpen.value = localStorage.getItem('sidebar') !== 'false';
});

const handleSidebarChange = (open: boolean) => {
    isOpen.value = open;
    localStorage.setItem('sidebar', String(open));
};
</script>

<template>
    <ImpersonationBanner />
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <slot />
    </div>
    <div v-else-if="variant === 'static-sidebar'" class="flex min-h-screen w-full flex-col bg-slate-50 dark:bg-background">
        <BrandHeader :home-href="homeHref">
            <slot name="header-end" />
            <LocaleSwitcher />
        </BrandHeader>
        <div class="grid w-full flex-1 grid-cols-1 gap-6 px-6 py-8 lg:grid-cols-12 lg:px-[12.5%]">
            <slot />
        </div>
    </div>
    <div v-else class="flex min-h-screen w-full flex-col">
        <BrandHeader :home-href="homeHref">
            <LocaleSwitcher />
        </BrandHeader>
        <SidebarProvider :default-open="isOpen" :open="isOpen" @update:open="handleSidebarChange">
            <slot />
        </SidebarProvider>
    </div>
    <FlashMessage />
</template>
