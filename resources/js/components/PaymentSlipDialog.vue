<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
    payableType: 'course_enrollment' | 'exam_registration' | 'translation_request';
    payableId: number;
    title: string;
    amount: number;
    mailDeliveryAvailable?: boolean;
    mailDeliveryFee?: number | null;
    triggerLabel?: string;
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

const onFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    form.slip = target.files?.[0] ?? null;
};

const submit = () => {
    form.post(route('user.payments.store'), {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
            form.reset();
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
                <DialogTitle>{{ t('components.paymentSlipDialog.title') }}</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="rounded-lg border bg-muted/40 p-3 text-sm">
                    <p class="font-medium">{{ title }}</p>
                    <p class="text-muted-foreground">
                        {{ t('components.paymentSlipDialog.amount', { amount: totalAmount.toLocaleString() }) }}
                    </p>
                </div>

                <div class="grid gap-2">
                    <label for="slip" class="text-sm font-medium">{{ t('components.paymentSlipDialog.fileLabel') }}</label>
                    <input
                        id="slip"
                        type="file"
                        accept=".jpg,.jpeg,.png,.pdf"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium"
                        required
                        @change="onFileChange"
                    />
                    <InputError :message="form.errors.slip" />
                </div>

                <label class="flex items-center gap-2 text-sm">
                    <Checkbox v-model:checked="form.wants_receipt" />
                    {{ t('components.paymentSlipDialog.wantsReceipt') }}
                </label>

                <label v-if="mailDeliveryAvailable" class="flex items-center gap-2 text-sm">
                    <Checkbox v-model:checked="form.wants_mail_delivery" />
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
