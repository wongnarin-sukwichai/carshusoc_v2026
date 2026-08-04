<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import PaymentSlipDialog from '@/components/PaymentSlipDialog.vue';
import TrainingGuidelinesModal from '@/components/TrainingGuidelinesModal.vue';
import { Button } from '@/components/ui/button';
import UserLayout from '@/layouts/user/UserLayout.vue';
import { formatDate } from '@/lib/date';
import { confirmDialog } from '@/lib/swal';
import { Head, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { BookOpen, BookOpenCheck, CalendarClock, CalendarRange, CheckCircle2, Lock, MapPin } from 'lucide-vue-next';
import { ref } from 'vue';
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
    location: string | null;
    start_date: string | null;
    end_date: string | null;
    registration_open_at: string | null;
    registration_close_at: string | null;
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
    if (course.registration_open_at && dayjs().isBefore(dayjs(course.registration_open_at), 'day')) {
        return { eligible: false, reason: t('user.training.reasonRegistrationNotOpen', { date: formatDate(course.registration_open_at) }) };
    }
    if (course.registration_close_at && dayjs().isAfter(dayjs(course.registration_close_at), 'day')) {
        return { eligible: false, reason: t('user.training.reasonRegistrationClosed', { date: formatDate(course.registration_close_at) }) };
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

const enroll = async (course: Course) => {
    const confirmed = await confirmDialog({
        title: t('common.areYouSure'),
        text: t('user.training.confirmEnrollText', { name: courseName(course) }),
        icon: 'question',
        confirmButtonText: t('user.training.confirmEnrollButton'),
    });

    if (!confirmed) return;

    router.post(route('user.courses.enroll', course.id), {}, { preserveScroll: true });
};

const showGuidelines = ref(false);
</script>

<template>
    <div class="flex flex-col flex-1 h-full gap-4 p-4 rounded-xl">
        <Head :title="t('nav.user.training')" />
            <div class="relative p-6 overflow-hidden text-white rounded-2xl bg-gradient-to-br from-indigo-900 to-slate-900">
                <BookOpen class="absolute bottom-0 right-0 w-48 h-48 translate-x-12 translate-y-6 opacity-10" />
                <div class="relative z-10 max-w-2xl">
                    <span class="rounded bg-indigo-500 px-2 py-0.5 text-[9px] font-black tracking-widest text-white uppercase">
                        {{ t('nav.user.training') }}
                    </span>
                    <h2 class="mt-2 text-xl font-extrabold">{{ t('nav.user.training') }}</h2>
                    <p class="mt-1 text-xs leading-relaxed text-slate-300">{{ t('user.training.description') }}</p>
                    <button
                        type="button"
                        class="mt-3 flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs font-bold text-white hover:bg-white/20"
                        @click="showGuidelines = true"
                    >
                        <BookOpenCheck class="h-3.5 w-3.5" />
                        {{ t('components.trainingGuidelines.trigger') }}
                    </button>
                </div>
            </div>

            <p v-if="courses.length === 0" class="p-8 text-sm text-center border border-dashed rounded-xl text-muted-foreground">
                {{ t('user.training.empty') }}
            </p>

            <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="course in courses"
                    :key="course.id"
                    class="flex flex-col justify-between p-4 border border-blue-500 shadow-sm rounded-xl"
                >
                    <div class="flex flex-col flex-1">
                        <div class="flex items-center justify-between mb-2 text-xs">
                            <span class="rounded-full bg-blue-100 px-2 py-0.5 font-bold text-blue-700"
                                >{{ t('user.training.languagePrefix') }}{{ course.language }}</span
                            >
                            <span class="font-mono text-muted-foreground">{{ course.code }}</span>
                        </div>
                        <h3 class="font-bold">{{ courseName(course) }}</h3>
                        <p class="mt-1 text-xs text-muted-foreground">{{ t('user.training.levelLabel', { level: course.level }) }}</p>

                        <div class="mt-2.5 space-y-1">
                            <p v-if="course.start_date && course.end_date" class="flex items-center gap-1.5 text-xs text-slate-900 font-semibold">
                                <CalendarRange class="w-3 h-3 shrink-0" />
                                {{ t('user.training.trainingPeriod', { start: formatDate(course.start_date), end: formatDate(course.end_date) }) }}
                            </p>
                            <p v-if="course.location" class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                <MapPin class="w-3 h-3 shrink-0" />
                                {{ course.location }}
                            </p>
                        </div>

                        <div
                            v-if="course.prerequisite"
                            class="mt-2.5 flex items-center gap-1.5 rounded-lg border border-violet-200 bg-violet-50 px-2 py-1.5 text-xs text-violet-800 mb-2"
                        >
                            <Lock class="w-3.5 h-3.5 shrink-0 text-violet-600" />
                            {{ t('user.training.prerequisiteNotice', { name: courseName(course.prerequisite) }) }}
                        </div>

                        <div
                            v-if="course.registration_open_at || course.registration_close_at"
                            class="mt-auto flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-2 py-1.5 text-xs text-amber-800"
                        >
                            <CalendarClock class="w-3.5 h-3.5 shrink-0 text-amber-600" />
                            {{
                                t('user.training.registrationPeriod', {
                                    start: course.registration_open_at ? formatDate(course.registration_open_at) : '—',
                                    end: course.registration_close_at ? formatDate(course.registration_close_at) : '—',
                                })
                            }}
                        </div>
                    </div>
                    
                    <div class="pt-3 mt-2 border-t">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-muted-foreground">{{ t('user.training.fee') }}</p>
                                <p class="text-lg font-bold">{{ Number(course.price).toLocaleString() }} ฿</p>
                            </div>

                            <div>
                                <span
                                    v-if="enrollments[course.id]?.status === 'passed'"
                                    class="flex items-center gap-1 text-sm font-bold text-emerald-600"
                                >
                                    <CheckCircle2 class="w-4 h-4" />
                                    {{ t('user.training.statusPassed') }}
                                </span>
                                <Badge
                                    v-else-if="enrollments[course.id]?.status === 'studying'"
                                    variant="info"
                                    class="text-white bg-indigo-600 border-indigo-600"
                                    >{{ t('user.training.statusStudying') }}</Badge
                                >
                                <PaymentSlipDialog
                                    v-else-if="enrollments[course.id]?.status === 'pending_payment'"
                                    payable-type="course_enrollment"
                                    :payable-id="enrollments[course.id].id"
                                    :title="courseName(course)"
                                    :amount="Number(course.price)"
                                    :trigger-label="t('user.training.uploadSlip')"
                                    @success="showGuidelines = true"
                                />
                                <Button v-else-if="eligibility(course).eligible" size="sm" @click="enroll(course)">{{
                                    t('user.training.enroll')
                                }}</Button>
                                <span v-else class="flex items-center gap-1 text-sm font-bold text-slate-500">
                                    <Lock class="w-4 h-4" />
                                    {{ t('user.training.locked') }}
                                </span>
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

        <TrainingGuidelinesModal v-model:open="showGuidelines" />
    </div>
</template>
