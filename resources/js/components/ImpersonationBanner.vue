<script setup lang="ts">
import { type SharedData } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { UserRoundCog } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const page = usePage<SharedData>();
const { t } = useI18n();

const leave = () => {
    router.post(route('admin.impersonate.stop'));
};
</script>

<template>
    <div
        v-if="page.props.impersonating"
        class="flex items-center justify-center gap-3 bg-amber-500 px-4 py-2 text-xs font-bold text-amber-950"
    >
        <UserRoundCog class="h-3.5 w-3.5 shrink-0" />
        <span>{{ t('components.impersonation.banner', { name: page.props.auth.user?.name }) }}</span>
        <button type="button" class="rounded-full bg-amber-950/10 px-3 py-1 hover:bg-amber-950/20" @click="leave">
            {{ t('components.impersonation.leave') }}
        </button>
    </div>
</template>
