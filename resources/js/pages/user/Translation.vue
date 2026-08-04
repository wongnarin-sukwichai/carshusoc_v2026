<script setup lang="ts">
import FileDropzone from '@/components/FileDropzone.vue';
import PaymentSlipDialog from '@/components/PaymentSlipDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import UserLayout from '@/layouts/user/UserLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowRight, Download, FileText, Languages, LoaderCircle, Upload } from 'lucide-vue-next';
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

// Border color carried on each history card so the list reads at a glance,
// distinct from the badge coloring above (submitted/quote_sent share a badge
// variant, but "awaiting a quote" vs. "needs payment" are different enough
// states to deserve different border colors here).
const cardBorderClass: Record<TranslationRequestRow['status'], string> = {
    submitted: 'border-slate-300',
    quote_sent: 'border-amber-300',
    translating: 'border-indigo-300',
    completed: 'border-emerald-300',
};
</script>

<template>
    <div class="flex flex-col flex-1 h-full gap-4 p-4 rounded-xl">
        <Head :title="t('nav.user.translation')" />
            <div class="relative p-6 overflow-hidden text-white rounded-2xl bg-gradient-to-br from-indigo-950 to-slate-900">
                <FileText class="absolute bottom-0 right-0 w-48 h-48 translate-x-12 translate-y-6 opacity-10" />
                <div class="relative z-10 max-w-2xl">
                    <span class="rounded bg-indigo-500 px-2 py-0.5 text-[9px] font-black tracking-widest text-white uppercase">
                        {{ t('nav.user.translation') }}
                    </span>
                    <h2 class="mt-2 text-xl font-extrabold">{{ t('nav.user.translation') }}</h2>
                    <p class="mt-1 text-xs leading-relaxed text-slate-300">{{ t('user.translation.description') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <form class="p-4 space-y-4 border shadow-sm rounded-xl border-slate-200 lg:col-span-1" @submit.prevent="submit">
                    <h3 class="flex items-center gap-2 text-sm font-bold">
                        <span class="flex items-center justify-center w-8 h-8 text-indigo-600 bg-indigo-100 rounded-full">
                            <Upload class="w-4 h-4" />
                        </span>
                        {{ t('user.translation.newRequest') }}
                    </h3>

                    <div class="p-3 border border-indigo-200 rounded-lg bg-indigo-50/60">
                        <p class="mb-2 flex items-center gap-1.5 text-xs font-bold text-indigo-900">
                            <Languages class="h-3.5 w-3.5 text-indigo-600" />
                            {{ t('user.translation.langPairTitle') }}
                        </p>
                        <div class="flex items-end gap-2">
                            <div class="grid flex-1 gap-1">
                                <Label for="source-lang" class="text-xs">{{ t('user.translation.sourceLang') }}</Label>
                                <select id="source-lang" v-model="form.source_lang" class="h-10 px-3 text-sm bg-white border rounded-md">
                                    <option value="ไทย">ไทย</option>
                                    <option value="อังกฤษ">อังกฤษ</option>
                                    <option value="ลาว">ลาว</option>
                                </select>
                            </div>
                            <ArrowRight class="mb-2.5 h-4 w-4 shrink-0 text-indigo-400" />
                            <div class="grid flex-1 gap-1">
                                <Label for="target-lang" class="text-xs">{{ t('user.translation.targetLang') }}</Label>
                                <select id="target-lang" v-model="form.target_lang" class="h-10 px-3 text-sm bg-white border rounded-md">
                                    <option value="อังกฤษ">อังกฤษ</option>
                                    <option value="ไทย">ไทย</option>
                                    <option value="ลาว">ลาว</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-1">
                        <Label for="document" class="flex items-center gap-1.5 text-sm">
                            <FileText class="w-4 h-4 text-indigo-600" />
                            {{ t('user.translation.document') }}
                        </Label>
                        <FileDropzone
                            id="document"
                            v-model="form.document"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            required
                            :hint="t('user.translation.chooseFileHint')"
                            :formats-hint="t('user.translation.chooseFileFormats')"
                        />
                        <p v-if="form.errors.document" class="text-xs text-destructive">{{ form.errors.document }}</p>
                    </div>

                    <Button type="submit" class="w-full" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                        {{ t('user.translation.submit') }}
                    </Button>
                </form>

                <div class="p-4 border shadow-sm rounded-xl border-slate-200 lg:col-span-2">
                    <h3 class="mb-3 text-sm font-bold">{{ t('user.translation.historyTitle') }}</h3>

                    <p v-if="requests.length === 0" class="py-8 text-sm text-center text-muted-foreground">{{ t('user.translation.empty') }}</p>

                    <div v-else class="space-y-2">
                        <div
                            v-for="req in requests"
                            :key="req.id"
                            :class="['flex items-center justify-between rounded-xl border bg-slate-50 p-3 text-sm', cardBorderClass[req.status]]"
                        >
                            <div>
                                <p class="font-medium">{{ req.file_name }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ t('user.translation.langPair', { source: req.source_lang, target: req.target_lang }) }}
                                </p>
                                <Badge :variant="statusVariant[req.status]" :class="req.status === 'translating' ? 'animate-pulse' : ''" class="mt-2">{{
                                    statusLabel[req.status]
                                }}</Badge>
                            </div>
                            <div class="mt-1 text-right">
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
                                <Button
                                    v-else-if="req.status === 'completed'"
                                    as-child
                                    size="sm"
                                    class="border-transparent bg-[#217346] text-white hover:bg-[#1a5c38] hover:text-white"
                                >
                                    <a :href="route('user.translations.download', req.id)"
                                        ><Download class="w-3 h-3 mr-1" />{{ t('user.translation.downloadResult') }}</a
                                    >
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</template>
