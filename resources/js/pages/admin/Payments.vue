<script setup lang="ts">
import { Badge, type BadgeVariants } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { CheckCircle2, Clock, DollarSign, ListChecks, XCircle, type LucideIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AdminLayout });

interface PaymentRow {
    id: number;
    user_name: string;
    user_email: string;
    label: string;
    amount: number;
    has_slip: boolean;
    status: 'pending' | 'approved' | 'rejected';
    wants_receipt: boolean;
    wants_mail_delivery: boolean;
    mail_delivery_fee_charged: number | null;
    created_at: string;
}

const props = defineProps<{
    payments: PaymentRow[];
}>();

const { t } = useI18n();

const approve = (payment: PaymentRow) => {
    router.post(route('admin.payments.approve', payment.id), {}, { preserveScroll: true });
};

const reject = (payment: PaymentRow) => {
    router.post(route('admin.payments.reject', payment.id), {}, { preserveScroll: true });
};

const statusLabel = computed<Record<PaymentRow['status'], string>>(() => ({
    pending: t('admin.payments.statusPending'),
    approved: t('admin.payments.statusApproved'),
    rejected: t('admin.payments.statusRejected'),
}));

const statusVariant: Record<PaymentRow['status'], NonNullable<BadgeVariants['variant']>> = {
    pending: 'warning',
    approved: 'success',
    rejected: 'destructive',
};

type StatusTab = 'all' | PaymentRow['status'];

const activeTab = ref<StatusTab>('all');

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

// Same status colors, carried onto each payment card's border so the list
// reads consistently with whichever tab color it belongs to.
const cardBorderClass: Record<PaymentRow['status'], string> = {
    pending: 'border-amber-300',
    approved: 'border-emerald-300',
    rejected: 'border-red-300',
};

const tabs = computed<{ value: StatusTab; label: string; count: number }[]>(() => [
    { value: 'all', label: t('admin.payments.tabAll'), count: props.payments.length },
    { value: 'pending', label: statusLabel.value.pending, count: props.payments.filter((p) => p.status === 'pending').length },
    { value: 'approved', label: statusLabel.value.approved, count: props.payments.filter((p) => p.status === 'approved').length },
    { value: 'rejected', label: statusLabel.value.rejected, count: props.payments.filter((p) => p.status === 'rejected').length },
]);

const filteredPayments = computed(() =>
    activeTab.value === 'all' ? props.payments : props.payments.filter((p) => p.status === activeTab.value),
);
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

            <p v-if="payments.length === 0" class="py-8 mt-4 text-xs italic text-center border border-dashed rounded-xl bg-slate-50 text-slate-400">
                {{ t('admin.payments.empty') }}
            </p>

            <template v-else>
                <Tabs v-model="activeTab" class="w-full mt-4">
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
                    <div :key="activeTab" class="mt-3 space-y-2">
                        <p
                            v-if="filteredPayments.length === 0"
                            class="py-8 text-xs italic text-center border border-dashed rounded-xl bg-slate-50 text-slate-400"
                        >
                            {{ t('admin.payments.emptyFiltered') }}
                        </p>

                        <div
                            v-for="payment in filteredPayments"
                            :key="payment.id"
                            :class="[
                                'flex flex-col items-start justify-between gap-4 rounded-xl border bg-slate-50 p-4 text-xs md:flex-row md:items-center',
                                cardBorderClass[payment.status],
                            ]"
                        >
                    <div>
                        <div class="mb-1.5 flex flex-wrap items-center gap-2">
                            <span class="rounded bg-indigo-100 px-1.5 py-0.5 text-[9px] font-bold text-indigo-800">{{ payment.label }}</span>
                            <span class="font-mono text-[9px] text-slate-400">{{ t('admin.payments.idPrefix') }}{{ payment.id }}</span>
                            <Badge v-if="payment.status !== 'pending'" :variant="statusVariant[payment.status]">{{
                                statusLabel[payment.status]
                            }}</Badge>
                        </div>
                        <p class="font-semibold text-slate-800">
                            {{ t('admin.payments.payerPrefix') }}{{ payment.user_name }}
                            <span class="font-normal text-slate-500">({{ payment.user_email }})</span>
                        </p>
                        <p class="mt-0.5 text-[10px] text-slate-500">
                            <a v-if="payment.has_slip" :href="route('admin.payments.slip', payment.id)" target="_blank" class="text-indigo-600 underline">
                                {{ t('admin.payments.viewSlip') }}
                            </a>
                            <span v-else>{{ t('admin.payments.noSlip') }}</span>
                            <span v-if="payment.wants_receipt"> &middot; {{ t('admin.payments.receiptWanted') }}</span>
                            <span v-if="payment.wants_mail_delivery">
                                &middot; {{ t('admin.payments.mailDeliveryFee', { fee: (payment.mail_delivery_fee_charged ?? 0).toLocaleString() }) }}
                            </span>
                        </p>
                        <p class="mt-1 font-extrabold text-indigo-600">
                            {{ t('admin.payments.amountPrefix') }}{{ payment.amount.toLocaleString() }} ฿
                        </p>
                    </div>

                    <div v-if="payment.status === 'pending'" class="flex w-full gap-2 md:w-auto">
                        <Button size="sm" class="flex-1 md:flex-none" @click="approve(payment)">{{ t('admin.payments.approve') }}</Button>
                        <Button size="sm" variant="destructive" class="flex-1 md:flex-none" @click="reject(payment)">{{
                            t('admin.payments.reject')
                        }}</Button>
                    </div>
                </div>
                    </div>
                </Transition>
            </template>
        </div>
    </div>
</template>
