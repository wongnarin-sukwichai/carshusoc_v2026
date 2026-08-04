<script setup lang="ts">
import AdminUserMenuContent from '@/components/AdminUserMenuContent.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { formatDate } from '@/lib/date';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Award,
    BookOpen,
    ChevronDown,
    ChevronRight,
    ChevronsUpDown,
    Database,
    DollarSign,
    FileText,
    GraduationCap,
    HelpCircle,
    Mail,
    Ruler,
    Sliders,
    TrendingUp,
    UserCheck,
    Users,
    type LucideIcon,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

interface NavEntry {
    title: string;
    routeName: string;
    icon: LucideIcon;
    adminOnly?: boolean;
}

const page = usePage<SharedData>();
const admin = page.props.auth.admin!;
const isAdmin = computed(() => admin.role === 'admin');
const { t } = useI18n();

// Labels intentionally drop the "(3.x)" numbering used in the docs/exampage-mockup-reference.jsx
// mockup. Items flagged adminOnly mirror the "admin.role:admin" route gate in
// routes/admin.php — this is UX (hide what you can't use), the real
// enforcement is server-side.
const topNavItems = computed<NavEntry[]>(() => [
    { title: t('nav.admin.dashboard'), routeName: 'admin.dashboard', icon: TrendingUp },
    { title: t('nav.admin.payments'), routeName: 'admin.payments', icon: DollarSign },
    { title: t('nav.admin.staff'), routeName: 'admin.staff', icon: UserCheck, adminOnly: true },
    { title: t('nav.admin.registrants'), routeName: 'admin.registrants', icon: Users },
    { title: t('nav.admin.coursesExams'), routeName: 'admin.courses-exams', icon: Sliders },
]);

// Mirrors the mockup's collapsible "DATA MANAGEMENT" group (default open).
const dataManagementItems = computed<NavEntry[]>(() => [
    { title: t('nav.admin.examScores'), routeName: 'admin.exam-scores', icon: GraduationCap },
    { title: t('nav.admin.courseGrading'), routeName: 'admin.course-grading', icon: BookOpen },
    { title: t('nav.admin.translationQuotes'), routeName: 'admin.translation-quotes', icon: FileText },
    { title: t('nav.admin.scoreScales'), routeName: 'admin.score-scales', icon: Ruler },
]);

const bottomNavItems = computed<NavEntry[]>(() => [
    { title: t('nav.admin.emailTemplates'), routeName: 'admin.email-templates', icon: Mail, adminOnly: true },
    { title: t('nav.admin.certificateTemplates'), routeName: 'admin.certificate-templates', icon: Award, adminOnly: true },
    { title: t('nav.admin.manual'), routeName: 'admin.manual', icon: HelpCircle },
]);

const visibleTopNavItems = computed(() => topNavItems.value.filter((item) => !item.adminOnly || isAdmin.value));
const visibleBottomNavItems = computed(() => bottomNavItems.value.filter((item) => !item.adminOnly || isAdmin.value));

const isDataMenuOpen = ref(false);

// Which grouped email-log rows are expanded to show their per-recipient list.
const expandedEmailLogIds = ref<Set<number | string>>(new Set());
const toggleEmailLogExpanded = (id: number | string) => {
    const next = new Set(expandedEmailLogIds.value);
    next.has(id) ? next.delete(id) : next.add(id);
    expandedEmailLogIds.value = next;
};
</script>

<template>
    <aside class="flex flex-col gap-6 lg:col-span-4">
        <div class="flex flex-col overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
            <div class="p-4 space-y-1">
                <span class="mb-2 block px-2 text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{ t('nav.admin.groupLabel') }}</span>
                <nav class="space-y-1.5">
                    <Link
                        v-for="item in visibleTopNavItems"
                        :key="item.routeName"
                        :href="route(item.routeName)"
                        class="flex items-center gap-3 px-3 py-3 text-xs font-bold text-left transition-all border-l-4 rounded-xl"
                        :class="
                            route().current(item.routeName)
                                ? 'border-indigo-600 bg-indigo-50 font-extrabold text-indigo-700'
                                : 'border-transparent text-slate-600 hover:bg-slate-50'
                        "
                    >
                        <component :is="item.icon" class="w-4 h-4 text-indigo-600" />
                        <span>{{ item.title }}</span>
                    </Link>

                    <div class="pt-2 pb-1 my-2 border-t border-b border-slate-100">
                        <button
                            type="button"
                            class="mb-1.5 flex w-full items-center justify-between px-3 text-left text-xs font-bold tracking-wider text-slate-400 uppercase transition-colors hover:text-slate-600"
                            @click="isDataMenuOpen = !isDataMenuOpen"
                        >
                            <span class="flex items-center gap-1">
                                <Database class="w-3 h-3 text-indigo-500" />
                                {{ t('nav.admin.dataManagement') }}
                            </span>
                            <ChevronDown v-if="isDataMenuOpen" class="w-3 h-3 text-slate-400" />
                            <ChevronRight v-else class="w-3 h-3 text-slate-400" />
                        </button>

                        <div v-if="isDataMenuOpen" class="px-4 space-y-1 border-b-gray-500">
                            <Link
                                v-for="item in dataManagementItems"
                                :key="item.routeName"
                                :href="route(item.routeName)"
                                class="flex items-center gap-2.5 rounded-xl border-l-4 px-3 py-2 text-left text-xs font-bold transition-all"
                                :class="
                                    route().current(item.routeName)
                                        ? 'border-indigo-600 bg-indigo-50 font-extrabold text-indigo-700'
                                        : 'border-transparent text-slate-600 hover:bg-slate-50'
                                "
                            >
                                <component :is="item.icon" class="h-3.5 w-3.5 text-indigo-600" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </div>                 
                    </div>

                    <Link
                        v-for="item in visibleBottomNavItems"
                        :key="item.routeName"
                        :href="route(item.routeName)"
                        class="flex items-center gap-3 px-3 py-3 text-xs font-bold text-left transition-all border-l-4 rounded-xl"
                        :class="
                            route().current(item.routeName)
                                ? 'border-indigo-600 bg-indigo-50 font-extrabold text-indigo-700'
                                : 'border-transparent text-slate-600 hover:bg-slate-50'
                        "
                    >
                        <component :is="item.icon" class="w-4 h-4 text-indigo-600" />
                        <span>{{ item.title }}</span>
                    </Link>
                </nav>
            </div>

            <div class="p-3 mt-auto border-t border-slate-100 bg-slate-50">
                <DropdownMenu>
                    <DropdownMenuTrigger class="flex items-center w-full gap-2 px-2 py-2 text-sm text-left rounded-xl hover:bg-slate-100">
                        <UserInfo :user="admin" show-email />
                        <ChevronsUpDown class="ml-auto size-4 text-slate-400" />
                    </DropdownMenuTrigger>
                    <DropdownMenuContent class="w-56 rounded-lg" side="bottom" align="start" :side-offset="10">
                        <AdminUserMenuContent :admin="admin" :show-identity="false" />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>

        <div class="p-4 text-white border shadow-lg rounded-2xl border-slate-800 bg-slate-900">
            <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-800">
                <h3 class="flex items-center gap-1.5 text-xs font-bold text-indigo-400">
                    <Mail class="w-4 h-4" />
                    {{ t('admin.emailLog.title') }}
                </h3>
                <span class="rounded-full bg-indigo-600 px-2 py-0.5 text-[9px] font-black text-white">{{ page.props.emailLogCount }}</span>
            </div>
            <p class="mb-3 text-[9px] leading-relaxed text-slate-400">{{ t('admin.emailLog.description') }}</p>

            <p v-if="page.props.recentEmailLogs.length === 0" class="py-3 text-center text-[10px] text-slate-500">
                {{ t('admin.emailLog.empty') }}
            </p>
            <div v-else class="max-h-[220px] space-y-2 overflow-y-auto pr-1">
                <div v-for="mail in page.props.recentEmailLogs" :key="mail.id" class="rounded-xl border border-slate-800 bg-slate-800/60 p-3 text-[10px]">
                    <p class="mb-0.5 font-bold text-indigo-300">{{ mail.subject }}</p>
                    <p class="mb-1.5 line-clamp-2 leading-relaxed text-slate-300">{{ mail.body }}</p>

                    <button
                        v-if="mail.recipient_count > 1"
                        type="button"
                        class="flex w-full items-center justify-between border-t border-slate-700/40 pt-1.5 text-[8px] text-slate-500 hover:text-slate-300"
                        @click="toggleEmailLogExpanded(mail.id)"
                    >
                        <span class="flex items-center gap-1">
                            <ChevronDown
                                :class="['h-2.5 w-2.5 transition-transform', expandedEmailLogIds.has(mail.id) && 'rotate-180']"
                            />
                            {{ t('admin.emailLog.sentToCount', { count: mail.recipient_count }) }}
                        </span>
                        <span>{{ formatDate(mail.sent_at, 'DD/MM/YYYY HH:mm') }}</span>
                    </button>
                    <div v-else class="flex items-center justify-between border-t border-slate-700/40 pt-1.5 text-[8px] text-slate-500">
                        <span class="max-w-[120px] truncate">{{ mail.recipients[0] }}</span>
                        <span>{{ formatDate(mail.sent_at, 'DD/MM/YYYY HH:mm') }}</span>
                    </div>

                    <ul
                        v-if="mail.recipient_count > 1 && expandedEmailLogIds.has(mail.id)"
                        class="mt-1.5 max-h-24 space-y-0.5 overflow-y-auto border-t border-slate-700/40 pt-1.5"
                    >
                        <li v-for="email in mail.recipients" :key="email" class="truncate text-slate-400">{{ email }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </aside>
    <slot />
</template>
