<script setup lang="ts">
import BrandHeader from '@/components/BrandHeader.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

interface Props {
    token: string;
    email: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <div class="flex min-h-screen flex-col bg-white text-slate-800 dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
        <Head title="Reset password" />

        <BrandHeader accent="blue" :home-href="route('home')" />

        <div class="flex flex-1 items-center justify-center p-6">
            <div class="w-full max-w-sm space-y-6">
                <div class="space-y-2 text-center">
                    <h1 class="text-xl font-medium">Reset password</h1>
                    <p class="text-sm text-muted-foreground">Please enter your new password below</p>
                </div>

                <form @submit.prevent="submit">
                    <div class="grid gap-6">
                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input id="email" type="email" name="email" autocomplete="email" v-model="form.email" class="mt-1 block w-full" readonly />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password">Password</Label>
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                autocomplete="new-password"
                                v-model="form.password"
                                class="mt-1 block w-full"
                                autofocus
                                placeholder="Password"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password_confirmation"> Confirm Password </Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                autocomplete="new-password"
                                v-model="form.password_confirmation"
                                class="mt-1 block w-full"
                                placeholder="Confirm password"
                            />
                            <InputError :message="form.errors.password_confirmation" />
                        </div>

                        <Button type="submit" class="mt-4 w-full" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            Reset password
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
