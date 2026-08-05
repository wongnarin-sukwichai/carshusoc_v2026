<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/vue3';
import { LoaderCircle, ShieldCheck } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const open = defineModel<boolean>('open', { default: false });

const { t } = useI18n();

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post(route('admin.login'), {
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
        <DialogContent class="text-white border-slate-700 bg-gradient-to-r from-purple-400 to-purple-900 sm:rounded-xl">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-black">
                    <ShieldCheck class="w-5 h-5 text-amber-400" />
                    {{ t('admin.login.title') }}
                </DialogTitle>
            </DialogHeader>
            <p class="-mt-2 text-sm text-slate-100">{{ t('admin.login.description') }}</p>

            <form @submit.prevent="submit" class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="admin-login-modal-email" class="text-black">{{ t('admin.login.email') }}</Label>
                    <Input
                        id="admin-login-modal-email"
                        type="email"
                        required
                        autofocus
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="name@msu.ac.th"
                        class="text-black border-slate-700 placeholder:text-slate-500 focus-visible:ring-amber-500"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="admin-login-modal-password" class="text-black">{{ t('admin.login.password') }}</Label>
                    <Input
                        id="admin-login-modal-password"
                        type="password"
                        required
                        autocomplete="current-password"
                        v-model="form.password"
                        placeholder="Password"
                        class="text-black border-slate-700 placeholder:text-slate-500 focus-visible:ring-amber-500"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <Button type="submit" class="w-full bg-amber-500 text-slate-900 hover:bg-amber-400" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                    {{ t('admin.login.submit') }}
                </Button>
            </form>
        </DialogContent>
    </Dialog>
</template>
