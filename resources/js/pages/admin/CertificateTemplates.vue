<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { confirmDialog, errorToast } from '@/lib/swal';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Award, GraduationCap, ImageOff, Plus, Star, Trash2, X, Eye } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const SIGNATORY_NUMBERS = [1, 2, 3, 4] as const;

// A4 at 96 CSS px/inch — the same assumption dompdf uses. The preview
// iframe is rendered at this TRUE size, then scaled down with CSS transform
// to fit the dialog, so admins see actual A4 proportions (and a hard page
// edge, no scrollbar) instead of a smaller box where the same fixed-px
// fonts look proportionally larger than they really are on the printed
// page. Training certificates render landscape; exam certificates render
// portrait (see App\Services\CertificateIssuer) — the preview box has to
// match whichever orientation the template being previewed actually uses.
const A4_LONG_SIDE_PX = 1123;
const A4_SHORT_SIDE_PX = 794;
const PREVIEW_MAX_DISPLAY_WIDTH_PX = 680;
const PREVIEW_MAX_DISPLAY_HEIGHT_PX = 620;

interface CertificateTemplateRow {
    id: number;
    service_center_code: string;
    name: string;
    title: string;
    subtitle: string | null;
    signatory1_name: string;
    signatory1_title: string;
    signatory1_signature_path: string | null;
    signatory2_name: string | null;
    signatory2_title: string | null;
    signatory2_signature_path: string | null;
    signatory3_name: string | null;
    signatory3_title: string | null;
    signatory3_signature_path: string | null;
    signatory4_name: string | null;
    signatory4_title: string | null;
    signatory4_signature_path: string | null;
    background_image_path: string | null;
    border_color: string;
    is_default: boolean;
}

const props = defineProps<{
    templates: CertificateTemplateRow[];
}>();

const centers = computed(() => [
    { code: 'training', label: t('admin.certificateTemplates.centerTraining'), icon: GraduationCap, accent: 'blue' as const },
    { code: 'exam', label: t('admin.certificateTemplates.centerExam'), icon: Award, accent: 'violet' as const },
]);

const accentClasses = {
    blue: {
        card: 'border-blue-200 bg-blue-50/40',
        heading: 'text-blue-900',
        badge: 'bg-blue-100 text-blue-600',
        icon: 'text-blue-600',
    },
    violet: {
        card: 'border-violet-200 bg-violet-50/40',
        heading: 'text-violet-900',
        badge: 'bg-violet-100 text-violet-600',
        icon: 'text-violet-600',
    },
} as const;

const templatesFor = (code: string) => props.templates.filter((row) => row.service_center_code === code);

const storageUrl = (path: string | null) => (path ? `/storage/${path}` : null);

// previewUrls holds what each <img> tag actually shows — server-persisted
// path by default, swapped for a local blob: URL the instant a file is
// chosen so the admin sees the picture before ever hitting "save".
const previewUrls = reactive<Record<string, string | null>>({});

const previewKey = (owner: string, field: string) => `${owner}-${field}`;

const initPreview = (owner: string, field: string, path: string | null) => {
    const key = previewKey(owner, field);
    if (!(key in previewUrls)) previewUrls[key] = storageUrl(path);
};

props.templates.forEach((template) => {
    const owner = `tpl-${template.id}`;
    initPreview(owner, 'background', template.background_image_path);
    SIGNATORY_NUMBERS.forEach((n) =>
        initPreview(owner, `sig${n}`, (template as unknown as Record<string, string | null>)[`signatory${n}_signature_path`]),
    );
});

type UploadForm = Record<string, unknown>;

const onFileChange = (owner: string, form: UploadForm, field: string, removeField: string, previewField: string, event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    form[field] = file;
    form[removeField] = false;
    previewUrls[previewKey(owner, previewField)] = file ? URL.createObjectURL(file) : null;
};

const clearImage = (owner: string, form: UploadForm, field: string, removeField: string, previewField: string) => {
    form[field] = null;
    form[removeField] = true;
    previewUrls[previewKey(owner, previewField)] = null;
};

// ---------- edit existing templates ----------
const expandedId = ref<number | null>(null);

const toggleExpand = (id: number) => {
    expandedId.value = expandedId.value === id ? null : id;
};

const expandedTemplateFor = (code: string) => templatesFor(code).find((row) => row.id === expandedId.value) ?? null;

const editForms = Object.fromEntries(
    props.templates.map((template) => [
        template.id,
        useForm({
            name: template.name,
            title: template.title,
            subtitle: template.subtitle ?? '',
            signatory1_name: template.signatory1_name,
            signatory1_title: template.signatory1_title,
            signatory1_signature: null as File | null,
            remove_signatory1_signature: false,
            signatory2_name: template.signatory2_name ?? '',
            signatory2_title: template.signatory2_title ?? '',
            signatory2_signature: null as File | null,
            remove_signatory2_signature: false,
            signatory3_name: template.signatory3_name ?? '',
            signatory3_title: template.signatory3_title ?? '',
            signatory3_signature: null as File | null,
            remove_signatory3_signature: false,
            signatory4_name: template.signatory4_name ?? '',
            signatory4_title: template.signatory4_title ?? '',
            signatory4_signature: null as File | null,
            remove_signatory4_signature: false,
            background_image: null as File | null,
            remove_background_image: false,
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

// ---------- preview dialog ----------
// Only reachable via "ดูตัวอย่างก่อนบันทึก" in the edit form now — the table's
// old click-the-thumbnail/eye-icon entry point for the *saved* version was
// removed since this draft preview is a strict superset of it: opening it
// without changing anything in the form renders exactly what's already
// saved (see Admin\CertificateTemplateController::previewDraft()), so having
// both was just two ways to see the same thing.
const previewTemplate = ref<CertificateTemplateRow | null>(null);
const draftPreviewHtml = ref<string | null>(null);
const draftPreviewLoading = ref(false);

// Training renders landscape, exam renders portrait (see
// App\Services\CertificateIssuer) — fit whichever orientation the
// previewed template actually uses into a bounding box, scaling down
// uniformly so the proportions shown always match the real PDF.
const previewPageDims = computed(() => {
    const isPortrait = previewTemplate.value?.service_center_code === 'exam';
    const pageWidth = isPortrait ? A4_SHORT_SIDE_PX : A4_LONG_SIDE_PX;
    const pageHeight = isPortrait ? A4_LONG_SIDE_PX : A4_SHORT_SIDE_PX;
    const scale = Math.min(PREVIEW_MAX_DISPLAY_WIDTH_PX / pageWidth, PREVIEW_MAX_DISPLAY_HEIGHT_PX / pageHeight);

    return {
        pageWidth,
        pageHeight,
        scale,
        displayWidth: Math.round(pageWidth * scale),
        displayHeight: Math.round(pageHeight * scale),
    };
});

const closePreview = () => {
    previewTemplate.value = null;
    draftPreviewHtml.value = null;
};

const readCookie = (name: string): string | null => {
    const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : null;
};

// Serializes the *current* in-memory form state (typed text + any chosen
// but unsaved files) so the draft-preview endpoint can render exactly what
// "บันทึก" would produce, without the admin having to save first.
const buildDraftFormData = (form: UploadForm): FormData => {
    const data = new FormData();

    (['title', 'subtitle', 'border_color', 'signatory1_name', 'signatory1_title', 'signatory2_name', 'signatory2_title', 'signatory3_name', 'signatory3_title', 'signatory4_name', 'signatory4_title'] as const).forEach((field) => {
        const value = form[field];
        if (value !== null && value !== undefined) data.append(field, String(value));
    });

    (['background_image', 'signatory1_signature', 'signatory2_signature', 'signatory3_signature', 'signatory4_signature'] as const).forEach((field) => {
        if (form[field] instanceof File) data.append(field, form[field] as File);
    });

    (['remove_background_image', 'remove_signatory1_signature', 'remove_signatory2_signature', 'remove_signatory3_signature', 'remove_signatory4_signature'] as const).forEach((field) => {
        data.append(field, form[field] ? '1' : '0');
    });

    return data;
};

const openDraftPreview = async (template: CertificateTemplateRow) => {
    previewTemplate.value = template;
    draftPreviewLoading.value = true;
    draftPreviewHtml.value = null;

    try {
        const response = await fetch(route('admin.certificate-templates.preview-draft', { certificateTemplate: template.id }), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...(readCookie('XSRF-TOKEN') ? { 'X-XSRF-TOKEN': readCookie('XSRF-TOKEN')! } : {}),
            },
            body: buildDraftFormData(editForms[template.id] as unknown as UploadForm),
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        draftPreviewHtml.value = await response.text();
    } catch {
        errorToast(t('admin.certificateTemplates.draftPreviewFailed'));
        previewTemplate.value = null;
    } finally {
        draftPreviewLoading.value = false;
    }
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
    signatory1_signature: null as File | null,
    signatory2_name: '',
    signatory2_title: '',
    signatory2_signature: null as File | null,
    signatory3_name: '',
    signatory3_title: '',
    signatory3_signature: null as File | null,
    signatory4_name: '',
    signatory4_title: '',
    signatory4_signature: null as File | null,
    background_image: null as File | null,
    border_color: '#4f46e5',
    is_default: false,
});

const newForms = Object.fromEntries(centers.value.map((c) => [c.code, useForm(emptyTemplate(c.code))]));

const createTemplate = (code: string) => {
    newForms[code].post(route('admin.certificate-templates.store'), {
        preserveScroll: true,
        onSuccess: () => {
            newForms[code].reset();
            SIGNATORY_NUMBERS.forEach((n) => (previewUrls[previewKey(`new-${code}`, `sig${n}`)] = null));
            previewUrls[previewKey(`new-${code}`, 'background')] = null;
            showNewForm[code] = false;
        },
    });
};
</script>

<template>
    <div class="flex flex-col flex-1 h-full gap-4 p-4 rounded-xl">
        <Head :title="t('nav.admin.certificateTemplates')" />
        <HeadingSmall :title="t('admin.certificateTemplates.title')" :description="t('admin.certificateTemplates.description')" />

        <Tabs default-value="training" class="w-full">
            <TabsList>
                <TabsTrigger v-for="center in centers" :key="center.code" :value="center.code" class="gap-1.5">
                    <component :is="center.icon" class="w-4 h-4" :class="accentClasses[center.accent].icon" />
                    {{ center.label }}
                </TabsTrigger>
            </TabsList>

            <TabsContent v-for="center in centers" :key="center.code" :value="center.code" class="space-y-4">
                <div :class="['space-y-4 rounded-xl border p-4 shadow-sm', accentClasses[center.accent].card]">
                    <div class="flex items-center justify-between">
                        <h3 :class="['flex items-center gap-2 text-sm font-bold', accentClasses[center.accent].heading]">
                            <span :class="['flex h-7 w-7 items-center justify-center rounded-full', accentClasses[center.accent].badge]">
                                <component :is="center.icon" class="w-4 h-4" />
                            </span>
                            {{ center.label }}
                        </h3>
                        <Button size="sm" variant="outline" @click="showNewForm[center.code] = !showNewForm[center.code]">
                            <Plus class="mr-1 h-3.5 w-3.5" />{{ t('admin.certificateTemplates.addNew') }}
                        </Button>
                    </div>

                    <!-- New template form -->
                    <Transition name="fade">
                    <form
                        v-if="showNewForm[center.code]"
                        class="p-4 space-y-3 bg-white border-2 border-dashed rounded-xl"
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

                        <div class="grid gap-1.5">
                            <Label>{{ t('admin.certificateTemplates.fieldBackgroundImage') }}</Label>
                            <p class="text-[10px] text-muted-foreground">{{ t('admin.certificateTemplates.backgroundImageHint') }}</p>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-24 h-16 overflow-hidden border rounded bg-slate-50">
                                    <img
                                        v-if="previewUrls[previewKey(`new-${center.code}`, 'background')]"
                                        :src="previewUrls[previewKey(`new-${center.code}`, 'background')]!"
                                        class="object-cover w-full h-full"
                                    />
                                    <ImageOff v-else class="w-5 h-5 text-slate-300" />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <input
                                        type="file"
                                        accept="image/*"
                                        class="text-[10px]"
                                        @change="
                                            onFileChange(
                                                `new-${center.code}`,
                                                newForms[center.code],
                                                'background_image',
                                                'remove_background_image',
                                                'background',
                                                $event,
                                            )
                                        "
                                    />
                                    <button
                                        v-if="previewUrls[previewKey(`new-${center.code}`, 'background')]"
                                        type="button"
                                        class="w-fit text-[10px] text-destructive hover:underline"
                                        @click="
                                            clearImage(
                                                `new-${center.code}`,
                                                newForms[center.code],
                                                'background_image',
                                                'remove_background_image',
                                                'background',
                                            )
                                        "
                                    >
                                        {{ t('admin.certificateTemplates.removeImage') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div v-for="n in SIGNATORY_NUMBERS" :key="n" class="grid gap-1.5">
                                <Label>{{ t(`admin.certificateTemplates.signatory${n}`) }}</Label>
                                <Input v-model="(newForms[center.code] as any)[`signatory${n}_name`]" />
                                <Input
                                    v-model="(newForms[center.code] as any)[`signatory${n}_title`]"
                                    :placeholder="t('admin.certificateTemplates.positionPlaceholder')"
                                />
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center justify-center w-16 h-10 overflow-hidden border rounded shrink-0 bg-slate-50">
                                        <img
                                            v-if="previewUrls[previewKey(`new-${center.code}`, `sig${n}`)]"
                                            :src="previewUrls[previewKey(`new-${center.code}`, `sig${n}`)]!"
                                            class="object-contain w-full h-full"
                                        />
                                        <ImageOff v-else class="h-3.5 w-3.5 text-slate-300" />
                                    </div>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        class="min-w-0 flex-1 text-[10px]"
                                        @change="
                                            onFileChange(
                                                `new-${center.code}`,
                                                newForms[center.code],
                                                `signatory${n}_signature`,
                                                `remove_signatory${n}_signature`,
                                                `sig${n}`,
                                                $event,
                                            )
                                        "
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-1.5">
                            <Label>{{ t('admin.certificateTemplates.fieldBorderColor') }}</Label>
                            <input v-model="newForms[center.code].border_color" type="color" class="w-20 border rounded h-9" />
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
                    </Transition>

                    <!-- Template list table -->
                    <div class="overflow-x-auto bg-white border rounded-xl border-slate-200">
                        <table class="w-full text-xs text-left text-slate-700">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th class="w-20 p-2.5">{{ t('admin.certificateTemplates.colPreview') }}</th>
                                    <th class="p-2.5">{{ t('admin.certificateTemplates.colName') }}</th>
                                    <th class="p-2.5">{{ t('admin.certificateTemplates.colDefault') }}</th>
                                    <th class="p-2.5 text-right">{{ t('admin.certificateTemplates.colActions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="templatesFor(center.code).length === 0">
                                    <td colspan="4" class="p-4 text-center text-muted-foreground">{{ t('admin.certificateTemplates.empty') }}</td>
                                </tr>
                                <template v-for="template in templatesFor(center.code)" :key="template.id">
                                    <tr class="border-t cursor-pointer border-slate-200 hover:bg-slate-50" @click="toggleExpand(template.id)">
                                        <td class="p-2.5">
                                            <div class="flex items-center justify-center h-10 overflow-hidden border rounded w-14 bg-slate-50">
                                                <img
                                                    v-if="previewUrls[previewKey(`tpl-${template.id}`, 'background')]"
                                                    :src="previewUrls[previewKey(`tpl-${template.id}`, 'background')]!"
                                                    class="object-cover w-full h-full"
                                                />
                                                <ImageOff v-else class="w-4 h-4 text-slate-300" />
                                            </div>
                                        </td>
                                        <td class="p-2.5 font-semibold">{{ template.name }}</td>
                                        <td class="p-2.5">
                                            <Star v-if="template.is_default" class="h-3.5 w-3.5 fill-amber-400 text-amber-400" />
                                        </td>
                                        <td class="p-2.5 text-right" @click.stop>
                                            <div class="flex justify-end gap-1">
                                                <button
                                                    type="button"
                                                    class="p-1 rounded text-destructive hover:bg-muted"
                                                    :title="t('admin.certificateTemplates.deleteTitle')"
                                                    @click="remove(template)"
                                                >
                                                    <Trash2 class="h-3.5 w-3.5" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <p v-if="templatesFor(center.code).length > 0" class="text-[10px] text-muted-foreground">
                        {{ t('admin.certificateTemplates.tableHint') }}
                    </p>

                    <!-- Edit card — deliberately a separate card below the list, not
                         nested inside the table, so it reads as its own editing
                         surface rather than an expanding row.

                         The <Transition> wraps a v-if'd div directly (not a v-for
                         producing 0-or-1 items) — that distinction matters: Vue
                         only animates enter/leave when the *same* <Transition>
                         instance stays mounted across a v-if toggle. A v-for whose
                         array flips between [] and [item] recreates the whole
                         <Transition> component from scratch each time, so it never
                         gets a chance to animate. The inner v-for below is just a
                         convenience "let editing = …" binding, kept safely inside
                         the already-v-if'd div where it can't interfere. -->
                    <Transition name="fade">
                        <div
                            v-if="expandedTemplateFor(center.code)"
                            :key="expandedTemplateFor(center.code)!.id"
                            class="p-4 space-y-3 bg-white border shadow-sm rounded-xl border-slate-200"
                        >
                            <template v-for="editing in [expandedTemplateFor(center.code)!]" :key="editing.id">
                                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                    <h4 class="text-xs font-bold text-slate-700">{{ editing.name }}</h4>
                                    <button
                                        type="button"
                                        class="p-1 rounded text-muted-foreground hover:bg-muted"
                                        :title="t('admin.certificateTemplates.cancel')"
                                        @click="expandedId = null"
                                    >
                                        <X class="w-4 h-4" />
                                    </button>
                                </div>

                                <form class="space-y-3" @submit.prevent="save(editing.id)">
                                    <div class="grid gap-1.5">
                                        <Label :for="`title-${editing.id}`">{{ t('admin.certificateTemplates.fieldTitle') }}</Label>
                                        <Input :id="`title-${editing.id}`" v-model="editForms[editing.id].title" />
                                    </div>

                                    <div class="grid gap-1.5">
                                        <Label :for="`subtitle-${editing.id}`">{{ t('admin.certificateTemplates.fieldSubtitle') }}</Label>
                                        <Textarea :id="`subtitle-${editing.id}`" v-model="editForms[editing.id].subtitle" rows="2" />
                                    </div>

                                    <div class="grid gap-1.5">
                                        <Label>{{ t('admin.certificateTemplates.fieldBackgroundImage') }}</Label>
                                        <p class="text-[10px] text-muted-foreground">
                                            {{ t('admin.certificateTemplates.backgroundImageHint') }}
                                        </p>
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center justify-center w-24 h-16 overflow-hidden border rounded bg-slate-50">
                                                <img
                                                    v-if="previewUrls[previewKey(`tpl-${editing.id}`, 'background')]"
                                                    :src="previewUrls[previewKey(`tpl-${editing.id}`, 'background')]!"
                                                    class="object-cover w-full h-full"
                                                />
                                                <ImageOff v-else class="w-5 h-5 text-slate-300" />
                                            </div>
                                            <div class="flex flex-col gap-1">
                                                <input
                                                    type="file"
                                                    accept="image/*"
                                                    class="text-[10px]"
                                                    @change="
                                                        onFileChange(
                                                            `tpl-${editing.id}`,
                                                            editForms[editing.id],
                                                            'background_image',
                                                            'remove_background_image',
                                                            'background',
                                                            $event,
                                                        )
                                                    "
                                                />
                                                <button
                                                    v-if="previewUrls[previewKey(`tpl-${editing.id}`, 'background')]"
                                                    type="button"
                                                    class="w-fit text-[10px] text-destructive hover:underline"
                                                    @click="
                                                        clearImage(
                                                            `tpl-${editing.id}`,
                                                            editForms[editing.id],
                                                            'background_image',
                                                            'remove_background_image',
                                                            'background',
                                                        )
                                                    "
                                                >
                                                    {{ t('admin.certificateTemplates.removeImage') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div v-for="n in SIGNATORY_NUMBERS" :key="n" class="grid gap-1.5">
                                            <Label>{{ t(`admin.certificateTemplates.signatory${n}`) }}</Label>
                                            <Input v-model="(editForms[editing.id] as any)[`signatory${n}_name`]" />
                                            <Input
                                                v-model="(editForms[editing.id] as any)[`signatory${n}_title`]"
                                                :placeholder="t('admin.certificateTemplates.positionPlaceholder')"
                                            />
                                            <div class="flex items-center gap-2">
                                                <div class="flex items-center justify-center w-16 h-10 overflow-hidden border rounded shrink-0 bg-slate-50">
                                                    <img
                                                        v-if="previewUrls[previewKey(`tpl-${editing.id}`, `sig${n}`)]"
                                                        :src="previewUrls[previewKey(`tpl-${editing.id}`, `sig${n}`)]!"
                                                        class="object-contain w-full h-full"
                                                    />
                                                    <ImageOff v-else class="h-3.5 w-3.5 text-slate-300" />
                                                </div>
                                                <input
                                                    type="file"
                                                    accept="image/*"
                                                    class="min-w-0 flex-1 text-[10px]"
                                                    @change="
                                                        onFileChange(
                                                            `tpl-${editing.id}`,
                                                            editForms[editing.id],
                                                            `signatory${n}_signature`,
                                                            `remove_signatory${n}_signature`,
                                                            `sig${n}`,
                                                            $event,
                                                        )
                                                    "
                                                />
                                                <button
                                                    v-if="previewUrls[previewKey(`tpl-${editing.id}`, `sig${n}`)]"
                                                    type="button"
                                                    class="p-1 rounded shrink-0 text-destructive hover:bg-muted"
                                                    :title="t('admin.certificateTemplates.removeImage')"
                                                    @click="
                                                        clearImage(
                                                            `tpl-${editing.id}`,
                                                            editForms[editing.id],
                                                            `signatory${n}_signature`,
                                                            `remove_signatory${n}_signature`,
                                                            `sig${n}`,
                                                        )
                                                    "
                                                >
                                                    <X class="h-3.5 w-3.5" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid gap-1.5">
                                        <Label :for="`color-${editing.id}`">{{ t('admin.certificateTemplates.fieldBorderColor') }}</Label>
                                        <input
                                            :id="`color-${editing.id}`"
                                            v-model="editForms[editing.id].border_color"
                                            type="color"
                                            class="w-20 border rounded h-9"
                                        />
                                    </div>

                                    <label class="flex items-center gap-2 text-sm">
                                        <Checkbox v-model:checked="editForms[editing.id].is_default" />
                                        {{ t('admin.certificateTemplates.setDefault') }}
                                    </label>
                                    <p v-if="editForms[editing.id].errors.is_default" class="text-xs text-destructive">
                                        {{ editForms[editing.id].errors.is_default }}
                                    </p>

                                    <div class="flex gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            class="flex-1"
                                            :disabled="draftPreviewLoading"
                                            @click="openDraftPreview(editing)"
                                        >
                                            <Eye class="mr-1 h-3.5 w-3.5" />{{ t('admin.certificateTemplates.previewDraftAction') }}
                                        </Button>
                                        <Button type="submit" class="flex-1" :disabled="editForms[editing.id].processing">{{
                                            t('admin.certificateTemplates.save')
                                        }}</Button>
                                    </div>
                                </form>
                            </template>
                        </div>
                    </Transition>
                </div>
            </TabsContent>
        </Tabs>

        <Dialog :open="!!previewTemplate" @update:open="(open) => !open && closePreview()">
            <DialogContent class="max-w-3xl">
                <DialogHeader>
                    <DialogTitle>{{ previewTemplate?.name }}</DialogTitle>
                </DialogHeader>

                <div
                    class="mx-auto overflow-hidden border rounded bg-slate-100"
                    :style="{ width: `${previewPageDims.displayWidth}px`, height: `${previewPageDims.displayHeight}px` }"
                >
                    <div v-if="draftPreviewLoading" class="flex items-center justify-center h-full text-sm text-muted-foreground">
                        {{ t('common.loading') }}
                    </div>
                    <iframe
                        v-else-if="previewTemplate && draftPreviewHtml"
                        :key="`draft-${previewTemplate.id}`"
                        :srcdoc="draftPreviewHtml"
                        scrolling="no"
                        :style="{
                            width: `${previewPageDims.pageWidth}px`,
                            height: `${previewPageDims.pageHeight}px`,
                            border: 0,
                            transform: `scale(${previewPageDims.scale})`,
                            transformOrigin: 'top left',
                        }"
                        :title="previewTemplate.name"
                    />
                </div>
                <p class="text-center text-[10px] text-amber-600">{{ t('admin.certificateTemplates.draftPreviewHint') }}</p>
                <p class="text-center text-[10px] text-muted-foreground">{{ t('admin.certificateTemplates.mockPreviewHint') }}</p>
            </DialogContent>
        </Dialog>
    </div>
</template>
