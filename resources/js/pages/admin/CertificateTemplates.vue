<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { confirmDialog } from '@/lib/swal';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Star, Trash2 } from 'lucide-vue-next';
import { computed, reactive } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

interface CertificateTemplateRow {
    id: number;
    service_center_code: string;
    name: string;
    title: string;
    subtitle: string | null;
    signatory1_name: string;
    signatory1_title: string;
    signatory2_name: string | null;
    signatory2_title: string | null;
    signatory3_name: string | null;
    signatory3_title: string | null;
    border_color: string;
    is_default: boolean;
}

const props = defineProps<{
    templates: CertificateTemplateRow[];
}>();

const centers = computed(() => [
    { code: 'training', label: t('admin.certificateTemplates.centerTraining'), hasThirdSignatory: false },
    { code: 'exam', label: t('admin.certificateTemplates.centerExam'), hasThirdSignatory: true },
]);

const templatesFor = (code: string) => props.templates.filter((row) => row.service_center_code === code);

// ---------- edit existing templates ----------
const editForms = Object.fromEntries(
    props.templates.map((template) => [
        template.id,
        useForm({
            name: template.name,
            title: template.title,
            subtitle: template.subtitle ?? '',
            signatory1_name: template.signatory1_name,
            signatory1_title: template.signatory1_title,
            signatory2_name: template.signatory2_name ?? '',
            signatory2_title: template.signatory2_title ?? '',
            signatory3_name: template.signatory3_name ?? '',
            signatory3_title: template.signatory3_title ?? '',
            border_color: template.border_color,
            is_default: template.is_default,
        }),
    ]),
);

const save = (templateId: number) => {
    editForms[templateId].put(route('admin.certificate-templates.update', templateId), { preserveScroll: true });
};

const remove = async (template: CertificateTemplateRow) => {
    const confirmed = await confirmDialog({
        title: t('common.areYouSure'),
        text: `${t('common.delete')}: "${template.name}" — ${t('common.actionCannotBeUndone')}`,
    });

    if (!confirmed) return;

    router.delete(route('admin.certificate-templates.destroy', template.id), { preserveScroll: true });
};

// ---------- add a new template per center ----------
const showNewForm = reactive<Record<string, boolean>>({ training: false, exam: false });

const emptyTemplate = (code: string) => ({
    service_center_code: code,
    name: '',
    title: '',
    subtitle: '',
    signatory1_name: '',
    signatory1_title: '',
    signatory2_name: '',
    signatory2_title: '',
    signatory3_name: '',
    signatory3_title: '',
    border_color: '#4f46e5',
    is_default: false,
});

const newForms = Object.fromEntries(centers.value.map((c) => [c.code, useForm(emptyTemplate(c.code))]));

const createTemplate = (code: string) => {
    newForms[code].post(route('admin.certificate-templates.store'), {
        preserveScroll: true,
        onSuccess: () => {
            newForms[code].reset();
            showNewForm[code] = false;
        },
    });
};

const hasThirdSignatory = (code: string) => centers.value.find((c) => c.code === code)?.hasThirdSignatory ?? false;
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <Head :title="t('nav.admin.certificateTemplates')" />
            <HeadingSmall :title="t('admin.certificateTemplates.title')" :description="t('admin.certificateTemplates.description')" />

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div v-for="center in centers" :key="center.code" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wide text-muted-foreground">{{ center.label }}</h3>
                        <Button size="sm" variant="outline" @click="showNewForm[center.code] = !showNewForm[center.code]">
                            <Plus class="mr-1 h-3.5 w-3.5" />{{ t('admin.certificateTemplates.addNew') }}
                        </Button>
                    </div>

                    <!-- New template form -->
                    <form
                        v-if="showNewForm[center.code]"
                        class="space-y-3 rounded-xl border-2 border-dashed p-4"
                        @submit.prevent="createTemplate(center.code)"
                    >
                        <div class="grid gap-1.5">
                            <Label>{{ t('admin.certificateTemplates.fieldName') }}</Label>
                            <Input v-model="newForms[center.code].name" :placeholder="t('admin.certificateTemplates.namePlaceholder')" />
                            <p v-if="newForms[center.code].errors.name" class="text-xs text-destructive">{{ newForms[center.code].errors.name }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label>{{ t('admin.certificateTemplates.fieldTitle') }}</Label>
                            <Input v-model="newForms[center.code].title" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label>{{ t('admin.certificateTemplates.fieldSubtitle') }}</Label>
                            <Textarea v-model="newForms[center.code].subtitle" rows="2" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="grid gap-1.5">
                                <Label>{{ t('admin.certificateTemplates.signatory1') }}</Label>
                                <Input v-model="newForms[center.code].signatory1_name" />
                                <Input v-model="newForms[center.code].signatory1_title" :placeholder="t('admin.certificateTemplates.positionPlaceholder')" />
                            </div>
                            <div class="grid gap-1.5">
                                <Label>{{ t('admin.certificateTemplates.signatory2') }}</Label>
                                <Input v-model="newForms[center.code].signatory2_name" />
                                <Input v-model="newForms[center.code].signatory2_title" :placeholder="t('admin.certificateTemplates.positionPlaceholder')" />
                            </div>
                        </div>
                        <div v-if="center.hasThirdSignatory" class="grid gap-1.5">
                            <Label>{{ t('admin.certificateTemplates.signatory3') }}</Label>
                            <Input v-model="newForms[center.code].signatory3_name" />
                            <Input v-model="newForms[center.code].signatory3_title" :placeholder="t('admin.certificateTemplates.positionPlaceholder')" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label>{{ t('admin.certificateTemplates.fieldBorderColor') }}</Label>
                            <input v-model="newForms[center.code].border_color" type="color" class="h-9 w-20 rounded border" />
                        </div>
                        <label class="flex items-center gap-2 text-sm">
                            <Checkbox v-model:checked="newForms[center.code].is_default" />
                            {{ t('admin.certificateTemplates.setDefault') }}
                        </label>
                        <div class="flex gap-2">
                            <Button type="submit" class="flex-1" :disabled="newForms[center.code].processing">{{
                                t('admin.certificateTemplates.saveNew')
                            }}</Button>
                            <Button type="button" variant="outline" @click="showNewForm[center.code] = false">{{
                                t('admin.certificateTemplates.cancel')
                            }}</Button>
                        </div>
                    </form>

                    <!-- Existing templates -->
                    <div v-for="template in templatesFor(center.code)" :key="template.id" class="space-y-3 rounded-xl border border-slate-200 p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="flex items-center gap-1 text-sm font-bold">
                                {{ template.name }}
                                <Star v-if="template.is_default" class="h-3.5 w-3.5 fill-amber-400 text-amber-400" />
                            </p>
                            <button
                                type="button"
                                class="rounded p-1 text-destructive hover:bg-muted"
                                :title="t('admin.certificateTemplates.deleteTitle')"
                                @click="remove(template)"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                            </button>
                        </div>

                        <form class="space-y-3" @submit.prevent="save(template.id)">
                            <div class="grid gap-1.5">
                                <Label :for="`title-${template.id}`">{{ t('admin.certificateTemplates.fieldTitle') }}</Label>
                                <Input :id="`title-${template.id}`" v-model="editForms[template.id].title" />
                            </div>

                            <div class="grid gap-1.5">
                                <Label :for="`subtitle-${template.id}`">{{ t('admin.certificateTemplates.fieldSubtitle') }}</Label>
                                <Textarea :id="`subtitle-${template.id}`" v-model="editForms[template.id].subtitle" rows="2" />
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="grid gap-1.5">
                                    <Label :for="`sig1n-${template.id}`">{{ t('admin.certificateTemplates.signatory1') }}</Label>
                                    <Input :id="`sig1n-${template.id}`" v-model="editForms[template.id].signatory1_name" />
                                    <Input
                                        v-model="editForms[template.id].signatory1_title"
                                        :placeholder="t('admin.certificateTemplates.positionPlaceholder')"
                                    />
                                </div>
                                <div class="grid gap-1.5">
                                    <Label :for="`sig2n-${template.id}`">{{ t('admin.certificateTemplates.signatory2') }}</Label>
                                    <Input :id="`sig2n-${template.id}`" v-model="editForms[template.id].signatory2_name" />
                                    <Input
                                        v-model="editForms[template.id].signatory2_title"
                                        :placeholder="t('admin.certificateTemplates.positionPlaceholder')"
                                    />
                                </div>
                            </div>

                            <div v-if="hasThirdSignatory(template.service_center_code)" class="grid gap-1.5">
                                <Label :for="`sig3n-${template.id}`">{{ t('admin.certificateTemplates.signatory3') }}</Label>
                                <Input :id="`sig3n-${template.id}`" v-model="editForms[template.id].signatory3_name" />
                                <Input
                                    v-model="editForms[template.id].signatory3_title"
                                    :placeholder="t('admin.certificateTemplates.positionPlaceholder')"
                                />
                            </div>

                            <div class="grid gap-1.5">
                                <Label :for="`color-${template.id}`">{{ t('admin.certificateTemplates.fieldBorderColor') }}</Label>
                                <input :id="`color-${template.id}`" v-model="editForms[template.id].border_color" type="color" class="h-9 w-20 rounded border" />
                            </div>

                            <label class="flex items-center gap-2 text-sm">
                                <Checkbox v-model:checked="editForms[template.id].is_default" />
                                {{ t('admin.certificateTemplates.setDefault') }}
                            </label>
                            <p v-if="editForms[template.id].errors.is_default" class="text-xs text-destructive">
                                {{ editForms[template.id].errors.is_default }}
                            </p>

                            <Button type="submit" class="w-full" :disabled="editForms[template.id].processing">{{
                                t('admin.certificateTemplates.save')
                            }}</Button>
                        </form>
                    </div>
                </div>
            </div>
    </div>
</template>
