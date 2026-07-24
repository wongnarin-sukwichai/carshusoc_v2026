<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Mail } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AdminLayout });

interface EmailTemplateRow {
    id: number;
    key: string;
    subject: string;
    body: string;
}

const props = defineProps<{
    templates: EmailTemplateRow[];
}>();

const { t } = useI18n();

const keyLabels = computed<Record<string, string>>(() => ({
    welcome: t('admin.emailTemplates.keys.welcome'),
    payment_approved: t('admin.emailTemplates.keys.payment_approved'),
    score_released: t('admin.emailTemplates.keys.score_released'),
    translation_quote_sent: t('admin.emailTemplates.keys.translation_quote_sent'),
    translation_delivered: t('admin.emailTemplates.keys.translation_delivered'),
    location_changed: t('admin.emailTemplates.keys.location_changed'),
}));

const placeholderHints: Record<string, string[]> = {
    welcome: ['{{name}}'],
    payment_approved: ['{{name}}', '{{item_name}}'],
    score_released: ['{{name}}', '{{exam_name}}', '{{score}}', '{{cefr_level}}'],
    translation_quote_sent: ['{{name}}', '{{price}}', '{{delivery_date}}'],
    translation_delivered: ['{{name}}', '{{file_name}}'],
    location_changed: ['{{name}}', '{{item_name}}', '{{old_location}}', '{{new_location}}'],
};

const editForms = Object.fromEntries(
    props.templates.map((template) => [
        template.id,
        useForm({
            subject: template.subject,
            body: template.body,
        }),
    ]),
);

const save = (templateId: number) => {
    editForms[templateId].put(route('admin.email-templates.update', templateId), { preserveScroll: true });
};
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <Head :title="t('nav.admin.emailTemplates')" />
            <HeadingSmall :title="t('admin.emailTemplates.title')" :description="t('admin.emailTemplates.description')" />

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div v-for="template in templates" :key="template.id" class="space-y-3 rounded-xl border border-slate-200 p-4 shadow-sm">
                    <div class="flex items-center gap-1.5 border-b pb-2">
                        <Mail class="h-4 w-4 text-indigo-600" />
                        <p class="text-sm font-bold text-foreground">{{ keyLabels[template.key] ?? template.key }}</p>
                    </div>

                    <form class="space-y-3" @submit.prevent="save(template.id)">
                        <div class="grid gap-1.5">
                            <Label :for="`subject-${template.id}`">{{ t('admin.emailTemplates.fieldSubject') }}</Label>
                            <Input :id="`subject-${template.id}`" v-model="editForms[template.id].subject" />
                            <p v-if="editForms[template.id].errors.subject" class="text-xs text-destructive">
                                {{ editForms[template.id].errors.subject }}
                            </p>
                        </div>

                        <div class="grid gap-1.5">
                            <Label :for="`body-${template.id}`">{{ t('admin.emailTemplates.fieldBody') }}</Label>
                            <Textarea :id="`body-${template.id}`" v-model="editForms[template.id].body" rows="5" />
                            <p v-if="editForms[template.id].errors.body" class="text-xs text-destructive">
                                {{ editForms[template.id].errors.body }}
                            </p>
                        </div>

                        <p class="text-[10px] text-muted-foreground">
                            {{ t('admin.emailTemplates.availableVariables') }}
                            <span class="font-mono">{{ placeholderHints[template.key]?.join(', ') ?? '-' }}</span>
                        </p>

                        <Button type="submit" class="w-full" :disabled="editForms[template.id].processing">{{ t('admin.emailTemplates.save') }}</Button>
                    </form>
                </div>
            </div>
    </div>
</template>
