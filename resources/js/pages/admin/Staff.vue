<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { confirmDialog } from '@/lib/swal';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Pencil, UserCheck } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

interface AdminRow {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'staff';
}

const props = defineProps<{
    admins: AdminRow[];
}>();

const editingId = ref<number | null>(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'staff',
});

const editAdmin = (admin: AdminRow) => {
    editingId.value = admin.id;
    form.name = admin.name;
    form.email = admin.email;
    form.password = '';
    form.role = admin.role;
};

const resetForm = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    if (editingId.value) {
        form.put(route('admin.staff.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => resetForm(),
        });
    } else {
        form.post(route('admin.staff.store'), {
            preserveScroll: true,
            onSuccess: () => resetForm(),
        });
    }
};

const remove = async (admin: AdminRow) => {
    const confirmed = await confirmDialog({
        title: t('common.areYouSure'),
        text: t('admin.staff.deleteConfirmText', { name: admin.name }),
    });

    if (!confirmed) return;

    router.delete(route('admin.staff.destroy', admin.id), { preserveScroll: true });
};
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <Head :title="t('nav.admin.staff')" />

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="border-b border-slate-100 pb-2">
                <h2 class="flex items-center gap-1.5 text-sm font-bold text-slate-800">
                    <UserCheck class="h-4 w-4 text-indigo-600" />
                    {{ t('admin.staff.title') }}
                </h2>
                <p class="text-[10px] text-slate-500">{{ t('admin.staff.description') }}</p>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-6 lg:grid-cols-4">
                <form class="h-fit space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4" @submit.prevent="submit">
                    <h4 class="text-xs font-bold text-indigo-900">
                        {{ editingId ? t('admin.staff.editTitle', { name: form.name }) : t('admin.staff.addNew') }}
                    </h4>

                    <div class="grid gap-1">
                        <Label for="staff-name" class="text-[10px] text-slate-500">{{ t('admin.staff.nameLabel') }}</Label>
                        <input
                            id="staff-name"
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full rounded-lg border border-slate-200 bg-white p-2 text-xs"
                        />
                        <p v-if="form.errors.name" class="text-[10px] text-destructive">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid gap-1">
                        <Label for="staff-email" class="text-[10px] text-slate-500">{{ t('admin.staff.emailLabel') }}</Label>
                        <input
                            id="staff-email"
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full rounded-lg border border-slate-200 bg-white p-2 text-xs"
                        />
                        <p v-if="form.errors.email" class="text-[10px] text-destructive">{{ form.errors.email }}</p>
                    </div>

                    <div class="grid gap-1">
                        <Label for="staff-password" class="text-[10px] text-slate-500">{{ t('admin.staff.passwordLabel') }}</Label>
                        <input
                            id="staff-password"
                            v-model="form.password"
                            type="password"
                            :required="!editingId"
                            :placeholder="editingId ? t('admin.staff.passwordEditPlaceholder') : t('admin.staff.passwordPlaceholder')"
                            class="w-full rounded-lg border border-slate-200 bg-white p-2 text-xs"
                        />
                        <p v-if="form.errors.password" class="text-[10px] text-destructive">{{ form.errors.password }}</p>
                    </div>

                    <div class="grid gap-1">
                        <Label for="staff-role" class="text-[10px] text-slate-500">{{ t('admin.staff.roleLabel') }}</Label>
                        <select id="staff-role" v-model="form.role" class="w-full rounded-lg border border-slate-200 bg-white p-2 text-xs">
                            <option value="admin">{{ t('admin.staff.roleAdmin') }}</option>
                            <option value="staff">{{ t('admin.staff.roleStaff') }}</option>
                        </select>
                        <p v-if="form.errors.role" class="text-[10px] text-destructive">{{ form.errors.role }}</p>
                    </div>

                    <div class="flex gap-2">
                        <Button type="submit" class="flex-1" size="sm" :disabled="form.processing">
                            {{ editingId ? t('common.save') : t('admin.staff.submit') }}
                        </Button>
                        <Button v-if="editingId" type="button" variant="outline" size="sm" @click="resetForm">{{ t('common.cancel') }}</Button>
                    </div>
                </form>

                <div class="min-w-0 overflow-x-auto rounded-xl border border-slate-200 lg:col-span-3">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="p-2.5 whitespace-nowrap">{{ t('admin.staff.colName') }}</th>
                                <th class="p-2.5 whitespace-nowrap">{{ t('admin.staff.colEmail') }}</th>
                                <th class="p-2.5 whitespace-nowrap">{{ t('admin.staff.colRole') }}</th>
                                <th class="p-2.5 whitespace-nowrap text-center">{{ t('admin.staff.colEdit') }}</th>
                                <th class="p-2.5 whitespace-nowrap text-center">{{ t('admin.staff.colActions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="member in props.admins" :key="member.id" class="border-t border-slate-200 hover:bg-slate-50">
                                <td class="p-2.5 font-semibold whitespace-nowrap">{{ member.name }}</td>
                                <td class="p-2.5 font-mono text-slate-500">{{ member.email }}</td>
                                <td class="p-2.5 whitespace-nowrap">
                                    <span
                                        class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                                        :class="member.role === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600'"
                                    >
                                        {{ member.role === 'admin' ? 'Admin' : 'Staff' }}
                                    </span>
                                </td>
                                <td class="p-2.5 text-center whitespace-nowrap">
                                    <button
                                        type="button"
                                        class="rounded p-1 text-amber-600 hover:bg-muted"
                                        :title="t('admin.staff.editAction')"
                                        @click="editAdmin(member)"
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                    </button>
                                </td>
                                <td class="p-2.5 text-center whitespace-nowrap">
                                    <button type="button" class="cursor-pointer font-bold text-red-500 hover:text-red-600" @click="remove(member)">
                                        {{ t('admin.staff.removeAction') }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
