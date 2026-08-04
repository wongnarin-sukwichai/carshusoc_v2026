<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { formatDate } from '@/lib/date';
import { Head, router, useForm } from '@inertiajs/vue3';
import { BookOpen, CalendarClock, CalendarRange, ClipboardList, Eye, EyeOff, Plus, X } from 'lucide-vue-next';
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
    start_date: string | null;
    end_date: string | null;
    registration_open_at: string | null;
    registration_close_at: string | null;
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
    registration_open_at: string | null;
    registration_close_at: string | null;
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

const trainingTemplates = () => props.certificateTemplates.filter((tpl) => tpl.service_center_code === 'training');
const examTemplates = () => props.certificateTemplates.filter((tpl) => tpl.service_center_code === 'exam');

// ---------- Courses ----------
const showNewCourseForm = ref(false);

const emptyCourse = {
    code: '',
    name_th: '',
    name_en: '',
    language: 'อังกฤษ',
    level: 1,
    price: 1000,
    prerequisite_course_id: null as number | null,
    location: '',
    start_date: '',
    end_date: '',
    registration_open_at: '',
    registration_close_at: '',
    requires_receipt: false,
    is_visible: true,
    certificate_template_id: null as number | null,
};

const newCourseForm = useForm({ ...emptyCourse });

const createCourse = () => {
    newCourseForm.post(route('admin.courses.store'), {
        preserveScroll: true,
        onSuccess: () => {
            newCourseForm.reset();
            showNewCourseForm.value = false;
        },
    });
};

const expandedCourseId = ref<number | null>(null);

const toggleExpandCourse = (id: number) => {
    expandedCourseId.value = expandedCourseId.value === id ? null : id;
};

const expandedCourse = () => props.courses.find((course) => course.id === expandedCourseId.value) ?? null;

const courseEditForms = Object.fromEntries(
    props.courses.map((course) => [
        course.id,
        useForm({
            code: course.code,
            name_th: course.name_th,
            name_en: course.name_en,
            language: course.language,
            level: course.level,
            price: Number(course.price),
            prerequisite_course_id: course.prerequisite_course_id,
            location: course.location ?? '',
            start_date: course.start_date ?? '',
            end_date: course.end_date ?? '',
            registration_open_at: course.registration_open_at ?? '',
            registration_close_at: course.registration_close_at ?? '',
            requires_receipt: course.requires_receipt,
            is_visible: course.is_visible,
            certificate_template_id: course.certificate_template_id,
        }),
    ]),
);

const saveCourse = (courseId: number) => {
    courseEditForms[courseId].put(route('admin.courses.update', courseId), { preserveScroll: true });
};

const toggleCourseVisibility = (course: CourseRow) => {
    router.patch(route('admin.courses.toggle-visibility', course.id), {}, { preserveScroll: true });
};

// ---------- Exams ----------
const showNewExamForm = ref(false);

const emptyExam = {
    code: '',
    type: 'EPT',
    name_th: '',
    name_en: '',
    price: 800,
    exam_date: '',
    registration_open_at: '',
    registration_close_at: '',
    location: '',
    requires_receipt: false,
    mail_delivery_available: false,
    mail_delivery_fee: null as number | null,
    is_visible: true,
    certificate_template_id: null as number | null,
    score_scale_id: null as number | null,
};

const newExamForm = useForm({ ...emptyExam });

const createExam = () => {
    newExamForm.post(route('admin.exams.store'), {
        preserveScroll: true,
        onSuccess: () => {
            newExamForm.reset();
            showNewExamForm.value = false;
        },
    });
};

const expandedExamId = ref<number | null>(null);

const toggleExpandExam = (id: number) => {
    expandedExamId.value = expandedExamId.value === id ? null : id;
};

const expandedExam = () => props.exams.find((exam) => exam.id === expandedExamId.value) ?? null;

const examEditForms = Object.fromEntries(
    props.exams.map((exam) => [
        exam.id,
        useForm({
            code: exam.code,
            type: exam.type,
            name_th: exam.name_th,
            name_en: exam.name_en,
            price: Number(exam.price),
            exam_date: exam.exam_date,
            registration_open_at: exam.registration_open_at ?? '',
            registration_close_at: exam.registration_close_at ?? '',
            location: exam.location ?? '',
            requires_receipt: exam.requires_receipt,
            mail_delivery_available: exam.mail_delivery_available,
            mail_delivery_fee: exam.mail_delivery_fee ? Number(exam.mail_delivery_fee) : null,
            is_visible: exam.is_visible,
            certificate_template_id: exam.certificate_template_id,
            score_scale_id: exam.score_scale_id,
        }),
    ]),
);

const saveExam = (examId: number) => {
    examEditForms[examId].put(route('admin.exams.update', examId), { preserveScroll: true });
};

const toggleExamVisibility = (exam: ExamRow) => {
    router.patch(route('admin.exams.toggle-visibility', exam.id), {}, { preserveScroll: true });
};
</script>

<template>
    <div class="flex flex-col flex-1 h-full gap-4 p-4 rounded-xl">
        <Head :title="t('nav.admin.coursesExams')" />

        <div class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200">
            <div class="pb-2 border-b border-slate-100">
                <h2 class="flex items-center gap-1.5 text-sm font-bold text-slate-800">
                    <BookOpen class="w-4 h-4 text-indigo-600" />
                    {{ t('admin.coursesExams.title') }}
                </h2>
                <p class="text-[10px] text-slate-500">{{ t('admin.coursesExams.description') }}</p>
            </div>

        <Tabs default-value="course" class="w-full mt-4">
            <TabsList>
                <TabsTrigger value="course" class="gap-1.5">
                    <BookOpen class="w-4 h-4 text-blue-600" />
                    {{ t('admin.coursesExams.course.newTitle') }}
                </TabsTrigger>
                <TabsTrigger value="exam" class="gap-1.5">
                    <ClipboardList class="w-4 h-4 text-violet-600" />
                    {{ t('admin.coursesExams.exam.newTitle') }}
                </TabsTrigger>
            </TabsList>

            <!-- Courses -->
            <TabsContent value="course" class="p-4 space-y-4 border border-blue-200 shadow-sm rounded-xl bg-blue-50/40">
                <div class="flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-xs font-bold text-blue-900">
                        <span class="flex items-center justify-center text-blue-600 bg-blue-100 rounded-full h-7 w-7">
                            <BookOpen class="w-4 h-4" />
                        </span>
                        {{ t('admin.coursesExams.course.newTitle') }}
                    </h3>
                    <Button size="sm" variant="outline" @click="showNewCourseForm = !showNewCourseForm">
                        <Plus class="mr-1 h-3.5 w-3.5" />{{ t('admin.coursesExams.course.newTitle') }}
                    </Button>
                </div>

                <!-- New course form -->
                <Transition name="fade">
                    <form v-if="showNewCourseForm" class="space-y-3 rounded-xl border-2 border-dashed bg-white p-4" @submit.prevent="createCourse">
                        <div class="grid grid-cols-2 gap-2">
                            <div class="grid gap-1">
                                <Label for="new-course-code">{{ t('admin.coursesExams.fieldCode') }}</Label>
                                <Input id="new-course-code" v-model="newCourseForm.code" :placeholder="t('admin.coursesExams.course.codePlaceholder')" />
                                <p v-if="newCourseForm.errors.code" class="text-xs text-destructive">{{ newCourseForm.errors.code }}</p>
                            </div>
                            <div class="grid gap-1">
                                <Label for="new-course-language">{{ t('admin.coursesExams.fieldLanguage') }}</Label>
                                <select id="new-course-language" v-model="newCourseForm.language" class="h-10 rounded-md border bg-background px-3 text-sm">
                                    <option value="อังกฤษ">อังกฤษ</option>
                                    <option value="ไทย">ไทย</option>
                                    <option value="ลาว">ลาว</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-1">
                            <Label for="new-course-name-th">{{ t('admin.coursesExams.fieldNameTh') }}</Label>
                            <Input id="new-course-name-th" v-model="newCourseForm.name_th" />
                            <p v-if="newCourseForm.errors.name_th" class="text-xs text-destructive">{{ newCourseForm.errors.name_th }}</p>
                        </div>

                        <div class="grid gap-1">
                            <Label for="new-course-name-en">{{ t('admin.coursesExams.fieldNameEn') }}</Label>
                            <Input id="new-course-name-en" v-model="newCourseForm.name_en" />
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div class="grid gap-1">
                                <Label for="new-course-level">{{ t('admin.coursesExams.fieldLevel') }}</Label>
                                <Input id="new-course-level" v-model.number="newCourseForm.level" type="number" min="1" />
                            </div>
                            <div class="grid gap-1">
                                <Label for="new-course-price">{{ t('admin.coursesExams.fieldPrice') }}</Label>
                                <Input id="new-course-price" v-model.number="newCourseForm.price" type="number" min="0" />
                            </div>
                        </div>

                        <div class="grid gap-1">
                            <Label for="new-course-prerequisite">{{ t('admin.coursesExams.fieldPrerequisite') }}</Label>
                            <select id="new-course-prerequisite" v-model="newCourseForm.prerequisite_course_id" class="h-10 rounded-md border bg-background px-3 text-sm">
                                <option :value="null">{{ t('admin.coursesExams.prerequisiteNone') }}</option>
                                <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.code }}: {{ course.name_th }}</option>
                            </select>
                        </div>

                        <div class="grid gap-1">
                            <Label for="new-course-location">{{ t('admin.coursesExams.fieldLocation') }}</Label>
                            <Input id="new-course-location" v-model="newCourseForm.location" :placeholder="t('admin.coursesExams.course.locationPlaceholder')" />
                        </div>

                        <div class="p-3 border rounded-lg border-sky-200 bg-sky-50">
                            <p class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-sky-700">
                                <CalendarRange class="h-3.5 w-3.5" />
                                {{ t('admin.coursesExams.trainingPeriodTitle') }}
                            </p>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="grid gap-1">
                                    <Label for="new-course-start-date">{{ t('admin.coursesExams.fieldStartDate') }}</Label>
                                    <Input id="new-course-start-date" v-model="newCourseForm.start_date" type="date" />
                                </div>
                                <div class="grid gap-1">
                                    <Label for="new-course-end-date">{{ t('admin.coursesExams.fieldEndDate') }}</Label>
                                    <Input id="new-course-end-date" v-model="newCourseForm.end_date" type="date" />
                                    <p v-if="newCourseForm.errors.end_date" class="text-xs text-destructive">{{ newCourseForm.errors.end_date }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 border rounded-lg border-amber-200 bg-amber-50">
                            <p class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-amber-700">
                                <CalendarClock class="h-3.5 w-3.5" />
                                {{ t('admin.coursesExams.registrationPeriodTitle') }}
                            </p>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="grid gap-1">
                                    <Label for="new-course-registration-open">{{ t('admin.coursesExams.fieldRegistrationOpenAt') }}</Label>
                                    <Input id="new-course-registration-open" v-model="newCourseForm.registration_open_at" type="date" />
                                    <p v-if="newCourseForm.errors.registration_open_at" class="text-xs text-destructive">
                                        {{ newCourseForm.errors.registration_open_at }}
                                    </p>
                                </div>
                                <div class="grid gap-1">
                                    <Label for="new-course-registration-close">{{ t('admin.coursesExams.fieldRegistrationCloseAt') }}</Label>
                                    <Input id="new-course-registration-close" v-model="newCourseForm.registration_close_at" type="date" />
                                    <p v-if="newCourseForm.errors.registration_close_at" class="text-xs text-destructive">
                                        {{ newCourseForm.errors.registration_close_at }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-1">
                            <Label for="new-course-cert-template">{{ t('admin.coursesExams.fieldCertTemplate') }}</Label>
                            <select id="new-course-cert-template" v-model="newCourseForm.certificate_template_id" class="h-10 rounded-md border bg-background px-3 text-sm">
                                <option :value="null">{{ t('admin.coursesExams.certTemplateDefault') }}</option>
                                <option v-for="tpl in trainingTemplates()" :key="tpl.id" :value="tpl.id">{{ tpl.name }}</option>
                            </select>
                        </div>

                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center gap-2 text-sm">
                                <Checkbox v-model:checked="newCourseForm.requires_receipt" />
                                {{ t('admin.coursesExams.requiresReceipt') }}
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <Checkbox v-model:checked="newCourseForm.is_visible" />
                                {{ t('admin.coursesExams.isVisible') }}
                            </label>
                        </div>

                        <div class="flex gap-2">
                            <Button type="submit" class="flex-1" :disabled="newCourseForm.processing">{{ t('admin.coursesExams.course.add') }}</Button>
                            <Button type="button" variant="outline" @click="showNewCourseForm = false">{{ t('admin.coursesExams.cancel') }}</Button>
                        </div>
                    </form>
                </Transition>

                <!-- Course list table -->
                <div class="overflow-x-auto bg-white border rounded-xl border-slate-200">
                    <table class="w-full text-xs text-left text-slate-700">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="p-2.5">{{ t('admin.coursesExams.colName') }}</th>
                                <th class="p-2.5">{{ t('admin.coursesExams.colPrerequisite') }}</th>
                                <th class="p-2.5">{{ t('admin.coursesExams.colVisible') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="courses.length === 0">
                                <td colspan="3" class="p-4 text-center text-muted-foreground">{{ t('admin.coursesExams.emptyCourses') }}</td>
                            </tr>
                            <tr
                                v-for="course in courses"
                                :key="course.id"
                                class="border-t cursor-pointer border-slate-200 hover:bg-slate-50"
                                @click="toggleExpandCourse(course.id)"
                            >
                                <td class="p-2.5 font-semibold" :class="{ 'text-muted-foreground line-through': !course.is_visible }">
                                    <span class="font-mono">{{ course.code }}</span>: {{ course.name_th }}
                                </td>
                                <td class="p-2.5 text-muted-foreground">{{ course.prerequisite?.name_th ?? '—' }}</td>
                                <td class="p-2.5" @click.stop>
                                    <button
                                        type="button"
                                        class="p-1 rounded hover:bg-muted"
                                        :title="course.is_visible ? t('admin.coursesExams.hide') : t('admin.coursesExams.show')"
                                        @click="toggleCourseVisibility(course)"
                                    >
                                        <Eye v-if="course.is_visible" class="h-3.5 w-3.5 text-emerald-600" />
                                        <EyeOff v-else class="h-3.5 w-3.5 text-slate-400" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-if="courses.length > 0" class="text-[10px] text-muted-foreground">{{ t('admin.coursesExams.tableHint') }}</p>

                <!-- Edit card -->
                <Transition name="fade">
                    <div v-if="expandedCourse()" :key="expandedCourse()!.id" class="p-4 space-y-3 bg-white border shadow-sm rounded-xl border-slate-200">
                        <template v-for="editing in [expandedCourse()!]" :key="editing.id">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                <h4 class="text-xs font-bold text-slate-700">{{ t('admin.coursesExams.course.editTitle', { id: editing.id }) }}</h4>
                                <button type="button" class="p-1 rounded text-muted-foreground hover:bg-muted" @click="expandedCourseId = null">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>

                            <form class="space-y-3" @submit.prevent="saveCourse(editing.id)">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="grid gap-1">
                                        <Label :for="`course-code-${editing.id}`">{{ t('admin.coursesExams.fieldCode') }}</Label>
                                        <Input :id="`course-code-${editing.id}`" v-model="courseEditForms[editing.id].code" />
                                        <p v-if="courseEditForms[editing.id].errors.code" class="text-xs text-destructive">
                                            {{ courseEditForms[editing.id].errors.code }}
                                        </p>
                                    </div>
                                    <div class="grid gap-1">
                                        <Label :for="`course-language-${editing.id}`">{{ t('admin.coursesExams.fieldLanguage') }}</Label>
                                        <select :id="`course-language-${editing.id}`" v-model="courseEditForms[editing.id].language" class="h-10 rounded-md border bg-background px-3 text-sm">
                                            <option value="อังกฤษ">อังกฤษ</option>
                                            <option value="ไทย">ไทย</option>
                                            <option value="ลาว">ลาว</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid gap-1">
                                    <Label :for="`course-name-th-${editing.id}`">{{ t('admin.coursesExams.fieldNameTh') }}</Label>
                                    <Input :id="`course-name-th-${editing.id}`" v-model="courseEditForms[editing.id].name_th" />
                                    <p v-if="courseEditForms[editing.id].errors.name_th" class="text-xs text-destructive">
                                        {{ courseEditForms[editing.id].errors.name_th }}
                                    </p>
                                </div>

                                <div class="grid gap-1">
                                    <Label :for="`course-name-en-${editing.id}`">{{ t('admin.coursesExams.fieldNameEn') }}</Label>
                                    <Input :id="`course-name-en-${editing.id}`" v-model="courseEditForms[editing.id].name_en" />
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div class="grid gap-1">
                                        <Label :for="`course-level-${editing.id}`">{{ t('admin.coursesExams.fieldLevel') }}</Label>
                                        <Input :id="`course-level-${editing.id}`" v-model.number="courseEditForms[editing.id].level" type="number" min="1" />
                                    </div>
                                    <div class="grid gap-1">
                                        <Label :for="`course-price-${editing.id}`">{{ t('admin.coursesExams.fieldPrice') }}</Label>
                                        <Input :id="`course-price-${editing.id}`" v-model.number="courseEditForms[editing.id].price" type="number" min="0" />
                                    </div>
                                </div>

                                <div class="grid gap-1">
                                    <Label :for="`course-prerequisite-${editing.id}`">{{ t('admin.coursesExams.fieldPrerequisite') }}</Label>
                                    <select :id="`course-prerequisite-${editing.id}`" v-model="courseEditForms[editing.id].prerequisite_course_id" class="h-10 rounded-md border bg-background px-3 text-sm">
                                        <option :value="null">{{ t('admin.coursesExams.prerequisiteNone') }}</option>
                                        <option v-for="course in courses.filter((c) => c.id !== editing.id)" :key="course.id" :value="course.id">
                                            {{ course.code }}: {{ course.name_th }}
                                        </option>
                                    </select>
                                </div>

                                <div class="grid gap-1">
                                    <Label :for="`course-location-${editing.id}`">{{ t('admin.coursesExams.fieldLocation') }}</Label>
                                    <Input :id="`course-location-${editing.id}`" v-model="courseEditForms[editing.id].location" />
                                </div>

                                <div class="p-3 border rounded-lg border-sky-200 bg-sky-50">
                                    <p class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-sky-700">
                                        <CalendarRange class="h-3.5 w-3.5" />
                                        {{ t('admin.coursesExams.trainingPeriodTitle') }}
                                    </p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="grid gap-1">
                                            <Label :for="`course-start-date-${editing.id}`">{{ t('admin.coursesExams.fieldStartDate') }}</Label>
                                            <Input :id="`course-start-date-${editing.id}`" v-model="courseEditForms[editing.id].start_date" type="date" />
                                        </div>
                                        <div class="grid gap-1">
                                            <Label :for="`course-end-date-${editing.id}`">{{ t('admin.coursesExams.fieldEndDate') }}</Label>
                                            <Input :id="`course-end-date-${editing.id}`" v-model="courseEditForms[editing.id].end_date" type="date" />
                                            <p v-if="courseEditForms[editing.id].errors.end_date" class="text-xs text-destructive">
                                                {{ courseEditForms[editing.id].errors.end_date }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-3 border rounded-lg border-amber-200 bg-amber-50">
                                    <p class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-amber-700">
                                        <CalendarClock class="h-3.5 w-3.5" />
                                        {{ t('admin.coursesExams.registrationPeriodTitle') }}
                                    </p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="grid gap-1">
                                            <Label :for="`course-registration-open-${editing.id}`">{{ t('admin.coursesExams.fieldRegistrationOpenAt') }}</Label>
                                            <Input
                                                :id="`course-registration-open-${editing.id}`"
                                                v-model="courseEditForms[editing.id].registration_open_at"
                                                type="date"
                                            />
                                            <p v-if="courseEditForms[editing.id].errors.registration_open_at" class="text-xs text-destructive">
                                                {{ courseEditForms[editing.id].errors.registration_open_at }}
                                            </p>
                                        </div>
                                        <div class="grid gap-1">
                                            <Label :for="`course-registration-close-${editing.id}`">{{ t('admin.coursesExams.fieldRegistrationCloseAt') }}</Label>
                                            <Input
                                                :id="`course-registration-close-${editing.id}`"
                                                v-model="courseEditForms[editing.id].registration_close_at"
                                                type="date"
                                            />
                                            <p v-if="courseEditForms[editing.id].errors.registration_close_at" class="text-xs text-destructive">
                                                {{ courseEditForms[editing.id].errors.registration_close_at }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid gap-1">
                                    <Label :for="`course-cert-template-${editing.id}`">{{ t('admin.coursesExams.fieldCertTemplate') }}</Label>
                                    <select :id="`course-cert-template-${editing.id}`" v-model="courseEditForms[editing.id].certificate_template_id" class="h-10 rounded-md border bg-background px-3 text-sm">
                                        <option :value="null">{{ t('admin.coursesExams.certTemplateDefault') }}</option>
                                        <option v-for="tpl in trainingTemplates()" :key="tpl.id" :value="tpl.id">{{ tpl.name }}</option>
                                    </select>
                                </div>

                                <div class="flex flex-wrap gap-4">
                                    <label class="flex items-center gap-2 text-sm">
                                        <Checkbox v-model:checked="courseEditForms[editing.id].requires_receipt" />
                                        {{ t('admin.coursesExams.requiresReceipt') }}
                                    </label>
                                    <label class="flex items-center gap-2 text-sm">
                                        <Checkbox v-model:checked="courseEditForms[editing.id].is_visible" />
                                        {{ t('admin.coursesExams.isVisible') }}
                                    </label>
                                </div>

                                <Button type="submit" class="w-full" :disabled="courseEditForms[editing.id].processing">{{ t('admin.coursesExams.course.save') }}</Button>
                            </form>
                        </template>
                    </div>
                </Transition>
            </TabsContent>

            <!-- Exams -->
            <TabsContent value="exam" class="p-4 space-y-4 border shadow-sm rounded-xl border-violet-200 bg-violet-50/40">
                <div class="flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-violet-900">
                        <span class="flex items-center justify-center rounded-full h-7 w-7 bg-violet-100 text-violet-600">
                            <ClipboardList class="w-4 h-4" />
                        </span>
                        {{ t('admin.coursesExams.exam.newTitle') }}
                    </h3>
                    <Button size="sm" variant="outline" @click="showNewExamForm = !showNewExamForm">
                        <Plus class="mr-1 h-3.5 w-3.5" />{{ t('admin.coursesExams.exam.newTitle') }}
                    </Button>
                </div>

                <!-- New exam form -->
                <Transition name="fade">
                    <form v-if="showNewExamForm" class="space-y-3 rounded-xl border-2 border-dashed bg-white p-4" @submit.prevent="createExam">
                        <div class="grid grid-cols-2 gap-2">
                            <div class="grid gap-1">
                                <Label for="new-exam-code">{{ t('admin.coursesExams.fieldCode') }}</Label>
                                <Input id="new-exam-code" v-model="newExamForm.code" :placeholder="t('admin.coursesExams.exam.codePlaceholder')" />
                                <p v-if="newExamForm.errors.code" class="text-xs text-destructive">{{ newExamForm.errors.code }}</p>
                            </div>
                            <div class="grid gap-1">
                                <Label for="new-exam-type">{{ t('admin.coursesExams.exam.type') }}</Label>
                                <select id="new-exam-type" v-model="newExamForm.type" class="h-10 rounded-md border bg-background px-3 text-sm">
                                    <option value="EPT">EPT</option>
                                    <option value="TOEIC">TOEIC</option>
                                    <option value="IELTS">IELTS</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-1">
                            <Label for="new-exam-name-th">{{ t('admin.coursesExams.fieldNameTh') }}</Label>
                            <Input id="new-exam-name-th" v-model="newExamForm.name_th" />
                            <p v-if="newExamForm.errors.name_th" class="text-xs text-destructive">{{ newExamForm.errors.name_th }}</p>
                        </div>

                        <div class="grid gap-1">
                            <Label for="new-exam-name-en">{{ t('admin.coursesExams.fieldNameEn') }}</Label>
                            <Input id="new-exam-name-en" v-model="newExamForm.name_en" />
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div class="grid gap-1">
                                <Label for="new-exam-price">{{ t('admin.coursesExams.exam.price') }}</Label>
                                <Input id="new-exam-price" v-model.number="newExamForm.price" type="number" min="0" />
                            </div>
                            <div class="grid gap-1">
                                <Label for="new-exam-date">{{ t('admin.coursesExams.exam.date') }}</Label>
                                <Input id="new-exam-date" v-model="newExamForm.exam_date" type="date" />
                                <p v-if="newExamForm.errors.exam_date" class="text-xs text-destructive">{{ newExamForm.errors.exam_date }}</p>
                            </div>
                        </div>

                        <div class="p-3 border rounded-lg border-amber-200 bg-amber-50">
                            <p class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-amber-700">
                                <CalendarClock class="h-3.5 w-3.5" />
                                {{ t('admin.coursesExams.registrationPeriodTitle') }}
                            </p>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="grid gap-1">
                                    <Label for="new-exam-registration-open">{{ t('admin.coursesExams.fieldRegistrationOpenAt') }}</Label>
                                    <Input id="new-exam-registration-open" v-model="newExamForm.registration_open_at" type="date" />
                                    <p v-if="newExamForm.errors.registration_open_at" class="text-xs text-destructive">
                                        {{ newExamForm.errors.registration_open_at }}
                                    </p>
                                </div>
                                <div class="grid gap-1">
                                    <Label for="new-exam-registration-close">{{ t('admin.coursesExams.fieldRegistrationCloseAt') }}</Label>
                                    <Input id="new-exam-registration-close" v-model="newExamForm.registration_close_at" type="date" />
                                    <p v-if="newExamForm.errors.registration_close_at" class="text-xs text-destructive">
                                        {{ newExamForm.errors.registration_close_at }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-1">
                            <Label for="new-exam-location">{{ t('admin.coursesExams.fieldLocation') }}</Label>
                            <Input id="new-exam-location" v-model="newExamForm.location" :placeholder="t('admin.coursesExams.exam.locationPlaceholder')" />
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div class="grid gap-1">
                                <Label for="new-exam-cert-template">{{ t('admin.coursesExams.fieldCertTemplate') }}</Label>
                                <select id="new-exam-cert-template" v-model="newExamForm.certificate_template_id" class="h-10 rounded-md border bg-background px-3 text-sm">
                                    <option :value="null">{{ t('admin.coursesExams.certTemplateDefault') }}</option>
                                    <option v-for="tpl in examTemplates()" :key="tpl.id" :value="tpl.id">{{ tpl.name }}</option>
                                </select>
                            </div>
                            <div class="grid gap-1">
                                <Label for="new-exam-score-scale">{{ t('admin.coursesExams.exam.scoreScale') }}</Label>
                                <select id="new-exam-score-scale" v-model="newExamForm.score_scale_id" class="h-10 rounded-md border bg-background px-3 text-sm">
                                    <option :value="null">{{ t('admin.coursesExams.exam.scoreScaleDefault') }}</option>
                                    <option v-for="scale in scoreScales" :key="scale.id" :value="scale.id">{{ scale.name }} (v{{ scale.version }})</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-end gap-4">
                            <label class="flex items-center gap-2 text-sm">
                                <Checkbox v-model:checked="newExamForm.requires_receipt" />
                                {{ t('admin.coursesExams.requiresReceipt') }}
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <Checkbox v-model:checked="newExamForm.is_visible" />
                                {{ t('admin.coursesExams.isVisible') }}
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <Checkbox v-model:checked="newExamForm.mail_delivery_available" />
                                {{ t('admin.coursesExams.exam.mailDeliveryAvailable') }}
                            </label>
                        </div>

                        <div v-if="newExamForm.mail_delivery_available" class="grid gap-1">
                            <Label for="new-exam-delivery-fee">{{ t('admin.coursesExams.exam.mailDeliveryFee') }}</Label>
                            <Input id="new-exam-delivery-fee" v-model.number="newExamForm.mail_delivery_fee" type="number" min="0" />
                        </div>

                        <div class="flex gap-2">
                            <Button type="submit" class="flex-1" :disabled="newExamForm.processing">{{ t('admin.coursesExams.exam.add') }}</Button>
                            <Button type="button" variant="outline" @click="showNewExamForm = false">{{ t('admin.coursesExams.cancel') }}</Button>
                        </div>
                    </form>
                </Transition>

                <!-- Exam list table -->
                <div class="overflow-x-auto bg-white border rounded-xl border-slate-200">
                    <table class="w-full text-xs text-left text-slate-700">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="p-2.5">{{ t('admin.coursesExams.colName') }}</th>
                                <th class="p-2.5">{{ t('admin.coursesExams.colDate') }}</th>
                                <th class="p-2.5">{{ t('admin.coursesExams.colVisible') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="exams.length === 0">
                                <td colspan="3" class="p-4 text-center text-muted-foreground">{{ t('admin.coursesExams.emptyExams') }}</td>
                            </tr>
                            <tr
                                v-for="exam in exams"
                                :key="exam.id"
                                class="border-t cursor-pointer border-slate-200 hover:bg-slate-50"
                                @click="toggleExpandExam(exam.id)"
                            >
                                <td class="p-2.5 font-semibold" :class="{ 'text-muted-foreground line-through': !exam.is_visible }">
                                    <span class="font-mono">{{ exam.code }}</span>: {{ exam.name_th }}
                                </td>
                                <td class="p-2.5 text-muted-foreground">{{ formatDate(exam.exam_date) }}</td>
                                <td class="p-2.5" @click.stop>
                                    <button
                                        type="button"
                                        class="p-1 rounded hover:bg-muted"
                                        :title="exam.is_visible ? t('admin.coursesExams.hide') : t('admin.coursesExams.show')"
                                        @click="toggleExamVisibility(exam)"
                                    >
                                        <Eye v-if="exam.is_visible" class="h-3.5 w-3.5 text-emerald-600" />
                                        <EyeOff v-else class="h-3.5 w-3.5 text-slate-400" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-if="exams.length > 0" class="text-[10px] text-muted-foreground">{{ t('admin.coursesExams.tableHint') }}</p>

                <!-- Edit card -->
                <Transition name="fade">
                    <div v-if="expandedExam()" :key="expandedExam()!.id" class="p-4 space-y-3 bg-white border shadow-sm rounded-xl border-slate-200">
                        <template v-for="editing in [expandedExam()!]" :key="editing.id">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                <h4 class="text-xs font-bold text-slate-700">{{ t('admin.coursesExams.exam.editTitle', { id: editing.id }) }}</h4>
                                <button type="button" class="p-1 rounded text-muted-foreground hover:bg-muted" @click="expandedExamId = null">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>

                            <form class="space-y-3" @submit.prevent="saveExam(editing.id)">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="grid gap-1">
                                        <Label :for="`exam-code-${editing.id}`">{{ t('admin.coursesExams.fieldCode') }}</Label>
                                        <Input :id="`exam-code-${editing.id}`" v-model="examEditForms[editing.id].code" />
                                        <p v-if="examEditForms[editing.id].errors.code" class="text-xs text-destructive">
                                            {{ examEditForms[editing.id].errors.code }}
                                        </p>
                                    </div>
                                    <div class="grid gap-1">
                                        <Label :for="`exam-type-${editing.id}`">{{ t('admin.coursesExams.exam.type') }}</Label>
                                        <select :id="`exam-type-${editing.id}`" v-model="examEditForms[editing.id].type" class="h-10 rounded-md border bg-background px-3 text-sm">
                                            <option value="EPT">EPT</option>
                                            <option value="TOEIC">TOEIC</option>
                                            <option value="IELTS">IELTS</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid gap-1">
                                    <Label :for="`exam-name-th-${editing.id}`">{{ t('admin.coursesExams.fieldNameTh') }}</Label>
                                    <Input :id="`exam-name-th-${editing.id}`" v-model="examEditForms[editing.id].name_th" />
                                    <p v-if="examEditForms[editing.id].errors.name_th" class="text-xs text-destructive">
                                        {{ examEditForms[editing.id].errors.name_th }}
                                    </p>
                                </div>

                                <div class="grid gap-1">
                                    <Label :for="`exam-name-en-${editing.id}`">{{ t('admin.coursesExams.fieldNameEn') }}</Label>
                                    <Input :id="`exam-name-en-${editing.id}`" v-model="examEditForms[editing.id].name_en" />
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div class="grid gap-1">
                                        <Label :for="`exam-price-${editing.id}`">{{ t('admin.coursesExams.exam.price') }}</Label>
                                        <Input :id="`exam-price-${editing.id}`" v-model.number="examEditForms[editing.id].price" type="number" min="0" />
                                    </div>
                                    <div class="grid gap-1">
                                        <Label :for="`exam-date-${editing.id}`">{{ t('admin.coursesExams.exam.date') }}</Label>
                                        <Input :id="`exam-date-${editing.id}`" v-model="examEditForms[editing.id].exam_date" type="date" />
                                        <p v-if="examEditForms[editing.id].errors.exam_date" class="text-xs text-destructive">
                                            {{ examEditForms[editing.id].errors.exam_date }}
                                        </p>
                                    </div>
                                </div>

                                <div class="p-3 border rounded-lg border-amber-200 bg-amber-50">
                                    <p class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-amber-700">
                                        <CalendarClock class="h-3.5 w-3.5" />
                                        {{ t('admin.coursesExams.registrationPeriodTitle') }}
                                    </p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="grid gap-1">
                                            <Label :for="`exam-registration-open-${editing.id}`">{{ t('admin.coursesExams.fieldRegistrationOpenAt') }}</Label>
                                            <Input
                                                :id="`exam-registration-open-${editing.id}`"
                                                v-model="examEditForms[editing.id].registration_open_at"
                                                type="date"
                                            />
                                            <p v-if="examEditForms[editing.id].errors.registration_open_at" class="text-xs text-destructive">
                                                {{ examEditForms[editing.id].errors.registration_open_at }}
                                            </p>
                                        </div>
                                        <div class="grid gap-1">
                                            <Label :for="`exam-registration-close-${editing.id}`">{{ t('admin.coursesExams.fieldRegistrationCloseAt') }}</Label>
                                            <Input
                                                :id="`exam-registration-close-${editing.id}`"
                                                v-model="examEditForms[editing.id].registration_close_at"
                                                type="date"
                                            />
                                            <p v-if="examEditForms[editing.id].errors.registration_close_at" class="text-xs text-destructive">
                                                {{ examEditForms[editing.id].errors.registration_close_at }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid gap-1">
                                    <Label :for="`exam-location-${editing.id}`">{{ t('admin.coursesExams.fieldLocation') }}</Label>
                                    <Input :id="`exam-location-${editing.id}`" v-model="examEditForms[editing.id].location" />
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div class="grid gap-1">
                                        <Label :for="`exam-cert-template-${editing.id}`">{{ t('admin.coursesExams.fieldCertTemplate') }}</Label>
                                        <select :id="`exam-cert-template-${editing.id}`" v-model="examEditForms[editing.id].certificate_template_id" class="h-10 rounded-md border bg-background px-3 text-sm">
                                            <option :value="null">{{ t('admin.coursesExams.certTemplateDefault') }}</option>
                                            <option v-for="tpl in examTemplates()" :key="tpl.id" :value="tpl.id">{{ tpl.name }}</option>
                                        </select>
                                    </div>
                                    <div class="grid gap-1">
                                        <Label :for="`exam-score-scale-${editing.id}`">{{ t('admin.coursesExams.exam.scoreScale') }}</Label>
                                        <select :id="`exam-score-scale-${editing.id}`" v-model="examEditForms[editing.id].score_scale_id" class="h-10 rounded-md border bg-background px-3 text-sm">
                                            <option :value="null">{{ t('admin.coursesExams.exam.scoreScaleDefault') }}</option>
                                            <option v-for="scale in scoreScales" :key="scale.id" :value="scale.id">{{ scale.name }} (v{{ scale.version }})</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-end gap-4">
                                    <label class="flex items-center gap-2 text-sm">
                                        <Checkbox v-model:checked="examEditForms[editing.id].requires_receipt" />
                                        {{ t('admin.coursesExams.requiresReceipt') }}
                                    </label>
                                    <label class="flex items-center gap-2 text-sm">
                                        <Checkbox v-model:checked="examEditForms[editing.id].is_visible" />
                                        {{ t('admin.coursesExams.isVisible') }}
                                    </label>
                                    <label class="flex items-center gap-2 text-sm">
                                        <Checkbox v-model:checked="examEditForms[editing.id].mail_delivery_available" />
                                        {{ t('admin.coursesExams.exam.mailDeliveryAvailable') }}
                                    </label>
                                </div>

                                <div v-if="examEditForms[editing.id].mail_delivery_available" class="grid gap-1">
                                    <Label :for="`exam-delivery-fee-${editing.id}`">{{ t('admin.coursesExams.exam.mailDeliveryFee') }}</Label>
                                    <Input :id="`exam-delivery-fee-${editing.id}`" v-model.number="examEditForms[editing.id].mail_delivery_fee" type="number" min="0" />
                                </div>

                                <Button type="submit" class="w-full" :disabled="examEditForms[editing.id].processing">{{ t('admin.coursesExams.exam.save') }}</Button>
                            </form>
                        </template>
                    </div>
                </Transition>
            </TabsContent>
        </Tabs>
        </div>
    </div>
</template>
