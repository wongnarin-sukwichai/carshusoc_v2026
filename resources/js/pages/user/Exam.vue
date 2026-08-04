<script setup lang="ts">
import ExamRequiredDocumentsModal from '@/components/ExamRequiredDocumentsModal.vue';
import ExamRulesModal from '@/components/ExamRulesModal.vue';
import PaymentSlipDialog from '@/components/PaymentSlipDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import UserLayout from '@/layouts/user/UserLayout.vue';
import { formatDate } from '@/lib/date';
import { Head, router } from '@inertiajs/vue3';
import { Award, Calendar, CalendarClock, FileCheck2, GraduationCap, MapPin, ShieldAlert } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: UserLayout });

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
    mail_delivery_available: boolean;
    mail_delivery_fee: string | null;
}

interface Registration {
    id: number;
    exam_id: number;
    status: 'pending_payment' | 'registered' | 'scored' | 'cancelled';
    total_score: number | null;
    cefr_level: string | null;
}

const props = defineProps<{
    exams: Exam[];
    registrations: Record<number, Registration>;
}>();

const { t, locale } = useI18n();

const examName = (exam: Exam) => (locale.value === 'en' && exam.name_en ? exam.name_en : exam.name_th);

const register = (exam: Exam) => {
    router.post(route('user.exams.register', exam.id), {}, { preserveScroll: true });
};

const showRequiredDocuments = ref(false);
const showExamRules = ref(false);
</script>

<template>
    <div class="flex flex-col flex-1 h-full gap-4 p-4 rounded-xl">
        <Head :title="t('nav.user.exam')" />
            <div class="relative p-6 overflow-hidden text-white rounded-2xl bg-gradient-to-br from-slate-900 to-indigo-950">
                <GraduationCap class="absolute bottom-0 right-0 w-48 h-48 translate-x-12 translate-y-6 opacity-10" />
                <div class="relative z-10 max-w-2xl">
                    <span class="rounded bg-indigo-500 px-2 py-0.5 text-[9px] font-black tracking-widest text-white uppercase">
                        {{ t('nav.user.exam') }}
                    </span>
                    <h2 class="mt-2 text-xl font-extrabold">{{ t('nav.user.exam') }}</h2>
                    <p class="mt-1 text-xs leading-relaxed text-slate-300">{{ t('user.exam.description') }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
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

                    <div class="pt-3 mt-2 border-t">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-muted-foreground">{{ t('user.exam.fee') }}</p>
                                <p class="text-lg font-bold">{{ Number(exam.price).toLocaleString() }} ฿</p>
                            </div>

                            <div>
                                <PaymentSlipDialog
                                    v-if="registrations[exam.id]?.status === 'pending_payment'"
                                    payable-type="exam_registration"
                                    :payable-id="registrations[exam.id].id"
                                    :title="examName(exam)"
                                    :amount="Number(exam.price)"
                                    :mail-delivery-available="exam.mail_delivery_available"
                                    :mail-delivery-fee="exam.mail_delivery_fee ? Number(exam.mail_delivery_fee) : null"
                                    :trigger-label="t('user.exam.uploadSlip')"
                                    @success="showExamRules = true"
                                />
                                <span
                                    v-else-if="registrations[exam.id]?.status === 'scored'"
                                    class="flex items-center gap-1 text-sm font-bold text-emerald-600"
                                >
                                    <Award class="w-4 h-4" />
                                    {{
                                        t('user.exam.scoreLabel', {
                                            score: registrations[exam.id].total_score,
                                            cefr: registrations[exam.id].cefr_level,
                                        })
                                    }}
                                </span>
                                <Badge
                                    v-else-if="registrations[exam.id]?.status === 'registered'"
                                    variant="info"
                                    class="text-white bg-indigo-600 border-indigo-600"
                                    >{{ t('user.exam.statusRegistered') }}</Badge
                                >
                                <Button v-else size="sm" @click="register(exam)">{{ t('user.exam.register') }}</Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <ExamRequiredDocumentsModal v-model:open="showRequiredDocuments" />
        <ExamRulesModal v-model:open="showExamRules" />
    </div>
</template>
