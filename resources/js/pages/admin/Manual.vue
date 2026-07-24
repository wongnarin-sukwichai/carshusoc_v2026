<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <Head :title="t('nav.admin.manual')" />
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <HeadingSmall :title="t('admin.manual.title')" :description="t('admin.manual.description')" />
        </div>
    </div>
</template>
