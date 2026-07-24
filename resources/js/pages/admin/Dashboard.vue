<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AdminLayout });

interface LanguageBreakdownRow {
    language: string;
    count: number;
    percent: number;
}

defineProps<{
    stats: {
        revenue: number;
        registrants: number;
        enrollments: number;
        translationJobs: number;
    };
    languageBreakdown: LanguageBreakdownRow[];
}>();

const { t } = useI18n();

const barColors = ['bg-indigo-600', 'bg-amber-500', 'bg-emerald-600', 'bg-slate-500'];
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <Head :title="t('nav.admin.dashboard')" />
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <HeadingSmall :title="t('admin.dashboard.title')" :description="t('admin.dashboard.description')" />

                <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-[9px] font-bold tracking-wider text-slate-400 uppercase">{{ t('admin.dashboard.revenue') }}</p>
                        <p class="mt-1 text-lg font-black text-indigo-600">{{ stats.revenue.toLocaleString() }} ฿</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-[9px] font-bold tracking-wider text-slate-400 uppercase">{{ t('admin.dashboard.registrants') }}</p>
                        <p class="mt-1 text-lg font-black text-slate-800">
                            {{ stats.registrants.toLocaleString() }} {{ t('admin.dashboard.registrantsUnit') }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-[9px] font-bold tracking-wider text-slate-400 uppercase">{{ t('admin.dashboard.enrollments') }}</p>
                        <p class="mt-1 text-lg font-black text-emerald-600">
                            {{ stats.enrollments.toLocaleString() }} {{ t('admin.dashboard.enrollmentsUnit') }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-[9px] font-bold tracking-wider text-slate-400 uppercase">{{ t('admin.dashboard.translationJobs') }}</p>
                        <p class="mt-1 text-lg font-black text-amber-600">
                            {{ stats.translationJobs.toLocaleString() }} {{ t('admin.dashboard.translationJobsUnit') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 text-sm font-bold">{{ t('admin.dashboard.languageBreakdownTitle') }}</h3>

                <p v-if="languageBreakdown.length === 0" class="py-6 text-center text-xs text-muted-foreground">
                    {{ t('admin.dashboard.noEnrollmentsYet') }}
                </p>

                <div v-else class="space-y-3">
                    <div v-for="(row, index) in languageBreakdown" :key="row.language">
                        <div class="mb-1 flex items-center justify-between text-xs font-bold">
                            <span>{{ row.language }}</span>
                            <span class="text-muted-foreground">{{ row.count }} {{ t('admin.dashboard.enrollmentsUnit') }}</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                            <div
                                class="h-2 rounded-full"
                                :class="barColors[index % barColors.length]"
                                :style="{ width: `${row.percent}%` }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</template>
