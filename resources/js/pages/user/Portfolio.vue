<script setup lang="ts">
import { Button } from '@/components/ui/button';
import UserLayout from '@/layouts/user/UserLayout.vue';
import { formatDate } from '@/lib/date';
import { Head } from '@inertiajs/vue3';
import { Award, Download, ShieldCheck } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: UserLayout });

interface CertificateRow {
    id: number;
    kind: 'course' | 'exam';
    title: string;
    issued_at: string;
}

const props = defineProps<{
    certificates: CertificateRow[];
}>();

const { t } = useI18n();

const courseCerts = () => props.certificates.filter((c) => c.kind === 'course');
const examCerts = () => props.certificates.filter((c) => c.kind === 'exam');
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <Head :title="t('nav.user.portfolio')" />
            <img src="/images/banner.png" alt="" class="w-full h-auto rounded-2xl" />
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-800 to-indigo-900 p-6 text-white">
                <ShieldCheck class="absolute right-0 bottom-0 h-48 w-48 translate-x-12 translate-y-6 opacity-10" />
                <div class="relative z-10 max-w-2xl">
                    <span class="rounded bg-indigo-500 px-2 py-0.5 text-[9px] font-black tracking-widest text-white uppercase">
                        {{ t('nav.user.portfolio') }}
                    </span>
                    <h2 class="mt-2 text-xl font-extrabold">{{ t('nav.user.portfolio') }}</h2>
                    <p class="mt-1 text-xs leading-relaxed text-slate-300">{{ t('user.portfolio.description') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 p-4 shadow-sm">
                    <h3 class="mb-3 flex items-center gap-1 text-sm font-bold">
                        <Award class="h-4 w-4" /> {{ t('user.portfolio.courseCertsTitle') }}
                    </h3>
                    <p v-if="courseCerts().length === 0" class="py-6 text-center text-xs italic text-muted-foreground">
                        {{ t('user.portfolio.noCourseCerts') }}
                    </p>
                    <div v-else class="space-y-2">
                        <div
                            v-for="cert in courseCerts()"
                            :key="cert.id"
                            class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3 text-sm"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                    <Award class="h-4 w-4" />
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate font-medium">{{ cert.title }}</p>
                                    <p class="text-xs text-muted-foreground">{{ t('user.portfolio.issuedOn', { date: formatDate(cert.issued_at) }) }}</p>
                                </div>
                            </div>
                            <Button
                                as-child
                                size="sm"
                                class="shrink-0 border-transparent bg-[#217346] text-white hover:bg-[#1a5c38] hover:text-white"
                            >
                                <a :href="route('user.certificates.download', cert.id)"
                                    ><Download class="mr-1 h-3 w-3" />{{ t('user.portfolio.download') }}</a
                                >
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 p-4 shadow-sm">
                    <h3 class="mb-3 flex items-center gap-1 text-sm font-bold">
                        <Award class="h-4 w-4" /> {{ t('user.portfolio.examCertsTitle') }}
                    </h3>
                    <p v-if="examCerts().length === 0" class="py-6 text-center text-xs italic text-muted-foreground">
                        {{ t('user.portfolio.noExamCerts') }}
                    </p>
                    <div v-else class="space-y-2">
                        <div
                            v-for="cert in examCerts()"
                            :key="cert.id"
                            class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3 text-sm"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-violet-100 text-violet-600">
                                    <Award class="h-4 w-4" />
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate font-medium">{{ cert.title }}</p>
                                    <p class="text-xs text-muted-foreground">{{ t('user.portfolio.issuedOn', { date: formatDate(cert.issued_at) }) }}</p>
                                </div>
                            </div>
                            <Button
                                as-child
                                size="sm"
                                class="shrink-0 border-transparent bg-[#217346] text-white hover:bg-[#1a5c38] hover:text-white"
                            >
                                <a :href="route('user.certificates.download', cert.id)"
                                    ><Download class="mr-1 h-3 w-3" />{{ t('user.portfolio.download') }}</a
                                >
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</template>
