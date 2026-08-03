<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Badge, type BadgeVariants } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { CheckCircle2, Clock, ListChecks, Loader2, Receipt, type LucideIcon } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AdminLayout });

interface TranslationRequestRow {
    id: number;
    user_name: string;
    user_email: string;
    file_name: string;
    source_lang: string;
    target_lang: string;
    status: 'submitted' | 'quote_sent' | 'translating' | 'completed';
    estimated_price: string | null;
    delivery_date: string | null;
    has_translated_file: boolean;
    payment_status: 'pending' | 'approved' | 'rejected' | null;
}

const props = defineProps<{
    requests: TranslationRequestRow[];
}>();

const { t } = useI18n();

const quoteDrafts = reactive<Record<number, { estimated_price: number; delivery_days: number }>>({});
const deliverFiles = reactive<Record<number, File | null>>({});

const draftFor = (id: number) => {
    if (!quoteDrafts[id]) {
        quoteDrafts[id] = { estimated_price: 500, delivery_days: 7 };
    }
    return quoteDrafts[id];
};

const sendQuote = (request: TranslationRequestRow) => {
    router.post(route('admin.translation-requests.quote', request.id), draftFor(request.id), { preserveScroll: true });
};

const onDeliverFileChange = (request: TranslationRequestRow, event: Event) => {
    const target = event.target as HTMLInputElement;
    deliverFiles[request.id] = target.files?.[0] ?? null;
};

const deliver = (request: TranslationRequestRow) => {
    const file = deliverFiles[request.id];
    if (!file) return;

    router.post(
        route('admin.translation-requests.deliver', request.id),
        { file },
        {
            preserveScroll: true,
            onSuccess: () => {
                deliverFiles[request.id] = null;
            },
        },
    );
};

const statusLabel = computed<Record<TranslationRequestRow['status'], string>>(() => ({
    submitted: t('admin.translationQuotes.statusSubmitted'),
    quote_sent: t('admin.translationQuotes.statusQuoteSent'),
    translating: t('admin.translationQuotes.statusTranslating'),
    completed: t('admin.translationQuotes.statusCompleted'),
}));

const paymentStatusLabel = computed<Record<'pending' | 'approved' | 'rejected', string>>(() => ({
    pending: t('admin.payments.statusPending'),
    approved: t('admin.payments.statusApproved'),
    rejected: t('admin.payments.statusRejected'),
}));

const statusVariant: Record<TranslationRequestRow['status'], NonNullable<BadgeVariants['variant']>> = {
    submitted: 'neutral',
    quote_sent: 'warning',
    translating: 'info',
    completed: 'success',
};

type StatusTab = 'all' | TranslationRequestRow['status'];

const activeTab = ref<StatusTab>('all');

const tabIcon: Record<StatusTab, LucideIcon> = {
    all: ListChecks,
    submitted: Clock,
    quote_sent: Receipt,
    translating: Loader2,
    completed: CheckCircle2,
};

const tabColorClass: Record<StatusTab, string> = {
    all: 'text-violet-600 data-[state=active]:bg-violet-100 data-[state=active]:text-violet-800',
    submitted: 'text-slate-600 data-[state=active]:bg-slate-100 data-[state=active]:text-slate-800',
    quote_sent: 'text-amber-600 data-[state=active]:bg-amber-100 data-[state=active]:text-amber-800',
    translating: 'text-indigo-600 data-[state=active]:bg-indigo-100 data-[state=active]:text-indigo-800',
    completed: 'text-emerald-600 data-[state=active]:bg-emerald-100 data-[state=active]:text-emerald-800',
};

// Same status colors, carried onto each request card's border so the list
// reads consistently with whichever tab color it belongs to.
const cardBorderClass: Record<TranslationRequestRow['status'], string> = {
    submitted: 'border-slate-300',
    quote_sent: 'border-amber-300',
    translating: 'border-indigo-300',
    completed: 'border-emerald-300',
};

const tabs = computed<{ value: StatusTab; label: string; count: number }[]>(() => [
    { value: 'all', label: t('admin.translationQuotes.tabAll'), count: props.requests.length },
    { value: 'submitted', label: statusLabel.value.submitted, count: props.requests.filter((r) => r.status === 'submitted').length },
    { value: 'quote_sent', label: statusLabel.value.quote_sent, count: props.requests.filter((r) => r.status === 'quote_sent').length },
    { value: 'translating', label: statusLabel.value.translating, count: props.requests.filter((r) => r.status === 'translating').length },
    { value: 'completed', label: statusLabel.value.completed, count: props.requests.filter((r) => r.status === 'completed').length },
]);

const filteredRequests = computed(() =>
    activeTab.value === 'all' ? props.requests : props.requests.filter((r) => r.status === activeTab.value),
);
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <Head :title="t('nav.admin.translationQuotes')" />
            <HeadingSmall :title="t('admin.translationQuotes.title')" :description="t('admin.translationQuotes.description')" />

            <p v-if="requests.length === 0" class="rounded-xl border border-dashed p-8 text-center text-sm text-muted-foreground">
                {{ t('admin.translationQuotes.empty') }}
            </p>

            <template v-else>
                <Tabs v-model="activeTab" class="w-full">
                    <TabsList>
                        <TabsTrigger
                            v-for="tab in tabs"
                            :key="tab.value"
                            :value="tab.value"
                            :class="['gap-1.5', tabColorClass[tab.value]]"
                        >
                            <component :is="tabIcon[tab.value]" class="h-4 w-4" />
                            {{ tab.label }}
                            <Badge variant="neutral" class="ml-1">{{ tab.count }}</Badge>
                        </TabsTrigger>
                    </TabsList>
                </Tabs>

                <Transition name="fade" mode="out-in">
                    <div :key="activeTab" class="space-y-3">
                        <p v-if="filteredRequests.length === 0" class="rounded-xl border border-dashed p-8 text-center text-sm text-muted-foreground">
                            {{ t('admin.translationQuotes.emptyFiltered') }}
                        </p>

                        <template v-else>
                            <div
                                v-for="request in filteredRequests"
                                :key="request.id"
                                :class="['rounded-xl border-2 p-4 shadow-sm', cardBorderClass[request.status]]"
                            >
                                <div class="flex flex-col justify-between gap-2 md:flex-row md:items-start">
                                    <div>
                                        <div class="mb-1 flex items-center gap-2 text-xs">
                                            <span class="rounded bg-muted px-1.5 py-0.5 font-mono">#{{ request.id }}</span>
                                            <span class="text-muted-foreground">{{ request.user_name }} ({{ request.user_email }})</span>
                                        </div>
                                        <p class="font-medium">{{ request.file_name }}</p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ t('admin.translationQuotes.langPair', { source: request.source_lang, target: request.target_lang }) }}
                                        </p>
                                        <a
                                            :href="route('admin.translation-requests.source', request.id)"
                                            target="_blank"
                                            class="text-xs text-primary underline"
                                            >{{ t('admin.translationQuotes.viewSource') }}</a
                                        >
                                    </div>

                                    <div class="text-right">
                                        <Badge
                                            :variant="statusVariant[request.status]"
                                            :class="request.status === 'translating' ? 'animate-pulse' : ''"
                                            >{{ statusLabel[request.status] }}</Badge
                                        >
                                        <p v-if="request.payment_status" class="mt-1 text-xs text-muted-foreground">
                                            {{ t('admin.translationQuotes.paymentStatus', { status: paymentStatusLabel[request.payment_status] }) }}
                                        </p>
                                    </div>
                                </div>

                                <div v-if="request.status === 'submitted'" class="mt-3 flex flex-wrap items-end gap-2 border-t pt-3">
                                    <div class="grid gap-1">
                                        <label class="text-xs font-medium">{{ t('admin.translationQuotes.estimatedPrice') }}</label>
                                        <input
                                            v-model.number="draftFor(request.id).estimated_price"
                                            type="number"
                                            min="0"
                                            class="h-9 w-28 rounded border px-2 text-sm"
                                        />
                                    </div>
                                    <div class="grid gap-1">
                                        <label class="text-xs font-medium">{{ t('admin.translationQuotes.deliveryDays') }}</label>
                                        <input
                                            v-model.number="draftFor(request.id).delivery_days"
                                            type="number"
                                            min="1"
                                            class="h-9 w-24 rounded border px-2 text-sm"
                                        />
                                    </div>
                                    <Button size="sm" @click="sendQuote(request)">{{ t('admin.translationQuotes.sendQuote') }}</Button>
                                </div>

                                <div v-else-if="request.status === 'translating'" class="mt-3 flex flex-wrap items-end gap-2 border-t pt-3">
                                    <input type="file" class="text-sm" @change="onDeliverFileChange(request, $event)" />
                                    <Button size="sm" :disabled="!deliverFiles[request.id]" @click="deliver(request)">{{
                                        t('admin.translationQuotes.deliverResult')
                                    }}</Button>
                                </div>
                            </div>
                        </template>
                    </div>
                </Transition>
            </template>
    </div>
</template>
