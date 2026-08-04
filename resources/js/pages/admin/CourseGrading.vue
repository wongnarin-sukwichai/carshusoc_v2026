<script setup lang="ts">
import { Badge, type BadgeVariants } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { CheckCircle2, GraduationCap, Search, XCircle } from 'lucide-vue-next';
import { computed, reactive, ref, watch } from 'vue';
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

const search = ref('');

const filteredEnrollments = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) return props.enrollments;

    return props.enrollments.filter((e) => e.user_name.toLowerCase().includes(query) || e.user_email.toLowerCase().includes(query));
});

const gradableEnrollments = computed(() => filteredEnrollments.value.filter((enrollment) => enrollment.status !== 'pending_payment'));

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
    <div class="flex flex-col flex-1 h-full gap-4 p-4 rounded-xl">
        <Head :title="t('nav.admin.courseGrading')" />

        <div class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200">
        <div class="flex flex-col justify-between gap-3 pb-2 border-b border-slate-100 md:flex-row md:items-center">
            <div>
                <h2 class="flex items-center gap-1.5 text-sm font-bold text-slate-800">
                    <GraduationCap class="w-4 h-4 text-indigo-600" />
                    {{ t('admin.courseGrading.title') }}
                </h2>
                <p class="text-[10px] text-slate-500">{{ t('admin.courseGrading.description') }}</p>
            </div>
            <select class="px-3 text-sm border rounded-md h-9 bg-background" :value="selectedCourseId ?? ''" @change="changeCourse">
                <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.code }}: {{ course.name_th }}</option>
            </select>
        </div>

        <div class="mt-4">
        <Transition name="fade" mode="out-in">
            <div :key="selectedCourseId ?? 'none'" class="flex flex-col gap-4">
                <div v-if="enrollments.length === 0" class="p-8 text-sm text-center border border-dashed rounded-xl text-muted-foreground">
                    {{ t('admin.courseGrading.empty') }}
                </div>

                <template v-else>
                    <div class="flex flex-wrap items-center gap-2">
                        <template v-if="gradableEnrollments.length > 0">
                            <Button
                                type="button"
                                size="sm"
                                variant="outline"
                                class="border-emerald-200 text-emerald-700 hover:bg-emerald-50 hover:text-emerald-800"
                                @click="selectAllPass"
                            >
                                <CheckCircle2 class="w-4 h-4" />
                                {{ t('admin.courseGrading.selectAllPass') }}
                            </Button>
                            <Button
                                type="button"
                                size="sm"
                                variant="outline"
                                class="text-red-700 border-red-200 hover:bg-red-50 hover:text-red-800"
                                @click="selectNone"
                            >
                                <XCircle class="w-4 h-4" />
                                {{ t('admin.courseGrading.selectNone') }}
                            </Button>
                        </template>

                        <div class="relative flex-1 max-w-xs ml-auto sm:flex-none">
                            <Search class="absolute top-1/2 left-2.5 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                            <Input v-model="search" type="text" :placeholder="t('admin.courseGrading.searchPlaceholder')" class="pl-8 text-xs" />
                        </div>

                        <Button type="button" size="sm" @click="saveGrades">{{ t('admin.courseGrading.save') }}</Button>
                    </div>

                    <div v-if="filteredEnrollments.length === 0" class="p-8 text-sm text-center border border-dashed rounded-xl text-muted-foreground">
                        {{ t('admin.courseGrading.noSearchResults') }}
                    </div>

                    <div v-else class="overflow-x-auto border rounded-xl border-slate-200">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th class="p-3">{{ t('admin.courseGrading.colStudent') }}</th>
                                    <th class="p-3 text-center">{{ t('admin.courseGrading.colStatus') }}</th>
                                    <th class="p-3 text-center">{{ t('admin.courseGrading.colGrade') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="enrollment in filteredEnrollments" :key="enrollment.id" class="border-t border-slate-200">
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
        </div>
    </div>
</template>
