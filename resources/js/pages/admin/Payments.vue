<script setup lang="ts">
import { Badge, type BadgeVariants } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { DollarSign } from 'lucide-vue-next';
import { computed } from 'vue';
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

defineProps<{
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
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <Head :title="t('nav.admin.payments')" />

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="border-b border-slate-100 pb-2">
                <h2 class="flex items-center gap-1.5 text-sm font-bold text-slate-800">
                    <DollarSign class="h-4 w-4 text-indigo-600" />
                    {{ t('admin.payments.title') }}
                </h2>
                <p class="text-[10px] text-slate-500">{{ t('admin.payments.description') }}</p>
            </div>

            <div class="mt-4 space-y-2">
                <p v-if="payments.length === 0" class="rounded-xl border border-dashed bg-slate-50 py-8 text-center text-xs italic text-slate-400">
                    {{ t('admin.payments.empty') }}
                </p>

                <div
                    v-for="payment in payments"
                    :key="payment.id"
                    class="flex flex-col items-start justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs md:flex-row md:items-center"
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
        </div>
    </div>
</template>
