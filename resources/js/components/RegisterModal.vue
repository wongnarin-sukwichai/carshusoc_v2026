<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/vue3';
import { LoaderCircle, MailCheck } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

withDefaults(defineProps<{ showTrigger?: boolean }>(), { showTrigger: true });

const open = defineModel<boolean>('open', { default: false });

const submitted = ref(false);

// Registration triggers a real synchronous SMTP send (the built-in
// ResetPassword notification isn't queued), so this can take a few seconds —
// keep the dialog open with a clear loading state instead of letting it look
// stuck, then swap to an explicit "check your email" panel once it lands.
watch(open, (value) => {
    if (!value) submitted.value = false;
});

const form = useForm({
    name: '',
    email: '',
});

const submit = () => {
    form.post(route('register'), {
        preserveScroll: true,
        onSuccess: () => {
            submitted.value = true;
            form.reset();
        },
    });
};
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger v-if="showTrigger" as-child>
            <slot>
                <button type="button" class="rounded-lg bg-blue-600 px-4 py-1.5 text-xs font-bold hover:bg-blue-700">
                    {{ t('welcome.register') }}
                </button>
            </slot>
        </DialogTrigger>
        <DialogContent>
            <template v-if="!submitted">
                <DialogHeader>
                    <DialogTitle>{{ t('auth.register.title') }}</DialogTitle>
                </DialogHeader>
                <p class="-mt-2 text-sm text-muted-foreground">{{ t('auth.register.description') }}</p>

                <form @submit.prevent="submit" class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="register-modal-name">{{ t('auth.register.name') }}</Label>
                        <Input
                            id="register-modal-name"
                            type="text"
                            required
                            autofocus
                            autocomplete="name"
                            v-model="form.name"
                            placeholder="Full name"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="register-modal-email">{{ t('auth.register.email') }}</Label>
                        <Input
                            id="register-modal-email"
                            type="email"
                            required
                            autocomplete="email"
                            v-model="form.email"
                            placeholder="email@example.com"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <Button type="submit" class="w-full" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        {{ form.processing ? t('auth.register.submitting') : t('auth.register.submit') }}
                    </Button>
                </form>
            </template>

            <template v-else>
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <MailCheck class="h-5 w-5 text-emerald-600" />
                        {{ t('auth.register.checkEmailTitle') }}
                    </DialogTitle>
                </DialogHeader>
                <p class="text-sm text-muted-foreground">{{ t('auth.register.checkEmailDescription') }}</p>

                <Button type="button" class="w-full" @click="open = false">{{ t('common.close') }}</Button>
            </template>
        </DialogContent>
    </Dialog>
</template>
