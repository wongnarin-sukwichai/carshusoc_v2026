<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AdminLayout });

interface ExamOption {
    id: number;
    code: string;
    name_th: string;
    type: string;
}

interface RegistrationRow {
    id: number;
    user_name: string;
    user_email: string;
    status: string;
    listening_score: number | null;
    reading_score: number | null;
    conversation_score: number | null;
    grammar_score: number | null;
    total_score: number | null;
    cefr_level: string | null;
    certificate_issued: boolean;
}

interface ScoreDraft {
    listening_score: number;
    reading_score: number;
    conversation_score: number;
    grammar_score: number;
}

const props = defineProps<{
    exams: ExamOption[];
    selectedExamId: number | null;
    registrations: RegistrationRow[];
}>();

const { t } = useI18n();

const scoreDrafts = reactive<Record<number, ScoreDraft>>({});

watch(
    () => props.registrations,
    (registrations) => {
        registrations.forEach((r) => {
            scoreDrafts[r.id] = {
                listening_score: r.listening_score ?? 0,
                reading_score: r.reading_score ?? 0,
                conversation_score: r.conversation_score ?? 0,
                grammar_score: r.grammar_score ?? 0,
            };
        });
    },
    { immediate: true },
);

const changeExam = (event: Event) => {
    const examId = (event.target as HTMLSelectElement).value;
    router.get(route('admin.exam-scores'), { exam: examId }, { preserveState: true });
};

const saveScore = (registrationId: number) => {
    router.put(route('admin.exam-registrations.update', registrationId), scoreDrafts[registrationId], { preserveScroll: true });
};

const csvForm = useForm({ csv: '' });

const importCsv = () => {
    if (!props.selectedExamId) return;

    csvForm.post(route('admin.exams.import-scores', props.selectedExamId), {
        preserveScroll: true,
        onSuccess: () => csvForm.reset(),
    });
};
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <Head :title="t('nav.admin.examScores')" />
            <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
                <HeadingSmall :title="t('admin.examScores.title')" :description="t('admin.examScores.description')" />
                <select class="h-9 rounded-md border bg-background px-3 text-sm" :value="selectedExamId ?? ''" @change="changeExam">
                    <option v-for="exam in exams" :key="exam.id" :value="exam.id">{{ exam.name_th }} ({{ exam.type }})</option>
                </select>
            </div>

            <div v-if="registrations.length === 0" class="rounded-xl border border-dashed p-8 text-center text-sm text-muted-foreground">
                {{ t('admin.examScores.empty') }}
            </div>

            <div v-else class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-100">
                        <tr>
                            <th class="p-2">{{ t('admin.examScores.colExaminee') }}</th>
                            <th class="p-2 text-center">Listening</th>
                            <th class="p-2 text-center">Reading</th>
                            <th class="p-2 text-center">Conversation</th>
                            <th class="p-2 text-center">Grammar</th>
                            <th class="p-2 text-center">{{ t('admin.examScores.colTotal') }}</th>
                            <th class="p-2 text-center">CEFR</th>
                            <th class="p-2 text-center">{{ t('admin.examScores.colSave') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="r in registrations" :key="r.id" class="border-t border-slate-200">
                            <td class="p-2">
                                <p class="font-medium">{{ r.user_name }}</p>
                                <p class="font-mono text-xs text-muted-foreground">{{ r.user_email }}</p>
                            </td>
                            <td class="p-2">
                                <input
                                    v-if="scoreDrafts[r.id]"
                                    v-model.number="scoreDrafts[r.id].listening_score"
                                    type="number"
                                    min="0"
                                    max="25"
                                    class="w-16 rounded border px-2 py-1 text-center"
                                />
                            </td>
                            <td class="p-2">
                                <input
                                    v-if="scoreDrafts[r.id]"
                                    v-model.number="scoreDrafts[r.id].reading_score"
                                    type="number"
                                    min="0"
                                    max="25"
                                    class="w-16 rounded border px-2 py-1 text-center"
                                />
                            </td>
                            <td class="p-2">
                                <input
                                    v-if="scoreDrafts[r.id]"
                                    v-model.number="scoreDrafts[r.id].conversation_score"
                                    type="number"
                                    min="0"
                                    max="25"
                                    class="w-16 rounded border px-2 py-1 text-center"
                                />
                            </td>
                            <td class="p-2">
                                <input
                                    v-if="scoreDrafts[r.id]"
                                    v-model.number="scoreDrafts[r.id].grammar_score"
                                    type="number"
                                    min="0"
                                    max="25"
                                    class="w-16 rounded border px-2 py-1 text-center"
                                />
                            </td>
                            <td class="p-2 text-center font-mono">{{ r.total_score ?? '-' }}</td>
                            <td class="p-2 text-center font-mono">{{ r.cefr_level ?? '-' }}</td>
                            <td class="p-2 text-center"><Button size="sm" @click="saveScore(r.id)">{{ t('admin.examScores.save') }}</Button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="selectedExamId" class="space-y-3 rounded-xl border border-slate-200 p-4">
                <h4 class="text-sm font-bold">{{ t('admin.examScores.csvImportTitle') }}</h4>
                <p class="text-xs text-muted-foreground">
                    {{ t('admin.examScores.csvFormat') }} <code class="rounded bg-muted px-1">email,listening,reading,conversation,grammar</code>
                </p>
                <Textarea v-model="csvForm.csv" rows="4" placeholder="somchai.dev@gmail.com,20,22,18,21" />
                <Button :disabled="csvForm.processing" @click="importCsv">{{ t('admin.examScores.importCsv') }}</Button>
            </div>
    </div>
</template>
