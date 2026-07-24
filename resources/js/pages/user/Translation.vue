<script setup lang="ts">
import PaymentSlipDialog from '@/components/PaymentSlipDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import UserLayout from '@/layouts/user/UserLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Download, FileText, LoaderCircle, Upload } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: UserLayout });

interface TranslationRequestRow {
    id: number;
    file_name: string;
    source_lang: string;
    target_lang: string;
    status: 'submitted' | 'quote_sent' | 'translating' | 'completed';
    estimated_price: string | null;
    delivery_date: string | null;
}

defineProps<{
    requests: TranslationRequestRow[];
}>();

const { t } = useI18n();

const form = useForm({
    source_lang: 'ไทย',
    target_lang: 'อังกฤษ',
    document: null as File | null,
});

const onFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    form.document = target.files?.[0] ?? null;
};

const submit = () => {
    form.post(route('user.translations.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const statusLabel = computed<Record<TranslationRequestRow['status'], string>>(() => ({
    submitted: t('user.translation.statusSubmitted'),
    quote_sent: t('user.translation.statusQuoteSent'),
    translating: t('user.translation.statusTranslating'),
    completed: t('user.translation.statusCompleted'),
}));

const statusVariant: Record<TranslationRequestRow['status'], 'warning' | 'info' | 'success'> = {
    submitted: 'warning',
    quote_sent: 'warning',
    translating: 'info',
    completed: 'success',
};
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <Head :title="t('nav.user.translation')" />
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950 to-slate-900 p-6 text-white">
                <FileText class="absolute right-0 bottom-0 h-48 w-48 translate-x-12 translate-y-6 opacity-10" />
                <div class="relative z-10 max-w-2xl">
                    <span class="rounded bg-indigo-500 px-2 py-0.5 text-[9px] font-black tracking-widest text-white uppercase">
                        {{ t('nav.user.translation') }}
                    </span>
                    <h2 class="mt-2 text-xl font-extrabold">{{ t('nav.user.translation') }}</h2>
                    <p class="mt-1 text-xs leading-relaxed text-slate-300">{{ t('user.translation.description') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <form class="space-y-3 rounded-xl border border-slate-200 p-4 shadow-sm lg:col-span-1" @submit.prevent="submit">
                    <h3 class="flex items-center gap-1 text-sm font-bold"><Upload class="h-4 w-4" /> {{ t('user.translation.newRequest') }}</h3>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="grid gap-1">
                            <Label for="source-lang">{{ t('user.translation.sourceLang') }}</Label>
                            <select id="source-lang" v-model="form.source_lang" class="h-10 rounded-md border bg-background px-3 text-sm">
                                <option value="ไทย">ไทย</option>
                                <option value="อังกฤษ">อังกฤษ</option>
                                <option value="ลาว">ลาว</option>
                            </select>
                        </div>
                        <div class="grid gap-1">
                            <Label for="target-lang">{{ t('user.translation.targetLang') }}</Label>
                            <select id="target-lang" v-model="form.target_lang" class="h-10 rounded-md border bg-background px-3 text-sm">
                                <option value="อังกฤษ">อังกฤษ</option>
                                <option value="ไทย">ไทย</option>
                                <option value="ลาว">ลาว</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-1">
                        <Label for="document">{{ t('user.translation.document') }}</Label>
                        <input
                            id="document"
                            type="file"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium"
                            required
                            @change="onFileChange"
                        />
                        <p v-if="form.errors.document" class="text-xs text-destructive">{{ form.errors.document }}</p>
                    </div>

                    <Button type="submit" class="w-full" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        {{ t('user.translation.submit') }}
                    </Button>
                </form>

                <div class="rounded-xl border border-slate-200 p-4 shadow-sm lg:col-span-2">
                    <h3 class="mb-3 text-sm font-bold">{{ t('user.translation.historyTitle') }}</h3>

                    <p v-if="requests.length === 0" class="py-8 text-center text-sm text-muted-foreground">{{ t('user.translation.empty') }}</p>

                    <div v-else class="space-y-2">
                        <div
                            v-for="req in requests"
                            :key="req.id"
                            class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm"
                        >
                            <div>
                                <p class="font-medium">{{ req.file_name }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ t('user.translation.langPair', { source: req.source_lang, target: req.target_lang }) }}
                                </p>
                                <Badge :variant="statusVariant[req.status]" :class="req.status === 'translating' ? 'animate-pulse' : ''">{{
                                    statusLabel[req.status]
                                }}</Badge>
                            </div>
                            <div class="text-right">
                                <p v-if="req.estimated_price" class="mb-1 text-xs text-muted-foreground">
                                    {{ t('user.translation.estimatedPrice', { price: Number(req.estimated_price).toLocaleString() }) }}
                                </p>
                                <PaymentSlipDialog
                                    v-if="req.status === 'quote_sent'"
                                    payable-type="translation_request"
                                    :payable-id="req.id"
                                    :title="req.file_name"
                                    :amount="Number(req.estimated_price)"
                                    :trigger-label="t('user.translation.uploadSlip')"
                                />
                                <Button v-else-if="req.status === 'completed'" as-child size="sm" variant="secondary">
                                    <a :href="route('user.translations.download', req.id)"
                                        ><Download class="mr-1 h-3 w-3" />{{ t('user.translation.downloadResult') }}</a
                                    >
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</template>
