<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { confirmDialog } from '@/lib/swal';
import { type Paginated } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Search, UserRoundCog, Users } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

interface UserRow {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    identity_type: 'national_id' | 'passport' | null;
    identity_number: string | null;
}

const props = defineProps<{
    users: Paginated<UserRow>;
    search: string | null;
}>();

const search = ref(props.search ?? '');
let searchDebounce: ReturnType<typeof setTimeout> | undefined;

watch(search, (value) => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
        router.get(route('admin.registrants'), { search: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
    }, 350);
});

const form = useForm({
    name: '',
    email: '',
    password: '',
});

const submit = () => {
    form.post(route('admin.registrants.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const impersonate = async (user: UserRow) => {
    const confirmed = await confirmDialog({
        title: t('common.areYouSure'),
        text: t('admin.registrants.impersonateConfirmText', { name: user.name }),
    });

    if (!confirmed) return;

    router.post(route('admin.registrants.impersonate', user.id));
};
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <Head :title="t('nav.admin.registrants')" />

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="border-b border-slate-100 pb-2">
                <h2 class="flex items-center gap-1.5 text-sm font-bold text-slate-800">
                    <Users class="h-4 w-4 text-indigo-600" />
                    {{ t('admin.registrants.title') }}
                </h2>
                <p class="text-[10px] text-slate-500">{{ t('admin.registrants.description') }}</p>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-6 lg:grid-cols-4">
                <form class="h-fit space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4" @submit.prevent="submit">
                    <h4 class="text-xs font-bold text-indigo-900">{{ t('admin.registrants.addNew') }}</h4>

                    <div class="grid gap-1">
                        <Label for="registrant-name" class="text-[10px] text-slate-500">{{ t('admin.registrants.nameLabel') }}</Label>
                        <Input id="registrant-name" v-model="form.name" type="text" required class="bg-white text-xs" />
                        <p v-if="form.errors.name" class="text-[10px] text-destructive">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid gap-1">
                        <Label for="registrant-email" class="text-[10px] text-slate-500">{{ t('admin.registrants.emailLabel') }}</Label>
                        <Input id="registrant-email" v-model="form.email" type="email" required class="bg-white text-xs" />
                        <p v-if="form.errors.email" class="text-[10px] text-destructive">{{ form.errors.email }}</p>
                    </div>

                    <div class="grid gap-1">
                        <Label for="registrant-password" class="text-[10px] text-slate-500">{{ t('admin.registrants.passwordLabel') }}</Label>
                        <Input
                            id="registrant-password"
                            v-model="form.password"
                            type="password"
                            required
                            :placeholder="t('admin.registrants.passwordPlaceholder')"
                            class="bg-white text-xs"
                        />
                        <p v-if="form.errors.password" class="text-[10px] text-destructive">{{ form.errors.password }}</p>
                    </div>

                    <Button type="submit" class="w-full" size="sm" :disabled="form.processing">
                        {{ t('admin.registrants.submit') }}
                    </Button>
                </form>

                <div class="lg:col-span-3">
                    <div class="relative mb-3">
                        <Search class="absolute top-1/2 left-2.5 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                        <Input v-model="search" type="text" :placeholder="t('admin.registrants.searchPlaceholder')" class="pl-8 text-xs" />
                    </div>

                    <div class="min-w-0 overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th class="p-2.5 whitespace-nowrap">{{ t('admin.registrants.colName') }}</th>
                                    <th class="p-2.5 whitespace-nowrap">{{ t('admin.registrants.colEmail') }}</th>
                                    <th class="p-2.5 whitespace-nowrap">{{ t('admin.registrants.colPhone') }}</th>
                                    <th class="p-2.5 whitespace-nowrap">{{ t('admin.registrants.colNationalId') }}</th>
                                    <th class="p-2.5 whitespace-nowrap">{{ t('admin.registrants.colPassport') }}</th>
                                    <th class="p-2.5 whitespace-nowrap text-center">{{ t('admin.registrants.colImpersonate') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="users.data.length === 0">
                                    <td colspan="6" class="p-4 text-center text-muted-foreground">{{ t('admin.registrants.empty') }}</td>
                                </tr>
                                <tr v-for="user in users.data" :key="user.id" class="border-t border-slate-200 hover:bg-slate-50">
                                    <td class="p-2.5 font-semibold whitespace-nowrap">{{ user.name }}</td>
                                    <td class="p-2.5 font-mono text-slate-500">{{ user.email }}</td>
                                    <td class="p-2.5 whitespace-nowrap">{{ user.phone ?? '—' }}</td>
                                    <td class="p-2.5 font-mono whitespace-nowrap">
                                        {{ user.identity_type === 'national_id' ? user.identity_number : '—' }}
                                    </td>
                                    <td class="p-2.5 font-mono whitespace-nowrap">
                                        {{ user.identity_type === 'passport' ? user.identity_number : '—' }}
                                    </td>
                                    <td class="p-2.5 text-center whitespace-nowrap">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-1 text-[10px] font-bold text-indigo-700 hover:bg-indigo-100"
                                            @click="impersonate(user)"
                                        >
                                            <UserRoundCog class="h-3 w-3" />
                                            {{ t('admin.registrants.impersonateAction') }}
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <Pagination
                        :current-page="users.current_page"
                        :last-page="users.last_page"
                        :prev-page-url="users.prev_page_url"
                        :next-page-url="users.next_page_url"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
