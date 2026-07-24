<script setup lang="ts">
import BrandHeader from '@/components/BrandHeader.vue';
import { Head } from '@inertiajs/vue3';
import { BadgeCheck } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

interface CertificateInfo {
    recipient_name: string;
    service_center_code: 'training' | 'exam';
    issued_at: string;
    item_name: string | null;
    total_score: number | null;
    cefr_level: string | null;
}

defineProps<{
    certificate: CertificateInfo;
}>();

const { t } = useI18n();
</script>

<template>
    <Head :title="t('certificateVerify.title')" />

    <div class="flex min-h-screen flex-col items-center bg-slate-50 text-slate-800 dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
        <BrandHeader :home-href="route('home')" class="w-full" />

        <div class="w-full max-w-lg flex-1 p-6 lg:flex lg:items-center lg:justify-center lg:p-8">
            <div class="w-full rounded-xl border border-emerald-200 bg-emerald-50 p-6 shadow-sm dark:border-emerald-900 dark:bg-emerald-950/40">
                <div class="mb-4 flex items-center gap-2 text-emerald-700 dark:text-emerald-400">
                    <BadgeCheck class="h-6 w-6" />
                    <p class="text-lg font-bold">{{ t('certificateVerify.valid') }}</p>
                </div>

                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-muted-foreground">{{ t('certificateVerify.recipient') }}</dt>
                        <dd class="font-medium">{{ certificate.recipient_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">{{ t('certificateVerify.item') }}</dt>
                        <dd class="font-medium">{{ certificate.item_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">{{ t('certificateVerify.issuedOn') }}</dt>
                        <dd class="font-medium">{{ certificate.issued_at }}</dd>
                    </div>
                    <div v-if="certificate.service_center_code === 'exam'">
                        <dt class="text-muted-foreground">{{ t('certificateVerify.score') }}</dt>
                        <dd class="font-medium">{{ certificate.total_score }} ({{ certificate.cefr_level }})</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</template>
