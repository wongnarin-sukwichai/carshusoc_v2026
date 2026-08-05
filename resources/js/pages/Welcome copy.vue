<script setup lang="ts">
import BrandHeader from '@/components/BrandHeader.vue';
import ExamRequiredDocumentsModal from '@/components/ExamRequiredDocumentsModal.vue';
import ExamRulesModal from '@/components/ExamRulesModal.vue';
import FlashMessage from '@/components/FlashMessage.vue';
import LocaleSwitcher from '@/components/LocaleSwitcher.vue';
import LoginModal from '@/components/LoginModal.vue';
import RegisterModal from '@/components/RegisterModal.vue';
import TrainingGuidelinesModal from '@/components/TrainingGuidelinesModal.vue';
import { formatDate } from '@/lib/date';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    BookOpenCheck,
    Calendar,
    CalendarClock,
    CalendarRange,
    FileCheck2,
    GraduationCap,
    Lock,
    MapPin,
    ShieldAlert,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

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

interface Exam {
    id: number;
    code: string;
    type: string;
    name_th: string;
    name_en: string;
    price: string;
    exam_date: string;
    location: string | null;
    registration_open_at: string | null;
    registration_close_at: string | null;
}

const props = defineProps<{
    courses: Course[];
    exams: Exam[];
}>();

const page = usePage<SharedData>();
const { t, locale } = useI18n();

const isAuthenticated = computed(() => Boolean(page.props.auth.user));

const activeTab = ref<'training' | 'exam'>('training');

// Same nav items and per-item icon colors as components/UserSidebar.vue —
// this sidebar is the guest-facing subset of it (training/exam only, since
// translation and portfolio require an account), kept visually identical so
// the transition into the real app after logging in doesn't feel like a
// different product.
const tabs = computed(() =>
    [
        { key: 'training' as const, title: t('nav.user.training'), icon: BookOpen, iconClass: 'text-blue-600', centerCode: 'training' },
        { key: 'exam' as const, title: t('nav.user.exam'), icon: GraduationCap, iconClass: 'text-violet-600', centerCode: 'exam' },
    ].filter((tab) => page.props.serviceCenters[tab.centerCode] !== false),
);

const courseName = (course: Course | { name_th: string; name_en?: string }) =>
    locale.value === 'en' && 'name_en' in course && course.name_en ? course.name_en : course.name_th;
const examName = (exam: Exam) => (locale.value === 'en' && exam.name_en ? exam.name_en : exam.name_th);

const showGuidelines = ref(false);
const showRequiredDocuments = ref(false);
const showExamRules = ref(false);
</script>

<template>
    <Head :title="t('welcome.title')" />

    <Transition name="fade" mode="out-in">
    <div :key="locale" class="flex min-h-screen flex-col bg-white text-slate-800 dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
        <BrandHeader accent="blue">
            <LocaleSwitcher class="text-white hover:text-white" />
            <Link
                v-if="isAuthenticated"
                :href="route('dashboard')"
                class="rounded-lg border border-slate-700 bg-slate-800 px-4 py-1.5 text-xs font-bold hover:bg-slate-700"
            >
                {{ t('welcome.dashboard') }}
            </Link>
            <template v-else>
                <LoginModal />
                <RegisterModal />
            </template>
        </BrandHeader>

        <div class="grid w-full flex-1 grid-cols-1 gap-6 px-6 py-8 lg:grid-cols-12 lg:px-[12.5%]">
            <!-- Guest sidebar: training/exam only, no translation/portfolio (require login) -->
            <aside class="flex flex-col gap-6 lg:col-span-4">
                <div class="flex flex-col overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
                    <div class="p-4 space-y-1">
                        <span class="mb-2 block px-2 text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{
                            t('nav.user.groupLabel')
                        }}</span>
                        <nav class="space-y-1.5">
                            <button
                                v-for="tab in tabs"
                                :key="tab.key"
                                type="button"
                                class="flex items-center w-full gap-3 px-3 py-3 text-xs font-bold text-left transition-all border-l-4 rounded-xl"
                                :class="
                                    activeTab === tab.key
                                        ? 'border-indigo-600 bg-indigo-50 font-extrabold text-indigo-700'
                                        : 'border-transparent text-slate-600 hover:bg-slate-50'
                                "
                                @click="activeTab = tab.key"
                            >
                                <component :is="tab.icon" class="w-4 h-4" :class="tab.iconClass" />
                                <span>{{ tab.title }}</span>
                            </button>
                        </nav>
                    </div>

                    <div class="p-4 mt-auto border-t border-slate-100 bg-slate-50">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center border rounded-full h-9 w-9 border-slate-300 bg-slate-200 text-slate-500">
                                <Lock class="w-4 h-4" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="flex items-center gap-1 text-xs font-bold text-red-600 truncate">
                                    <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                                    {{ t('welcome.notLoggedIn') }}
                                </p>
                                <p class="truncate text-[10px] text-slate-500">{{ t('welcome.loginToUseFull') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
            
            <main class="flex flex-col gap-6 lg:col-span-8">
              <Transition name="fade" mode="out-in">
                <!-- Training tab -->
                <div v-if="activeTab === 'training'" key="training" class="flex flex-col gap-6">
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
                                    <p
                                        v-if="course.start_date && course.end_date"
                                        class="flex items-center gap-1.5 text-xs text-slate-900 font-semibold"
                                    >
                                        <CalendarRange class="w-3 h-3 shrink-0" />
                                        {{
                                            t('user.training.trainingPeriod', {
                                                start: formatDate(course.start_date),
                                                end: formatDate(course.end_date),
                                            })
                                        }}
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

                            <LoginModal>
                                <button
                                    type="button"
                                    class="flex items-center w-full gap-2 p-3 mt-4 text-left border rounded-lg border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100"
                                >
                                    <Lock class="w-4 h-4 shrink-0" />
                                    <span>
                                        <span class="block text-xs font-bold">{{ t('welcome.notLoggedIn') }}</span>
                                        <span class="block text-[10px]">{{ t('welcome.guestCardSubtitle') }}</span>
                                    </span>
                                </button>
                            </LoginModal>
                        </div>
                    </div>
                </div>

                <!-- Exam tab -->
                <div v-else-if="activeTab === 'exam'" key="exam" class="flex flex-col gap-6">
                    <div class="relative p-6 overflow-hidden text-white rounded-2xl bg-gradient-to-br from-slate-900 to-indigo-950">
                        <GraduationCap class="absolute bottom-0 right-0 w-48 h-48 translate-x-12 translate-y-6 opacity-10" />
                        <div class="relative z-10 max-w-2xl">
                            <span class="rounded bg-indigo-500 px-2 py-0.5 text-[9px] font-black tracking-widest text-white uppercase">
                                {{ t('nav.user.exam') }}
                            </span>
                            <h2 class="mt-2 text-xl font-extrabold">{{ t('nav.user.exam') }}</h2>
                            <p class="mt-1 text-xs leading-relaxed text-slate-300">{{ t('user.exam.description') }}</p>
                            <div class="flex flex-wrap gap-2 mt-3">
                                <button
                                    type="button"
                                    class="flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs font-bold text-white hover:bg-white/20"
                                    @click="showRequiredDocuments = true"
                                >
                                    <FileCheck2 class="h-3.5 w-3.5" />
                                    {{ t('components.examRequiredDocuments.trigger') }}
                                </button>
                                <button
                                    type="button"
                                    class="flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs font-bold text-white hover:bg-white/20"
                                    @click="showExamRules = true"
                                >
                                    <ShieldAlert class="h-3.5 w-3.5" />
                                    {{ t('components.examRules.trigger') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <p v-if="exams.length === 0" class="p-8 text-sm text-center border border-dashed rounded-xl text-muted-foreground">
                        {{ t('user.exam.empty') }}
                    </p>

                    <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div v-for="exam in exams" :key="exam.id" class="flex flex-col justify-between p-4 border shadow-sm border-violet-600 rounded-xl">
                            <div class="flex flex-col flex-1">
                                <div class="flex items-center justify-between mb-2 text-xs">
                                    <span class="rounded-lg bg-violet-100 px-2 py-0.5 font-bold text-violet-700">{{ exam.type }}</span>
                                </div>
                                <h3 class="font-bold">{{ examName(exam) }}</h3>
                                <p class="mt-1 text-xs text-muted-foreground">{{ t('user.exam.codeLabel', { code: exam.code }) }}</p>
                                <p class="mt-1 flex items-center gap-1.5 text-xs font-semibold text-slate-900">
                                    <Calendar class="w-3 h-3 shrink-0" />
                                    {{ t('user.exam.examDateLabel', { date: formatDate(exam.exam_date) }) }}
                                </p>
                                <p v-if="exam.location" class="mt-1 flex items-center gap-1.5 text-xs text-muted-foreground mb-2">
                                    <MapPin class="w-3 h-3 shrink-0" />
                                    {{ exam.location }}
                                </p>

                                <div
                                    v-if="exam.registration_open_at || exam.registration_close_at"
                                    class="mt-auto flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-2 py-1.5 text-xs text-amber-800"
                                >
                                    <CalendarClock class="w-3.5 h-3.5 shrink-0 text-amber-600" />
                                    {{
                                        t('user.exam.registrationPeriod', {
                                            start: exam.registration_open_at ? formatDate(exam.registration_open_at) : '—',
                                            end: exam.registration_close_at ? formatDate(exam.registration_close_at) : '—',
                                        })
                                    }}
                                </div>
                            </div>

                            <LoginModal>
                                <button
                                    type="button"
                                    class="flex items-center w-full gap-2 p-3 mt-4 text-left border rounded-lg border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100"
                                >
                                    <Lock class="w-4 h-4 shrink-0" />
                                    <span>
                                        <span class="block text-xs font-bold">{{ t('welcome.notLoggedIn') }}</span>
                                        <span class="block text-[10px]">{{ t('welcome.guestCardSubtitle') }}</span>
                                    </span>
                                </button>
                            </LoginModal>
                        </div>
                    </div>
                </div>
              </Transition>
            </main>
        </div>
    </div>
    </Transition>

    <TrainingGuidelinesModal v-model:open="showGuidelines" />
    <ExamRequiredDocumentsModal v-model:open="showRequiredDocuments" />
    <ExamRulesModal v-model:open="showExamRules" />

    <FlashMessage />
</template>
