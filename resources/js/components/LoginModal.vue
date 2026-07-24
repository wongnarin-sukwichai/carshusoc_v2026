<script setup lang="ts">
import AdminLoginModal from '@/components/AdminLoginModal.vue';
import ForgotPasswordModal from '@/components/ForgotPasswordModal.vue';
import InputError from '@/components/InputError.vue';
import RegisterModal from '@/components/RegisterModal.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/vue3';
import { LoaderCircle, ShieldCheck } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const open = ref(false);
const adminOpen = ref(false);
const forgotOpen = ref(false);
const registerOpen = ref(false);

const switchToAdminLogin = () => {
    open.value = false;
    adminOpen.value = true;
};

const switchToForgotPassword = () => {
    open.value = false;
    forgotOpen.value = true;
};

const switchToRegister = () => {
    open.value = false;
    registerOpen.value = true;
};

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
            form.reset();
        },
        onError: () => form.reset('password'),
    });
};
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <slot>
                <button type="button" class="rounded-lg px-4 py-1.5 text-xs font-bold text-slate-300 hover:text-white">
                    {{ t('welcome.login') }}
                </button>
            </slot>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ t('auth.login.title') }}</DialogTitle>
            </DialogHeader>
            <p class="-mt-2 text-sm text-slate-400">{{ t('auth.login.description') }}</p>

            <form @submit.prevent="submit" class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="login-modal-email">{{ t('auth.login.email') }}</Label>
                    <Input
                        id="login-modal-email"
                        type="email"
                        required
                        autofocus
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="login-modal-password">{{ t('auth.login.password') }}</Label>
                        <button type="button" class="text-sm text-blue-600 hover:underline" @click="switchToForgotPassword">
                            {{ t('auth.login.forgotPassword') }}
                        </button>
                    </div>
                    <Input
                        id="login-modal-password"
                        type="password"
                        required
                        autocomplete="current-password"
                        v-model="form.password"
                        placeholder="Password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <Label class="flex items-center space-x-3">
                    <Checkbox v-model:checked="form.remember" />
                    <span>{{ t('auth.login.remember') }}</span>
                </Label>

                <Button type="submit" class="w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                    {{ t('auth.login.submit') }}
                </Button>
            </form>

            <p class="text-sm text-center text-muted-foreground">
                {{ t('auth.login.noAccount') }}
                <button type="button" class="font-medium text-primary hover:underline" @click="switchToRegister">
                    {{ t('auth.login.signUp') }}
                </button>
            </p>

            <button
                type="button"
                class="mx-auto flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:underline"
                @click="switchToAdminLogin"
            >
                <ShieldCheck class="h-3.5 w-3.5" />
                {{ t('auth.login.staffLink') }}
            </button>
        </DialogContent>
    </Dialog>

    <AdminLoginModal v-model:open="adminOpen" />
    <ForgotPasswordModal v-model:open="forgotOpen" />
    <RegisterModal v-model:open="registerOpen" :show-trigger="false" />
</template>
