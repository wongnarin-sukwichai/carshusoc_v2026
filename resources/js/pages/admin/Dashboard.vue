<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Award, LayoutDashboard, TrendingUp } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AdminLayout });

interface LanguageBreakdownRow {
    language: string;
    count: number;
    percent: number;
}

interface RevenueTrendPoint {
    month: string;
    total: number;
}

interface RevenueByCenterRow {
    center: 'training' | 'exam' | 'translation';
    total: number;
}

interface PaymentStatusRow {
    status: 'pending' | 'approved' | 'rejected';
    count: number;
}

interface CefrRow {
    level: string;
    count: number;
}

const props = defineProps<{
    stats: {
        revenue: number;
        registrants: number;
        enrollments: number;
        examRegistrations: number;
        translationJobs: number;
        certificatesIssued: number;
    };
    languageBreakdown: LanguageBreakdownRow[];
    revenueTrend: RevenueTrendPoint[];
    revenueByCenter: RevenueByCenterRow[];
    paymentStatusBreakdown: PaymentStatusRow[];
    cefrDistribution: CefrRow[];
}>();

const { t } = useI18n();

// ---------- language breakdown (existing chart) ----------
// Fixed by language identity, not by array position — a filter or re-sort
// must never repaint which color a given language carries.
const languageColorMap: Record<string, string> = {
    อังกฤษ: 'bg-indigo-600',
    ไทย: 'bg-emerald-600',
    ลาว: 'bg-amber-500',
};
const languageColor = (language: string) => languageColorMap[language] ?? 'bg-slate-500';

// ---------- revenue trend (line chart) ----------
const CHART_WIDTH = 600;
const CHART_HEIGHT = 160;
const CHART_PAD_X = 12;
const CHART_PAD_TOP = 16;
const CHART_PAD_BOTTOM = 28;

const trendMax = computed(() => Math.max(...props.revenueTrend.map((p) => p.total), 1));

const trendPoints = computed(() => {
    const innerWidth = CHART_WIDTH - CHART_PAD_X * 2;
    const innerHeight = CHART_HEIGHT - CHART_PAD_TOP - CHART_PAD_BOTTOM;
    const n = props.revenueTrend.length;

    return props.revenueTrend.map((point, index) => {
        const x = n <= 1 ? CHART_WIDTH / 2 : CHART_PAD_X + (innerWidth * index) / (n - 1);
        const y = CHART_PAD_TOP + innerHeight * (1 - point.total / trendMax.value);

        return { ...point, x, y };
    });
});

const trendLinePath = computed(() => trendPoints.value.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' '));

const trendAreaPath = computed(() => {
    if (trendPoints.value.length === 0) return '';

    const baseline = CHART_HEIGHT - CHART_PAD_BOTTOM;
    const first = trendPoints.value[0];
    const last = trendPoints.value[trendPoints.value.length - 1];

    return `${trendLinePath.value} L ${last.x} ${baseline} L ${first.x} ${baseline} Z`;
});

const monthLabel = (month: string) => {
    const [year, monthNumber] = month.split('-');
    return `${monthNumber}/${year.slice(2)}`;
};

const hoveredTrendIndex = ref<number | null>(null);
const hoveredTrendPoint = computed(() => (hoveredTrendIndex.value === null ? null : trendPoints.value[hoveredTrendIndex.value]));

// ---------- revenue by service center ----------
const centerColor: Record<RevenueByCenterRow['center'], string> = {
    training: 'bg-blue-600',
    exam: 'bg-violet-600',
    translation: 'bg-amber-500',
};

const centerLabel = computed<Record<RevenueByCenterRow['center'], string>>(() => ({
    training: t('admin.dashboard.centerTraining'),
    exam: t('admin.dashboard.centerExam'),
    translation: t('admin.dashboard.centerTranslation'),
}));

const revenueByCenterMax = computed(() => Math.max(...props.revenueByCenter.map((r) => r.total), 1));
const revenueByCenterTotal = computed(() => props.revenueByCenter.reduce((sum, r) => sum + r.total, 0));

// ---------- payment status breakdown (stacked bar) ----------
const statusColor: Record<PaymentStatusRow['status'], string> = {
    pending: 'bg-amber-500',
    approved: 'bg-emerald-600',
    rejected: 'bg-red-500',
};

const statusLabel = computed<Record<PaymentStatusRow['status'], string>>(() => ({
    pending: t('admin.payments.statusPending'),
    approved: t('admin.payments.statusApproved'),
    rejected: t('admin.payments.statusRejected'),
}));

const paymentStatusTotal = computed(() => Math.max(props.paymentStatusBreakdown.reduce((sum, r) => sum + r.count, 0), 1));

// ---------- CEFR distribution ----------
const cefrMax = computed(() => Math.max(...props.cefrDistribution.map((r) => r.count), 1));
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <Head :title="t('nav.admin.dashboard')" />

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="border-b border-slate-100 pb-2">
                <h2 class="flex items-center gap-1.5 text-sm font-bold text-slate-800">
                    <LayoutDashboard class="h-4 w-4 text-indigo-600" />
                    {{ t('admin.dashboard.title') }}
                </h2>
                <p class="text-[10px] text-slate-500">{{ t('admin.dashboard.description') }}</p>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
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
                    <p class="mt-1 text-lg font-black text-blue-600">
                        {{ stats.enrollments.toLocaleString() }} {{ t('admin.dashboard.enrollmentsUnit') }}
                    </p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[9px] font-bold tracking-wider text-slate-400 uppercase">{{ t('admin.dashboard.examRegistrations') }}</p>
                    <p class="mt-1 text-lg font-black text-violet-600">
                        {{ stats.examRegistrations.toLocaleString() }} {{ t('admin.dashboard.examRegistrationsUnit') }}
                    </p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[9px] font-bold tracking-wider text-slate-400 uppercase">{{ t('admin.dashboard.translationJobs') }}</p>
                    <p class="mt-1 text-lg font-black text-amber-600">
                        {{ stats.translationJobs.toLocaleString() }} {{ t('admin.dashboard.translationJobsUnit') }}
                    </p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[9px] font-bold tracking-wider text-slate-400 uppercase">{{ t('admin.dashboard.certificatesIssued') }}</p>
                    <p class="mt-1 text-lg font-black text-emerald-600">
                        {{ stats.certificatesIssued.toLocaleString() }} {{ t('admin.dashboard.certificatesIssuedUnit') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Revenue trend -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-3 flex items-center gap-1.5 text-sm font-bold text-slate-800">
                <TrendingUp class="h-4 w-4 text-indigo-600" />
                {{ t('admin.dashboard.revenueTrendTitle') }}
            </h3>

            <p v-if="revenueTrend.every((p) => p.total === 0)" class="py-6 text-center text-xs text-muted-foreground">
                {{ t('admin.dashboard.noRevenueYet') }}
            </p>

            <div v-else class="relative">
                <svg :viewBox="`0 0 ${CHART_WIDTH} ${CHART_HEIGHT}`" class="w-full" preserveAspectRatio="none">
                    <path :d="trendAreaPath" fill="rgb(79 70 229 / 0.1)" stroke="none" />
                    <path :d="trendLinePath" fill="none" stroke="rgb(79 70 229)" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />

                    <line
                        v-if="hoveredTrendPoint"
                        :x1="hoveredTrendPoint.x"
                        :y1="16"
                        :x2="hoveredTrendPoint.x"
                        :y2="CHART_HEIGHT - CHART_PAD_BOTTOM"
                        stroke="rgb(148 163 184)"
                        stroke-width="1"
                    />

                    <circle
                        :cx="trendPoints[trendPoints.length - 1].x"
                        :cy="trendPoints[trendPoints.length - 1].y"
                        r="4"
                        fill="rgb(79 70 229)"
                        stroke="white"
                        stroke-width="2"
                    />
                    <circle
                        v-if="hoveredTrendPoint"
                        :cx="hoveredTrendPoint.x"
                        :cy="hoveredTrendPoint.y"
                        r="4"
                        fill="rgb(79 70 229)"
                        stroke="white"
                        stroke-width="2"
                    />

                    <text
                        v-for="(point, index) in trendPoints"
                        :key="`label-${index}`"
                        :x="point.x"
                        :y="CHART_HEIGHT - 8"
                        text-anchor="middle"
                        class="fill-slate-400"
                        font-size="9"
                    >
                        {{ monthLabel(point.month) }}
                    </text>

                    <rect
                        v-for="(point, index) in trendPoints"
                        :key="`hit-${index}`"
                        :x="CHART_WIDTH * (index / trendPoints.length)"
                        y="0"
                        :width="CHART_WIDTH / trendPoints.length"
                        :height="CHART_HEIGHT - CHART_PAD_BOTTOM"
                        fill="transparent"
                        @mouseenter="hoveredTrendIndex = index"
                        @mouseleave="hoveredTrendIndex = null"
                        @focus="hoveredTrendIndex = index"
                        @blur="hoveredTrendIndex = null"
                        tabindex="0"
                    />
                </svg>

                <div
                    v-if="hoveredTrendPoint"
                    class="pointer-events-none absolute -translate-x-1/2 -translate-y-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[10px] shadow-md"
                    :style="{ left: `${(hoveredTrendPoint.x / CHART_WIDTH) * 100}%`, top: `${(hoveredTrendPoint.y / CHART_HEIGHT) * 100}%` }"
                >
                    <p class="font-mono text-slate-400">{{ monthLabel(hoveredTrendPoint.month) }}</p>
                    <p class="font-bold text-indigo-600">{{ hoveredTrendPoint.total.toLocaleString() }} ฿</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <!-- Revenue by service center -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 text-sm font-bold text-slate-800">{{ t('admin.dashboard.revenueByCenterTitle') }}</h3>

                <p v-if="revenueByCenterTotal === 0" class="py-6 text-center text-xs text-muted-foreground">
                    {{ t('admin.dashboard.noRevenueYet') }}
                </p>

                <div v-else class="space-y-3">
                    <div v-for="row in revenueByCenter" :key="row.center">
                        <div class="mb-1 flex items-center justify-between text-xs font-bold">
                            <span class="flex items-center gap-1.5">
                                <span :class="['h-2.5 w-2.5 rounded-full', centerColor[row.center]]"></span>
                                {{ centerLabel[row.center] }}
                            </span>
                            <span class="text-muted-foreground">{{ row.total.toLocaleString() }} ฿</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                            <div
                                class="h-2 rounded-full transition-all"
                                :class="centerColor[row.center]"
                                :style="{ width: `${(row.total / revenueByCenterMax) * 100}%` }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment status breakdown -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 text-sm font-bold text-slate-800">{{ t('admin.dashboard.paymentStatusTitle') }}</h3>

                <p v-if="paymentStatusTotal <= 1 && paymentStatusBreakdown.every((r) => r.count === 0)" class="py-6 text-center text-xs text-muted-foreground">
                    {{ t('admin.dashboard.noPaymentsYet') }}
                </p>

                <template v-else>
                    <div class="flex h-4 gap-[2px] overflow-hidden rounded-full">
                        <div
                            v-for="row in paymentStatusBreakdown"
                            :key="row.status"
                            :class="statusColor[row.status]"
                            :title="`${statusLabel[row.status]}: ${row.count}`"
                            :style="{ width: `${(row.count / paymentStatusTotal) * 100}%` }"
                        ></div>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5">
                        <span v-for="row in paymentStatusBreakdown" :key="row.status" class="flex items-center gap-1.5 text-xs">
                            <span :class="['h-2.5 w-2.5 rounded-full', statusColor[row.status]]"></span>
                            <span class="font-bold text-slate-700">{{ row.count }}</span>
                            <span class="text-muted-foreground">{{ statusLabel[row.status] }}</span>
                        </span>
                    </div>
                </template>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <!-- CEFR distribution -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 flex items-center gap-1.5 text-sm font-bold text-slate-800">
                    <Award class="h-4 w-4 text-indigo-600" />
                    {{ t('admin.dashboard.cefrDistributionTitle') }}
                </h3>

                <p v-if="cefrDistribution.length === 0" class="py-6 text-center text-xs text-muted-foreground">
                    {{ t('admin.dashboard.noCefrDataYet') }}
                </p>

                <div v-else class="space-y-2.5">
                    <div v-for="row in cefrDistribution" :key="row.level" class="flex items-center gap-2">
                        <span class="w-7 shrink-0 font-mono text-xs font-bold text-slate-600">{{ row.level }}</span>
                        <div class="h-4 flex-1 overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="flex h-4 items-center justify-end rounded-full bg-indigo-600 pr-1.5 transition-all"
                                :style="{ width: `${Math.max((row.count / cefrMax) * 100, 12)}%` }"
                            >
                                <span class="text-[9px] font-bold text-white">{{ row.count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Language breakdown -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 text-sm font-bold text-slate-800">{{ t('admin.dashboard.languageBreakdownTitle') }}</h3>

                <p v-if="languageBreakdown.length === 0" class="py-6 text-center text-xs text-muted-foreground">
                    {{ t('admin.dashboard.noEnrollmentsYet') }}
                </p>

                <div v-else class="space-y-3">
                    <div v-for="row in languageBreakdown" :key="row.language">
                        <div class="mb-1 flex items-center justify-between text-xs font-bold">
                            <span class="flex items-center gap-1.5">
                                <span :class="['h-2.5 w-2.5 rounded-full', languageColor(row.language)]"></span>
                                {{ row.language }}
                            </span>
                            <span class="text-muted-foreground">{{ row.count }} {{ t('admin.dashboard.enrollmentsUnit') }}</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                            <div
                                class="h-2 rounded-full transition-all"
                                :class="languageColor(row.language)"
                                :style="{ width: `${row.percent}%` }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
