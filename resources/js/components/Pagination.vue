<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

defineProps<{
    currentPage: number;
    lastPage: number;
    prevPageUrl: string | null;
    nextPageUrl: string | null;
}>();

const { t } = useI18n();
</script>

<template>
    <div v-if="lastPage > 1" class="flex items-center justify-between pt-3 mt-3 border-t">
        <Link
            :href="prevPageUrl ?? '#'"
            preserve-scroll
            class="flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100"
            :class="!prevPageUrl ? 'pointer-events-none opacity-40' : ''"
        >
            <ChevronLeft class="w-3.5 h-3.5" />
            {{ t('common.previous') }}
        </Link>
        <span class="text-xs text-muted-foreground">
            {{ t('common.pageOf', { current: currentPage, last: lastPage }) }}
        </span>
        <Link
            :href="nextPageUrl ?? '#'"
            preserve-scroll
            class="flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100"
            :class="!nextPageUrl ? 'pointer-events-none opacity-40' : ''"
        >
            {{ t('common.next') }}
            <ChevronRight class="w-3.5 h-3.5" />
        </Link>
    </div>
</template>
