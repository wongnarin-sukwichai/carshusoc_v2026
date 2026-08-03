<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Badge, type BadgeVariants } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { CheckCircle2, XCircle } from 'lucide-vue-next';
import { computed, reactive, watch } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AdminLayout });

interface CourseOption {
    id: number;
    code: string;
    name_th: string;
    level: number;
}

interface EnrollmentRow {
    id: number;
    user_name: string;
    user_email: string;
    status: 'pending_payment' | 'studying' | 'passed' | 'failed';
}

const props = defineProps<{
    courses: CourseOption[];
    selectedCourseId: number | null;
    enrollments: EnrollmentRow[];
}>();

const { t } = useI18n();

const changeCourse = (event: Event) => {
    const courseId = (event.target as HTMLSelectElement).value;
    router.get(route('admin.course-grading'), { course: courseId }, { preserveState: true });
};

// Pass/fail is editable indefinitely, not just while "studying" — only rows
// still awaiting payment have nothing to grade yet. Toggling a switch (or the
// select-all/none shortcuts) only changes this staged local state; nothing is
// sent to the server until "save", and only rows whose decision actually
// differs from their current DB status are included in that request.
const decisions = reactive<Record<number, boolean>>({});

const gradableEnrollments = computed(() => props.enrollments.filter((enrollment) => enrollment.status !== 'pending_payment'));

watch(
    () => props.enrollments,
    (rows) => {
        for (const key of Object.keys(decisions)) {
            delete decisions[Number(key)];
        }
        for (const row of rows) {
            if (row.status !== 'pending_payment') {
                decisions[row.id] = row.status === 'passed';
            }
        }
    },
    { immediate: true },
);

const selectAllPass = () => {
    gradableEnrollments.value.forEach((enrollment) => {
        decisions[enrollment.id] = true;
    });
};

const selectNone = () => {
    gradableEnrollments.value.forEach((enrollment) => {
        decisions[enrollment.id] = false;
    });
};

const saveGrades = () => {
    const grades = gradableEnrollments.value
        .filter((enrollment) => decisions[enrollment.id] !== (enrollment.status === 'passed'))
        .map((enrollment) => ({
            id: enrollment.id,
            status: decisions[enrollment.id] ? 'passed' : 'failed',
        }));

    if (grades.length === 0) {
        return;
    }

    router.post(route('admin.course-grading.save'), { grades }, { preserveScroll: true });
};

const statusLabel = computed<Record<EnrollmentRow['status'], string>>(() => ({
    pending_payment: t('admin.courseGrading.statusPendingPayment'),
    studying: t('admin.courseGrading.statusStudying'),
    passed: t('admin.courseGrading.statusPassed'),
    failed: t('admin.courseGrading.statusFailed'),
}));

const statusVariant: Record<EnrollmentRow['status'], NonNullable<BadgeVariants['variant']>> = {
    pending_payment: 'neutral',
    studying: 'info',
    passed: 'success',
    failed: 'destructive',
};
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <Head :title="t('nav.admin.courseGrading')" />
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <HeadingSmall :title="t('admin.courseGrading.title')" :description="t('admin.courseGrading.description')" />
            <select class="h-9 rounded-md border bg-background px-3 text-sm" :value="selectedCourseId ?? ''" @change="changeCourse">
                <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.code }}: {{ course.name_th }}</option>
            </select>
        </div>

        <Transition name="fade" mode="out-in">
            <div :key="selectedCourseId ?? 'none'" class="flex flex-col gap-4">
                <div v-if="enrollments.length === 0" class="rounded-xl border border-dashed p-8 text-center text-sm text-muted-foreground">
                    {{ t('admin.courseGrading.empty') }}
                </div>

                <template v-else>
                    <div v-if="gradableEnrollments.length > 0" class="flex flex-wrap items-center gap-2">
                        <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            class="border-emerald-200 text-emerald-700 hover:bg-emerald-50 hover:text-emerald-800"
                            @click="selectAllPass"
                        >
                            <CheckCircle2 class="h-4 w-4" />
                            {{ t('admin.courseGrading.selectAllPass') }}
                        </Button>
                        <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            class="border-red-200 text-red-700 hover:bg-red-50 hover:text-red-800"
                            @click="selectNone"
                        >
                            <XCircle class="h-4 w-4" />
                            {{ t('admin.courseGrading.selectNone') }}
                        </Button>
                        <Button type="button" size="sm" class="ml-auto" @click="saveGrades">{{ t('admin.courseGrading.save') }}</Button>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th class="p-3">{{ t('admin.courseGrading.colStudent') }}</th>
                                    <th class="p-3 text-center">{{ t('admin.courseGrading.colStatus') }}</th>
                                    <th class="p-3 text-center">{{ t('admin.courseGrading.colGrade') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="enrollment in enrollments" :key="enrollment.id" class="border-t border-slate-200">
                                    <td class="p-3">
                                        <p class="font-medium">{{ enrollment.user_name }}</p>
                                        <p class="font-mono text-xs text-muted-foreground">{{ enrollment.user_email }}</p>
                                    </td>
                                    <td class="p-3 text-center">
                                        <Badge
                                            :variant="statusVariant[enrollment.status]"
                                            :class="enrollment.status === 'studying' ? 'animate-pulse' : ''"
                                            >{{ statusLabel[enrollment.status] }}</Badge
                                        >
                                    </td>
                                    <td class="p-3">
                                        <div v-if="enrollment.status !== 'pending_payment'" class="flex items-center justify-center gap-2">
                                            <span class="text-xs text-muted-foreground">{{ t('admin.courseGrading.fail') }}</span>
                                            <Switch v-model:checked="decisions[enrollment.id]" />
                                            <span class="text-xs text-muted-foreground">{{ t('admin.courseGrading.pass') }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
            </div>
        </Transition>
    </div>
</template>
