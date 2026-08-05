<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import { Badge, type BadgeVariants } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { formatDate } from '@/lib/date';
import { type Paginated } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { CheckCircle2, Clock, DollarSign, ListChecks, XCircle, type LucideIcon } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AdminLayout });

type PaymentStatus = 'pending' | 'approved' | 'rejected';
type StatusTab = 'all' | PaymentStatus;

interface PaymentAttempt {
    id: number;
    amount: number;
    has_slip: boolean;
    status: PaymentStatus;
    wants_receipt: boolean;
    wants_mail_delivery: boolean;
    mail_delivery_fee_charged: number | null;
    rejected_reason: string | null;
    created_at: string;
}

interface PaymentCase {
    case_key: string;
    user_name: string;
    user_email: string;
    label: string;
    latest_status: PaymentStatus;
    payments: PaymentAttempt[];
}

const props = defineProps<{
    paymentCases: Paginated<PaymentCase>;
    statusCounts: Record<StatusTab, number>;
    activeStatus: StatusTab;
}>();

const { t } = useI18n();

const goToTab = (tab: StatusTab | number | undefined) => {
    router.get(route('admin.payments'), { status: tab }, { preserveScroll: true });
};

const approve = (payment: PaymentAttempt) => {
    router.post(route('admin.payments.approve', payment.id), {}, { preserveScroll: true });
};

const rejectingId = ref<number | null>(null);
const rejectReasons = reactive<Record<number, string>>({});

const startReject = (payment: PaymentAttempt) => {
    rejectingId.value = payment.id;
    if (!(payment.id in rejectReasons)) rejectReasons[payment.id] = '';
};

const cancelReject = () => {
    rejectingId.value = null;
};

const confirmReject = (payment: PaymentAttempt) => {
    router.post(
        route('admin.payments.reject', payment.id),
        { reason: rejectReasons[payment.id] || undefined },
        {
            preserveScroll: true,
            onSuccess: () => {
                rejectingId.value = null;
            },
        },
    );
};

const statusLabel = computed<Record<PaymentStatus, string>>(() => ({
    pending: t('admin.payments.statusPending'),
    approved: t('admin.payments.statusApproved'),
    rejected: t('admin.payments.statusRejected'),
}));

const statusVariant: Record<PaymentStatus, NonNullable<BadgeVariants['variant']>> = {
    pending: 'warning',
    approved: 'success',
    rejected: 'destructive',
};

const tabIcon: Record<StatusTab, LucideIcon> = {
    all: ListChecks,
    pending: Clock,
    approved: CheckCircle2,
    rejected: XCircle,
};

const tabColorClass: Record<StatusTab, string> = {
    all: 'text-slate-600 data-[state=active]:bg-slate-200 data-[state=active]:text-slate-800',
    pending: 'text-amber-600 data-[state=active]:bg-amber-100 data-[state=active]:text-amber-800',
    approved: 'text-emerald-600 data-[state=active]:bg-emerald-100 data-[state=active]:text-emerald-800',
    rejected: 'text-red-600 data-[state=active]:bg-red-100 data-[state=active]:text-red-800',
};

// Same status colors, carried onto each case card's border so the list
// reads consistently with whichever tab color it belongs to.
const cardBorderClass: Record<PaymentStatus, string> = {
    pending: 'border-amber-300',
    approved: 'border-emerald-300',
    rejected: 'border-red-300',
};

const tabs = computed<{ value: StatusTab; label: string; count: number }[]>(() => [
    { value: 'all', label: t('admin.payments.tabAll'), count: props.statusCounts.all },
    { value: 'pending', label: statusLabel.value.pending, count: props.statusCounts.pending },
    { value: 'approved', label: statusLabel.value.approved, count: props.statusCounts.approved },
    { value: 'rejected', label: statusLabel.value.rejected, count: props.statusCounts.rejected },
]);
</script>

<template>
    <div class="flex flex-col flex-1 h-full gap-4 p-4 rounded-xl">
        <Head :title="t('nav.admin.payments')" />

        <div class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200">
            <div class="pb-2 border-b border-slate-100">
                <h2 class="flex items-center gap-1.5 text-sm font-bold text-slate-800">
                    <DollarSign class="w-4 h-4 text-indigo-600" />
                    {{ t('admin.payments.title') }}
                </h2>
                <p class="text-[10px] text-slate-500">{{ t('admin.payments.description') }}</p>
            </div>

            <p
                v-if="statusCounts.all === 0"
                class="py-8 mt-4 text-xs italic text-center border border-dashed rounded-xl bg-slate-50 text-slate-400"
            >
                {{ t('admin.payments.empty') }}
            </p>

            <template v-else>
                <Tabs :model-value="activeStatus" class="w-full mt-4" @update:model-value="goToTab">
                    <TabsList>
                        <TabsTrigger
                            v-for="tab in tabs"
                            :key="tab.value"
                            :value="tab.value"
                            :class="['gap-1.5', tabColorClass[tab.value]]"
                        >
                            <component :is="tabIcon[tab.value]" class="w-4 h-4" />
                            {{ tab.label }}
                            <Badge variant="neutral" class="ml-1">{{ tab.count }}</Badge>
                        </TabsTrigger>
                    </TabsList>
                </Tabs>

                <Transition name="fade" mode="out-in">
                    <div :key="activeStatus" class="mt-3 space-y-3">
                        <p
                            v-if="paymentCases.data.length === 0"
                            class="py-8 text-xs italic text-center border border-dashed rounded-xl bg-slate-50 text-slate-400"
                        >
                            {{ t('admin.payments.emptyFiltered') }}
                        </p>

                        <div
                            v-for="paymentCase in paymentCases.data"
                            :key="paymentCase.case_key"
                            :class="['rounded-xl border bg-slate-50 p-4 text-xs', cardBorderClass[paymentCase.latest_status]]"
                        >
                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                <span class="rounded bg-indigo-100 px-1.5 py-0.5 text-[9px] font-bold text-indigo-800">{{ paymentCase.label }}</span>
                                <Badge :variant="statusVariant[paymentCase.latest_status]">{{ statusLabel[paymentCase.latest_status] }}</Badge>
                            </div>
                            <p class="font-semibold text-slate-800">
                                {{ t('admin.payments.payerPrefix') }}{{ paymentCase.user_name }}
                                <span class="font-normal text-slate-500">({{ paymentCase.user_email }})</span>
                            </p>

                            <p v-if="paymentCase.payments.length > 1" class="mt-2 text-[10px] font-medium text-slate-500">
                                {{ t('admin.payments.attemptHistory', { count: paymentCase.payments.length }) }}
                            </p>

                            <div class="mt-2 space-y-2">
                                <div
                                    v-for="payment in paymentCase.payments"
                                    :key="payment.id"
                                    class="rounded-lg border border-slate-200 bg-white p-3"
                                >
                                    <div class="flex flex-col items-start justify-between gap-3 md:flex-row md:items-center">
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                                <span class="font-mono text-[9px] text-slate-400">{{ t('admin.payments.idPrefix') }}{{ payment.id }}</span>
                                                <span class="text-[10px] text-slate-400">{{ formatDate(payment.created_at, 'DD/MM/YYYY HH:mm') }}</span>
                                                <Badge :variant="statusVariant[payment.status]">{{ statusLabel[payment.status] }}</Badge>
                                            </div>
                                            <p class="text-[10px] text-slate-500">
                                                <a
                                                    v-if="payment.has_slip"
                                                    :href="route('admin.payments.slip', payment.id)"
                                                    target="_blank"
                                                    class="text-indigo-600 underline"
                                                >
                                                    {{ t('admin.payments.viewSlip') }}
                                                </a>
                                                <span v-else>{{ t('admin.payments.noSlip') }}</span>
                                                <span v-if="payment.wants_receipt"> &middot; {{ t('admin.payments.receiptWanted') }}</span>
                                                <span v-if="payment.wants_mail_delivery">
                                                    &middot;
                                                    {{ t('admin.payments.mailDeliveryFee', { fee: (payment.mail_delivery_fee_charged ?? 0).toLocaleString() }) }}
                                                </span>
                                            </p>
                                            <p class="mt-1 font-extrabold text-indigo-600">
                                                {{ t('admin.payments.amountPrefix') }}{{ payment.amount.toLocaleString() }} ฿
                                            </p>
                                            <p v-if="payment.status === 'rejected' && payment.rejected_reason" class="mt-1 text-red-600">
                                                {{ t('admin.payments.rejectedReasonPrefix') }}{{ payment.rejected_reason }}
                                            </p>
                                        </div>

                                        <div v-if="payment.status === 'pending' && rejectingId !== payment.id" class="flex w-full gap-2 md:w-auto">
                                            <Button size="sm" class="flex-1 md:flex-none" @click="approve(payment)">{{
                                                t('admin.payments.approve')
                                            }}</Button>
                                            <Button size="sm" variant="destructive" class="flex-1 md:flex-none" @click="startReject(payment)">{{
                                                t('admin.payments.reject')
                                            }}</Button>
                                        </div>
                                    </div>

                                    <div v-if="rejectingId === payment.id" class="mt-3 space-y-2 border-t pt-3">
                                        <label class="text-[10px] font-medium text-slate-600">{{ t('admin.payments.rejectReasonLabel') }}</label>
                                        <textarea
                                            v-model="rejectReasons[payment.id]"
                                            rows="2"
                                            :placeholder="t('admin.payments.rejectReasonPlaceholder')"
                                            class="w-full rounded-md border border-input bg-background px-2 py-1.5 text-xs"
                                        />
                                        <div class="flex gap-2">
                                            <Button size="sm" variant="destructive" @click="confirmReject(payment)">{{
                                                t('admin.payments.confirmReject')
                                            }}</Button>
                                            <Button size="sm" variant="outline" @click="cancelReject">{{ t('common.cancel') }}</Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <Pagination
                            :current-page="paymentCases.current_page"
                            :last-page="paymentCases.last_page"
                            :prev-page-url="paymentCases.prev_page_url"
                            :next-page-url="paymentCases.next_page_url"
                        />
                    </div>
                </Transition>
            </template>
        </div>
    </div>
</template>
