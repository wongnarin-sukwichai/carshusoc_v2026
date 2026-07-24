<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Badge, type BadgeVariants } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';
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

defineProps<{
    courses: CourseOption[];
    selectedCourseId: number | null;
    enrollments: EnrollmentRow[];
}>();

const { t } = useI18n();

const changeCourse = (event: Event) => {
    const courseId = (event.target as HTMLSelectElement).value;
    router.get(route('admin.course-grading'), { course: courseId }, { preserveState: true });
};

const grade = (enrollment: EnrollmentRow, status: 'passed' | 'failed') => {
    router.post(route('admin.course-enrollments.grade', enrollment.id), { status }, { preserveScroll: true });
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

            <div v-if="enrollments.length === 0" class="rounded-xl border border-dashed p-8 text-center text-sm text-muted-foreground">
                {{ t('admin.courseGrading.empty') }}
            </div>

            <div v-else class="overflow-x-auto rounded-xl border border-slate-200">
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
                                <Badge :variant="statusVariant[enrollment.status]" :class="enrollment.status === 'studying' ? 'animate-pulse' : ''">{{
                                    statusLabel[enrollment.status]
                                }}</Badge>
                            </td>
                            <td class="p-3">
                                <div v-if="enrollment.status === 'studying'" class="flex justify-center gap-2">
                                    <Button size="sm" @click="grade(enrollment, 'passed')">{{ t('admin.courseGrading.pass') }}</Button>
                                    <Button size="sm" variant="destructive" @click="grade(enrollment, 'failed')">{{
                                        t('admin.courseGrading.fail')
                                    }}</Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
    </div>
</template>
