<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { BookOpen } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <Head :title="t('nav.admin.manual')" />
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="border-b border-slate-100 pb-2">
                <h2 class="flex items-center gap-1.5 text-sm font-bold text-slate-800">
                    <BookOpen class="h-4 w-4 text-indigo-600" />
                    {{ t('admin.manual.title') }}
                </h2>
                <p class="text-[10px] text-slate-500">{{ t('admin.manual.description') }}</p>
            </div>
        </div>
    </div>
</template>
