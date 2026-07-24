<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import PaymentSlipDialog from '@/components/PaymentSlipDialog.vue';
import { Button } from '@/components/ui/button';
import UserLayout from '@/layouts/user/UserLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { BookOpen, Lock } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: UserLayout });

interface Course {
    id: number;
    code: string;
    name_th: string;
    name_en: string;
    language: string;
    level: number;
    price: string;
    prerequisite_course_id: number | null;
    prerequisite: { id: number; name_th: string; name_en: string } | null;
}

interface Enrollment {
    id: number;
    course_id: number;
    status: 'pending_payment' | 'studying' | 'passed' | 'failed';
}

const props = defineProps<{
    courses: Course[];
    enrollments: Record<number, Enrollment>;
}>();

const { t, locale } = useI18n();

const courseName = (course: Course | { name_th: string; name_en?: string }) =>
    locale.value === 'en' && 'name_en' in course && course.name_en ? course.name_en : course.name_th;

const eligibility = (course: Course): { eligible: boolean; reason: string | null } => {
    const enrollment = props.enrollments[course.id];

    if (enrollment?.status === 'passed') {
        return { eligible: false, reason: t('user.training.reasonPassed') };
    }
    if (enrollment?.status === 'studying') {
        return { eligible: false, reason: t('user.training.reasonStudying') };
    }
    if (enrollment?.status === 'pending_payment') {
        return { eligible: false, reason: t('user.training.reasonPendingPayment') };
    }
    if (course.prerequisite_course_id) {
        const prereqStatus = props.enrollments[course.prerequisite_course_id]?.status;
        if (prereqStatus !== 'passed') {
            return {
                eligible: false,
                reason: t('user.training.reasonPrerequisite', { name: course.prerequisite ? courseName(course.prerequisite) : '' }),
            };
        }
    }
    return { eligible: true, reason: null };
};

const enroll = (course: Course) => {
    router.post(route('user.courses.enroll', course.id), {}, { preserveScroll: true });
};
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <Head :title="t('nav.user.training')" />
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-900 to-slate-900 p-6 text-white">
                <BookOpen class="absolute right-0 bottom-0 h-48 w-48 translate-x-12 translate-y-6 opacity-10" />
                <div class="relative z-10 max-w-2xl">
                    <span class="rounded bg-indigo-500 px-2 py-0.5 text-[9px] font-black tracking-widest text-white uppercase">
                        {{ t('nav.user.training') }}
                    </span>
                    <h2 class="mt-2 text-xl font-extrabold">{{ t('nav.user.training') }}</h2>
                    <p class="mt-1 text-xs leading-relaxed text-slate-300">{{ t('user.training.description') }}</p>
                </div>
            </div>

            <p v-if="courses.length === 0" class="rounded-xl border border-dashed p-8 text-center text-sm text-muted-foreground">
                {{ t('user.training.empty') }}
            </p>

            <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="course in courses"
                    :key="course.id"
                    class="flex flex-col justify-between rounded-xl border border-slate-200 p-4 shadow-sm"
                >
                    <div>
                        <div class="mb-2 flex items-center justify-between text-xs">
                            <span class="rounded-full bg-muted px-2 py-0.5 font-medium"
                                >{{ t('user.training.languagePrefix') }}{{ course.language }}</span
                            >
                            <span class="font-mono text-muted-foreground">{{ course.code }}</span>
                        </div>
                        <h3 class="font-bold">{{ courseName(course) }}</h3>
                        <p class="mt-1 text-xs text-muted-foreground">{{ t('user.training.levelLabel', { level: course.level }) }}</p>
                        <div
                            v-if="course.prerequisite"
                            class="mt-2 flex items-center gap-1.5 rounded-lg bg-muted px-2 py-1 text-xs text-muted-foreground"
                        >
                            <Lock class="h-3 w-3" />
                            {{ t('user.training.prerequisiteNotice', { name: courseName(course.prerequisite) }) }}
                        </div>
                    </div>

                    <div class="mt-4 border-t pt-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-muted-foreground">{{ t('user.training.fee') }}</p>
                                <p class="text-lg font-bold">{{ Number(course.price).toLocaleString() }} ฿</p>
                            </div>

                            <div>
                                <Badge v-if="enrollments[course.id]?.status === 'passed'" variant="success">{{
                                    t('user.training.statusPassed')
                                }}</Badge>
                                <Badge v-else-if="enrollments[course.id]?.status === 'studying'" variant="info" class="animate-pulse">{{
                                    t('user.training.statusStudying')
                                }}</Badge>
                                <PaymentSlipDialog
                                    v-else-if="enrollments[course.id]?.status === 'pending_payment'"
                                    payable-type="course_enrollment"
                                    :payable-id="enrollments[course.id].id"
                                    :title="courseName(course)"
                                    :amount="Number(course.price)"
                                    :trigger-label="t('user.training.uploadSlip')"
                                />
                                <Button v-else-if="eligibility(course).eligible" size="sm" @click="enroll(course)">{{
                                    t('user.training.enroll')
                                }}</Button>
                                <Badge v-else variant="neutral">{{ t('user.training.locked') }}</Badge>
                            </div>
                        </div>
                        <p
                            v-if="!eligibility(course).eligible && enrollments[course.id]?.status !== 'passed'"
                            class="mt-2 text-right text-[11px] italic text-red-500"
                        >
                            * {{ eligibility(course).reason }}
                        </p>
                    </div>
                </div>
            </div>
    </div>
</template>
