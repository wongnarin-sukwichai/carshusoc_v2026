<script setup lang="ts">
import PaymentSlipDialog from '@/components/PaymentSlipDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import UserLayout from '@/layouts/user/UserLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Calendar, GraduationCap } from 'lucide-vue-next';
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
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <Head :title="t('nav.user.exam')" />
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 to-indigo-950 p-6 text-white">
                <GraduationCap class="absolute right-0 bottom-0 h-48 w-48 translate-x-12 translate-y-6 opacity-10" />
                <div class="relative z-10 max-w-2xl">
                    <span class="rounded bg-indigo-500 px-2 py-0.5 text-[9px] font-black tracking-widest text-white uppercase">
                        {{ t('nav.user.exam') }}
                    </span>
                    <h2 class="mt-2 text-xl font-extrabold">{{ t('nav.user.exam') }}</h2>
                    <p class="mt-1 text-xs leading-relaxed text-slate-300">{{ t('user.exam.description') }}</p>
                </div>
            </div>

            <p v-if="exams.length === 0" class="rounded-xl border border-dashed p-8 text-center text-sm text-muted-foreground">
                {{ t('user.exam.empty') }}
            </p>

            <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div v-for="exam in exams" :key="exam.id" class="flex flex-col justify-between rounded-xl border border-slate-200 p-4 shadow-sm">
                    <div>
                        <div class="mb-2 flex items-center justify-between text-xs">
                            <span class="rounded-lg bg-muted px-2 py-0.5 font-bold">{{ exam.type }}</span>
                            <span class="flex items-center gap-1 text-muted-foreground"><Calendar class="h-3 w-3" />{{ exam.exam_date }}</span>
                        </div>
                        <h3 class="font-bold">{{ examName(exam) }}</h3>
                        <p class="mt-1 text-xs text-muted-foreground">{{ t('user.exam.codeLabel', { code: exam.code }) }}</p>
                    </div>

                    <div class="mt-4 border-t pt-3">
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
                                />
                                <Badge v-else-if="registrations[exam.id]?.status === 'scored'" variant="success">
                                    {{
                                        t('user.exam.scoreLabel', {
                                            score: registrations[exam.id].total_score,
                                            cefr: registrations[exam.id].cefr_level,
                                        })
                                    }}
                                </Badge>
                                <Badge v-else-if="registrations[exam.id]?.status === 'registered'" variant="info" class="animate-pulse">{{
                                    t('user.exam.statusRegistered')
                                }}</Badge>
                                <Button v-else size="sm" @click="register(exam)">{{ t('user.exam.register') }}</Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</template>
