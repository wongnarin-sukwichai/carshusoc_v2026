<script setup lang="ts">
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { type SharedData, type User } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, CheckCircle, ChevronsUpDown, FileText, GraduationCap, type LucideIcon } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

interface NavEntry {
    title: string;
    routeName: string;
    icon: LucideIcon;
    iconClass?: string;
    centerCode?: string;
}

const page = usePage<SharedData>();
const user = page.props.auth.user as User;
const { t } = useI18n();

// centerCode links a nav item to service_centers.code so it can be hidden
// when an admin toggles that center's visibility off (see ServiceCenter model).
const allNavItems = computed<NavEntry[]>(() => [
    { title: t('nav.user.training'), routeName: 'user.training', icon: BookOpen, centerCode: 'training' },
    { title: t('nav.user.exam'), routeName: 'user.exam', icon: GraduationCap, centerCode: 'exam' },
    { title: t('nav.user.translation'), routeName: 'user.translation', icon: FileText, centerCode: 'translation' },
    { title: t('nav.user.portfolio'), routeName: 'user.portfolio', icon: CheckCircle, iconClass: 'text-emerald-600' },
]);

const mainNavItems = computed(() =>
    allNavItems.value.filter((item) => !item.centerCode || page.props.serviceCenters[item.centerCode] !== false),
);
</script>

<template>
    <aside class="flex flex-col gap-6 lg:col-span-4">
        <div class="flex flex-col overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
            <div class="p-4 space-y-1">
                <span class="mb-2 block px-2 text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{ t('nav.user.groupLabel') }}</span>
                <nav class="space-y-1.5">
                    <Link
                        v-for="item in mainNavItems"
                        :key="item.routeName"
                        :href="route(item.routeName)"
                        class="flex items-center gap-3 px-3 py-3 text-xs font-bold text-left transition-all border-l-4 rounded-xl"
                        :class="
                            route().current(item.routeName)
                                ? 'border-indigo-600 bg-indigo-50 font-extrabold text-indigo-700'
                                : 'border-transparent text-slate-600 hover:bg-slate-50'
                        "
                    >
                        <component :is="item.icon" class="w-4 h-4" :class="item.iconClass ?? 'text-indigo-600'" />
                        <span>{{ item.title }}</span>
                    </Link>
                </nav>
            </div>
            <div class="p-3 mt-auto border-t border-slate-100 bg-slate-50">
                <DropdownMenu>
                    <DropdownMenuTrigger
                        class="flex items-center w-full gap-2 px-2 py-2 text-sm text-left rounded-xl hover:bg-slate-100"
                    >
                        <UserInfo :user="user" show-email />
                        <ChevronsUpDown class="ml-auto size-4 text-slate-400" />
                    </DropdownMenuTrigger>
                    <DropdownMenuContent class="w-56 rounded-lg" side="bottom" align="start" :side-offset="10">
                        <UserMenuContent :user="user" :show-identity="false" />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
    </aside>
    <slot />
</template>
