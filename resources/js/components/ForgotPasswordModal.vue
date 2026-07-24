<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const open = defineModel<boolean>('open', { default: false });

const { t } = useI18n();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'), {
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
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ t('auth.forgotPassword.title') }}</DialogTitle>
            </DialogHeader>
            <p class="-mt-2 text-sm text-muted-foreground">{{ t('auth.forgotPassword.description') }}</p>

            <form @submit.prevent="submit" class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="forgot-password-email">{{ t('auth.login.email') }}</Label>
                    <Input
                        id="forgot-password-email"
                        type="email"
                        required
                        autofocus
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <Button type="submit" class="w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    {{ t('auth.forgotPassword.submit') }}
                </Button>
            </form>
        </DialogContent>
    </Dialog>
</template>
