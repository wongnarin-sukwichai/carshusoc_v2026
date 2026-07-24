<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, Pencil } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AdminLayout });

interface CertificateTemplateOption {
    id: number;
    service_center_code: string;
    name: string;
}

interface ScoreScaleOption {
    id: number;
    name: string;
    version: number;
}

interface CourseRow {
    id: number;
    code: string;
    name_th: string;
    name_en: string;
    language: string;
    level: number;
    price: string;
    prerequisite_course_id: number | null;
    prerequisite: { id: number; name_th: string } | null;
    location: string | null;
    requires_receipt: boolean;
    is_visible: boolean;
    certificate_template_id: number | null;
}

interface ExamRow {
    id: number;
    code: string;
    type: string;
    name_th: string;
    name_en: string;
    price: string;
    exam_date: string;
    location: string | null;
    requires_receipt: boolean;
    mail_delivery_available: boolean;
    mail_delivery_fee: string | null;
    is_visible: boolean;
    certificate_template_id: number | null;
    score_scale_id: number | null;
}

const props = defineProps<{
    courses: CourseRow[];
    exams: ExamRow[];
    certificateTemplates: CertificateTemplateOption[];
    scoreScales: ScoreScaleOption[];
}>();

const { t } = useI18n();

const trainingTemplates = () => props.certificateTemplates.filter((t) => t.service_center_code === 'training');
const examTemplates = () => props.certificateTemplates.filter((t) => t.service_center_code === 'exam');

// ---------- Courses ----------
const editingCourseId = ref<number | null>(null);

const emptyCourse = {
    code: '',
    name_th: '',
    name_en: '',
    language: 'อังกฤษ',
    level: 1,
    price: 1000,
    prerequisite_course_id: null as number | null,
    location: '',
    requires_receipt: false,
    is_visible: true,
    certificate_template_id: null as number | null,
};

const courseForm = useForm({ ...emptyCourse });

const editCourse = (course: CourseRow) => {
    editingCourseId.value = course.id;
    courseForm.code = course.code;
    courseForm.name_th = course.name_th;
    courseForm.name_en = course.name_en;
    courseForm.language = course.language;
    courseForm.level = course.level;
    courseForm.price = Number(course.price);
    courseForm.prerequisite_course_id = course.prerequisite_course_id;
    courseForm.location = course.location ?? '';
    courseForm.requires_receipt = course.requires_receipt;
    courseForm.is_visible = course.is_visible;
    courseForm.certificate_template_id = course.certificate_template_id;
};

const resetCourseForm = () => {
    editingCourseId.value = null;
    courseForm.reset();
    courseForm.clearErrors();
};

const submitCourse = () => {
    if (editingCourseId.value) {
        courseForm.put(route('admin.courses.update', editingCourseId.value), {
            preserveScroll: true,
            onSuccess: () => resetCourseForm(),
        });
    } else {
        courseForm.post(route('admin.courses.store'), {
            preserveScroll: true,
            onSuccess: () => resetCourseForm(),
        });
    }
};

const toggleCourseVisibility = (course: CourseRow) => {
    router.patch(route('admin.courses.toggle-visibility', course.id), {}, { preserveScroll: true });
};

// ---------- Exams ----------
const editingExamId = ref<number | null>(null);

const emptyExam = {
    code: '',
    type: 'EPT',
    name_th: '',
    name_en: '',
    price: 800,
    exam_date: '',
    location: '',
    requires_receipt: false,
    mail_delivery_available: false,
    mail_delivery_fee: null as number | null,
    is_visible: true,
    certificate_template_id: null as number | null,
    score_scale_id: null as number | null,
};

const examForm = useForm({ ...emptyExam });

const editExam = (exam: ExamRow) => {
    editingExamId.value = exam.id;
    examForm.code = exam.code;
    examForm.type = exam.type;
    examForm.name_th = exam.name_th;
    examForm.name_en = exam.name_en;
    examForm.price = Number(exam.price);
    examForm.exam_date = exam.exam_date;
    examForm.location = exam.location ?? '';
    examForm.requires_receipt = exam.requires_receipt;
    examForm.mail_delivery_available = exam.mail_delivery_available;
    examForm.mail_delivery_fee = exam.mail_delivery_fee ? Number(exam.mail_delivery_fee) : null;
    examForm.is_visible = exam.is_visible;
    examForm.certificate_template_id = exam.certificate_template_id;
    examForm.score_scale_id = exam.score_scale_id;
};

const resetExamForm = () => {
    editingExamId.value = null;
    examForm.reset();
    examForm.clearErrors();
};

const submitExam = () => {
    if (editingExamId.value) {
        examForm.put(route('admin.exams.update', editingExamId.value), {
            preserveScroll: true,
            onSuccess: () => resetExamForm(),
        });
    } else {
        examForm.post(route('admin.exams.store'), {
            preserveScroll: true,
            onSuccess: () => resetExamForm(),
        });
    }
};

const toggleExamVisibility = (exam: ExamRow) => {
    router.patch(route('admin.exams.toggle-visibility', exam.id), {}, { preserveScroll: true });
};
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <Head :title="t('nav.admin.coursesExams')" />
            <HeadingSmall :title="t('admin.coursesExams.title')" :description="t('admin.coursesExams.description')" />

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Courses -->
                <div class="space-y-4 rounded-xl border border-slate-200 p-4 shadow-sm">
                    <h3 class="text-sm font-bold">
                        {{ editingCourseId ? t('admin.coursesExams.course.editTitle', { id: editingCourseId }) : t('admin.coursesExams.course.newTitle') }}
                    </h3>

                    <form class="space-y-3" @submit.prevent="submitCourse">
                        <div class="grid grid-cols-2 gap-2">
                            <div class="grid gap-1">
                                <Label for="course-code">{{ t('admin.coursesExams.fieldCode') }}</Label>
                                <Input id="course-code" v-model="courseForm.code" :placeholder="t('admin.coursesExams.course.codePlaceholder')" />
                                <p v-if="courseForm.errors.code" class="text-xs text-destructive">{{ courseForm.errors.code }}</p>
                            </div>
                            <div class="grid gap-1">
                                <Label for="course-language">{{ t('admin.coursesExams.fieldLanguage') }}</Label>
                                <select id="course-language" v-model="courseForm.language" class="h-10 rounded-md border bg-background px-3 text-sm">
                                    <option value="อังกฤษ">อังกฤษ</option>
                                    <option value="ไทย">ไทย</option>
                                    <option value="ลาว">ลาว</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-1">
                            <Label for="course-name-th">{{ t('admin.coursesExams.fieldNameTh') }}</Label>
                            <Input id="course-name-th" v-model="courseForm.name_th" />
                            <p v-if="courseForm.errors.name_th" class="text-xs text-destructive">{{ courseForm.errors.name_th }}</p>
                        </div>

                        <div class="grid gap-1">
                            <Label for="course-name-en">{{ t('admin.coursesExams.fieldNameEn') }}</Label>
                            <Input id="course-name-en" v-model="courseForm.name_en" />
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div class="grid gap-1">
                                <Label for="course-level">{{ t('admin.coursesExams.fieldLevel') }}</Label>
                                <Input id="course-level" v-model.number="courseForm.level" type="number" min="1" />
                            </div>
                            <div class="grid gap-1">
                                <Label for="course-price">{{ t('admin.coursesExams.fieldPrice') }}</Label>
                                <Input id="course-price" v-model.number="courseForm.price" type="number" min="0" />
                            </div>
                        </div>

                        <div class="grid gap-1">
                            <Label for="course-prerequisite">{{ t('admin.coursesExams.fieldPrerequisite') }}</Label>
                            <select id="course-prerequisite" v-model="courseForm.prerequisite_course_id" class="h-10 rounded-md border bg-background px-3 text-sm">
                                <option :value="null">{{ t('admin.coursesExams.prerequisiteNone') }}</option>
                                <option v-for="course in courses.filter((c) => c.id !== editingCourseId)" :key="course.id" :value="course.id">
                                    {{ course.code }}: {{ course.name_th }}
                                </option>
                            </select>
                        </div>

                        <div class="grid gap-1">
                            <Label for="course-location">{{ t('admin.coursesExams.fieldLocation') }}</Label>
                            <Input id="course-location" v-model="courseForm.location" :placeholder="t('admin.coursesExams.course.locationPlaceholder')" />
                        </div>

                        <div class="grid gap-1">
                            <Label for="course-cert-template">{{ t('admin.coursesExams.fieldCertTemplate') }}</Label>
                            <select
                                id="course-cert-template"
                                v-model="courseForm.certificate_template_id"
                                class="h-10 rounded-md border bg-background px-3 text-sm"
                            >
                                <option :value="null">{{ t('admin.coursesExams.certTemplateDefault') }}</option>
                                <option v-for="tpl in trainingTemplates()" :key="tpl.id" :value="tpl.id">{{ tpl.name }}</option>
                            </select>
                        </div>

                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center gap-2 text-sm">
                                <Checkbox v-model:checked="courseForm.requires_receipt" />
                                {{ t('admin.coursesExams.requiresReceipt') }}
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <Checkbox v-model:checked="courseForm.is_visible" />
                                {{ t('admin.coursesExams.isVisible') }}
                            </label>
                        </div>

                        <div class="flex gap-2">
                            <Button type="submit" class="flex-1" :disabled="courseForm.processing">
                                {{ editingCourseId ? t('admin.coursesExams.course.save') : t('admin.coursesExams.course.add') }}
                            </Button>
                            <Button v-if="editingCourseId" type="button" variant="outline" @click="resetCourseForm">{{
                                t('admin.coursesExams.cancel')
                            }}</Button>
                        </div>
                    </form>

                    <div class="space-y-1.5 rounded-lg border border-slate-200 bg-slate-50 p-2">
                        <div v-for="course in courses" :key="course.id" class="flex items-center justify-between rounded p-1.5 text-xs">
                            <div :class="{ 'text-muted-foreground line-through': !course.is_visible }">
                                <span class="font-mono font-bold">{{ course.code }}</span>: {{ course.name_th }}
                                <span v-if="course.prerequisite" class="text-muted-foreground">{{
                                    t('admin.coursesExams.prerequisiteNote', { name: course.prerequisite.name_th })
                                }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    class="rounded p-1 hover:bg-muted"
                                    :title="course.is_visible ? t('admin.coursesExams.hide') : t('admin.coursesExams.show')"
                                    @click="toggleCourseVisibility(course)"
                                >
                                    <EyeOff v-if="course.is_visible" class="h-3.5 w-3.5" />
                                    <Eye v-else class="h-3.5 w-3.5 text-red-500" />
                                </button>
                                <button type="button" class="rounded p-1 text-amber-600 hover:bg-muted" @click="editCourse(course)">
                                    <Pencil class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exams -->
                <div class="space-y-4 rounded-xl border border-slate-200 p-4 shadow-sm">
                    <h3 class="text-sm font-bold">
                        {{ editingExamId ? t('admin.coursesExams.exam.editTitle', { id: editingExamId }) : t('admin.coursesExams.exam.newTitle') }}
                    </h3>

                    <form class="space-y-3" @submit.prevent="submitExam">
                        <div class="grid grid-cols-2 gap-2">
                            <div class="grid gap-1">
                                <Label for="exam-code">{{ t('admin.coursesExams.fieldCode') }}</Label>
                                <Input id="exam-code" v-model="examForm.code" :placeholder="t('admin.coursesExams.exam.codePlaceholder')" />
                                <p v-if="examForm.errors.code" class="text-xs text-destructive">{{ examForm.errors.code }}</p>
                            </div>
                            <div class="grid gap-1">
                                <Label for="exam-type">{{ t('admin.coursesExams.exam.type') }}</Label>
                                <select id="exam-type" v-model="examForm.type" class="h-10 rounded-md border bg-background px-3 text-sm">
                                    <option value="EPT">EPT</option>
                                    <option value="TOEIC">TOEIC</option>
                                    <option value="IELTS">IELTS</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-1">
                            <Label for="exam-name-th">{{ t('admin.coursesExams.fieldNameTh') }}</Label>
                            <Input id="exam-name-th" v-model="examForm.name_th" />
                            <p v-if="examForm.errors.name_th" class="text-xs text-destructive">{{ examForm.errors.name_th }}</p>
                        </div>

                        <div class="grid gap-1">
                            <Label for="exam-name-en">{{ t('admin.coursesExams.fieldNameEn') }}</Label>
                            <Input id="exam-name-en" v-model="examForm.name_en" />
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div class="grid gap-1">
                                <Label for="exam-price">{{ t('admin.coursesExams.exam.price') }}</Label>
                                <Input id="exam-price" v-model.number="examForm.price" type="number" min="0" />
                            </div>
                            <div class="grid gap-1">
                                <Label for="exam-date">{{ t('admin.coursesExams.exam.date') }}</Label>
                                <Input id="exam-date" v-model="examForm.exam_date" type="date" />
                                <p v-if="examForm.errors.exam_date" class="text-xs text-destructive">{{ examForm.errors.exam_date }}</p>
                            </div>
                        </div>

                        <div class="grid gap-1">
                            <Label for="exam-location">{{ t('admin.coursesExams.fieldLocation') }}</Label>
                            <Input id="exam-location" v-model="examForm.location" :placeholder="t('admin.coursesExams.exam.locationPlaceholder')" />
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div class="grid gap-1">
                                <Label for="exam-cert-template">{{ t('admin.coursesExams.fieldCertTemplate') }}</Label>
                                <select
                                    id="exam-cert-template"
                                    v-model="examForm.certificate_template_id"
                                    class="h-10 rounded-md border bg-background px-3 text-sm"
                                >
                                    <option :value="null">{{ t('admin.coursesExams.certTemplateDefault') }}</option>
                                    <option v-for="tpl in examTemplates()" :key="tpl.id" :value="tpl.id">{{ tpl.name }}</option>
                                </select>
                            </div>
                            <div class="grid gap-1">
                                <Label for="exam-score-scale">{{ t('admin.coursesExams.exam.scoreScale') }}</Label>
                                <select id="exam-score-scale" v-model="examForm.score_scale_id" class="h-10 rounded-md border bg-background px-3 text-sm">
                                    <option :value="null">{{ t('admin.coursesExams.exam.scoreScaleDefault') }}</option>
                                    <option v-for="scale in scoreScales" :key="scale.id" :value="scale.id">{{ scale.name }} (v{{ scale.version }})</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-end gap-4">
                            <label class="flex items-center gap-2 text-sm">
                                <Checkbox v-model:checked="examForm.requires_receipt" />
                                {{ t('admin.coursesExams.requiresReceipt') }}
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <Checkbox v-model:checked="examForm.is_visible" />
                                {{ t('admin.coursesExams.isVisible') }}
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <Checkbox v-model:checked="examForm.mail_delivery_available" />
                                {{ t('admin.coursesExams.exam.mailDeliveryAvailable') }}
                            </label>
                        </div>

                        <div v-if="examForm.mail_delivery_available" class="grid gap-1">
                            <Label for="exam-delivery-fee">{{ t('admin.coursesExams.exam.mailDeliveryFee') }}</Label>
                            <Input id="exam-delivery-fee" v-model.number="examForm.mail_delivery_fee" type="number" min="0" />
                        </div>

                        <div class="flex gap-2">
                            <Button type="submit" class="flex-1" :disabled="examForm.processing">
                                {{ editingExamId ? t('admin.coursesExams.exam.save') : t('admin.coursesExams.exam.add') }}
                            </Button>
                            <Button v-if="editingExamId" type="button" variant="outline" @click="resetExamForm">{{
                                t('admin.coursesExams.cancel')
                            }}</Button>
                        </div>
                    </form>

                    <div class="space-y-1.5 rounded-lg border border-slate-200 bg-slate-50 p-2">
                        <div v-for="exam in exams" :key="exam.id" class="flex items-center justify-between rounded p-1.5 text-xs">
                            <div :class="{ 'text-muted-foreground line-through': !exam.is_visible }">
                                <span class="font-mono font-bold">{{ exam.code }}</span>: {{ exam.name_th }}
                                <span class="text-muted-foreground">({{ exam.exam_date }})</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    class="rounded p-1 hover:bg-muted"
                                    :title="exam.is_visible ? t('admin.coursesExams.hide') : t('admin.coursesExams.show')"
                                    @click="toggleExamVisibility(exam)"
                                >
                                    <EyeOff v-if="exam.is_visible" class="h-3.5 w-3.5" />
                                    <Eye v-else class="h-3.5 w-3.5 text-red-500" />
                                </button>
                                <button type="button" class="rounded p-1 text-amber-600 hover:bg-muted" @click="editExam(exam)">
                                    <Pencil class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</template>
