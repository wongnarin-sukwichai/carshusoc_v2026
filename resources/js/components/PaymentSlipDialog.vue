<script setup lang="ts">
import FileDropzone from '@/components/FileDropzone.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { successToast } from '@/lib/swal';
import { useForm } from '@inertiajs/vue3';
import { Copy, FileText, Landmark, LoaderCircle, Receipt, Truck, Wallet } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

// Real bank transfer details for CARS-HUSOC — a plain account number can't
// be turned into a scannable auto-pay (PromptPay) QR, since that requires a
// PromptPay proxy ID (phone/citizen ID), not a bank account number. The QR
// image at public/images/payment-qr.svg is a placeholder — swap that file
// for the bank-issued QR image (exported from the SCB Easy app) whenever
// it's available; no code change needed since it's a static public asset.
const BANK_ACCOUNT_NUMBER = '406-552932-8';

const copyAccountNumber = async () => {
    await navigator.clipboard.writeText(BANK_ACCOUNT_NUMBER.replace(/-/g, ''));
    successToast(t('components.paymentSlipDialog.accountNumberCopied'));
};

const props = defineProps<{
    payableType: 'course_enrollment' | 'exam_registration' | 'translation_request';
    payableId: number;
    title: string;
    amount: number;
    mailDeliveryAvailable?: boolean;
    mailDeliveryFee?: number | null;
    triggerLabel?: string;
}>();

const emit = defineEmits<{
    success: [];
}>();

const open = ref(false);

const form = useForm({
    payable_type: props.payableType,
    payable_id: props.payableId,
    slip: null as File | null,
    wants_receipt: false,
    wants_mail_delivery: false,
});

const totalAmount = computed(() => props.amount + (form.wants_mail_delivery ? (props.mailDeliveryFee ?? 0) : 0));

const submit = () => {
    form.post(route('user.payments.store'), {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
            form.reset();
            emit('success');
        },
    });
};
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <Button size="sm">{{ triggerLabel ?? t('components.paymentSlipDialog.trigger') }}</Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                        <Receipt class="h-4 w-4" />
                    </span>
                    {{ t('components.paymentSlipDialog.title') }}
                </DialogTitle>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="rounded-lg border border-indigo-200 bg-indigo-50/60 p-3">
                    <p class="mb-2 flex items-center gap-1.5 text-sm font-bold text-indigo-900">
                        <Landmark class="h-4 w-4 text-indigo-600" />
                        {{ t('components.paymentSlipDialog.bankTransferTitle') }}
                    </p>
                    <div class="flex items-center gap-3">
                        <img
                            src="/images/payment-qr.svg"
                            :alt="t('components.paymentSlipDialog.qrAlt')"
                            class="h-24 w-24 shrink-0 rounded-lg border border-indigo-100 bg-white object-contain p-1"
                        />
                        <div class="min-w-0 flex-1 space-y-0.5 text-xs">
                            <p class="text-muted-foreground">{{ t('components.paymentSlipDialog.bankName') }}</p>
                            <p class="font-medium text-indigo-950">{{ t('components.paymentSlipDialog.bankNameValue') }}</p>
                            <p class="mt-1.5 text-muted-foreground">{{ t('components.paymentSlipDialog.accountName') }}</p>
                            <p class="font-medium text-indigo-950">{{ t('components.paymentSlipDialog.accountNameValue') }}</p>
                            <p class="mt-1.5 text-muted-foreground">{{ t('components.paymentSlipDialog.accountNumber') }}</p>
                            <button
                                type="button"
                                class="flex items-center gap-1 font-mono font-bold text-indigo-600 hover:underline"
                                @click="copyAccountNumber"
                            >
                                {{ BANK_ACCOUNT_NUMBER }}
                                <Copy class="h-3 w-3" />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50/60 p-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                        <Wallet class="h-4 w-4" />
                    </span>
                    <div class="min-w-0 text-sm">
                        <p class="truncate font-medium text-emerald-950">{{ title }}</p>
                        <p class="font-bold text-emerald-700">
                            {{ t('components.paymentSlipDialog.amount', { amount: totalAmount.toLocaleString() }) }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-2">
                    <label for="slip" class="flex items-center gap-1.5 text-sm font-medium">
                        <FileText class="h-4 w-4 text-indigo-600" />
                        {{ t('components.paymentSlipDialog.fileLabel') }}
                    </label>

                    <FileDropzone
                        id="slip"
                        v-model="form.slip"
                        accept=".jpg,.jpeg,.png,.pdf"
                        required
                        :hint="t('components.paymentSlipDialog.chooseFileHint')"
                        :formats-hint="t('components.paymentSlipDialog.chooseFileFormats')"
                    />
                    <InputError :message="form.errors.slip" />
                </div>

                <label class="flex items-center gap-2 rounded-lg border p-2.5 text-sm">
                    <Checkbox v-model:checked="form.wants_receipt" />
                    <FileText class="h-4 w-4 shrink-0 text-slate-400" />
                    {{ t('components.paymentSlipDialog.wantsReceipt') }}
                </label>

                <label v-if="mailDeliveryAvailable" class="flex items-center gap-2 rounded-lg border p-2.5 text-sm">
                    <Checkbox v-model:checked="form.wants_mail_delivery" />
                    <Truck class="h-4 w-4 shrink-0 text-slate-400" />
                    {{ t('components.paymentSlipDialog.wantsMailDelivery', { fee: (mailDeliveryFee ?? 0).toLocaleString() }) }}
                </label>

                <DialogFooter>
                    <Button type="submit" class="w-full" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        {{ t('components.paymentSlipDialog.submit') }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
