<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { confirmDialog } from '@/lib/swal';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ClipboardCheck, Download, FileSpreadsheet, Search } from 'lucide-vue-next';
import { computed, reactive, ref, watch } from 'vue';
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
    room: string | null;
    seat_number: string | null;
    listening_score: number | null;
    reading_score: number | null;
    conversation_score: number | null;
    grammar_score: number | null;
    total_score: number | null;
    cefr_level: string | null;
    certificate_issued: boolean;
}

interface ScoreDraft {
    room: string;
    seat_number: string;
    listening_score: number;
    reading_score: number;
    conversation_score: number;
    grammar_score: number;
}

interface ImportPreviewRow {
    row: number;
    email: string;
    listening: string;
    reading: string;
    conversation: string;
    grammar: string;
    room: string;
    seat_number: string;
    errors: string[];
    valid: boolean;
}

interface ImportPreview {
    rows: ImportPreviewRow[];
    validCount: number;
    invalidCount: number;
}

const props = defineProps<{
    exams: ExamOption[];
    selectedExamId: number | null;
    registrations: RegistrationRow[];
    importPreview?: ImportPreview | null;
}>();

const { t } = useI18n();

const scoreDrafts = reactive<Record<number, ScoreDraft>>({});

watch(
    () => props.registrations,
    (registrations) => {
        registrations.forEach((r) => {
            scoreDrafts[r.id] = {
                room: r.room ?? '',
                seat_number: r.seat_number ?? '',
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

const search = ref('');

const filteredRegistrations = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) return props.registrations;

    return props.registrations.filter((r) => r.user_name.toLowerCase().includes(query) || r.user_email.toLowerCase().includes(query));
});

const importForm = useForm<{ file: File | null }>({ file: null });
const fileInput = ref<HTMLInputElement | null>(null);

const onFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    importForm.file = input.files?.[0] ?? null;
};

const downloadTemplate = () => {
    if (!props.selectedExamId) return;
    window.location.href = route('admin.exams.score-template', props.selectedExamId);
};

const exportRegistrants = async () => {
    if (!props.selectedExamId) return;

    const confirmed = await confirmDialog({
        title: t('common.areYouSure'),
        text: t('admin.examScores.exportConfirmText'),
        icon: 'question',
        confirmButtonText: t('admin.examScores.export'),
    });

    if (!confirmed) return;

    window.location.href = route('admin.exams.export-registrants', props.selectedExamId);
};

const pendingAction = ref<'validate' | 'save' | null>(null);

const showLoadingModal = ref(false);
const loadingProgress = ref(0);
const loadingLabel = ref('');
let loadingHideTimer: number | undefined;

const loadingTransitionDuration = computed(() => (loadingProgress.value >= 100 ? '250ms' : '3000ms'));

const startLoadingModal = (label: string) => {
    window.clearTimeout(loadingHideTimer);
    loadingLabel.value = label;
    loadingProgress.value = 0;
    showLoadingModal.value = true;
    // Kick off on the next frame so the 0% state actually paints before the
    // width transition to 90% starts — otherwise the browser coalesces both
    // style changes into one and the bar never appears to move.
    requestAnimationFrame(() => {
        loadingProgress.value = 90;
    });
};

const finishLoadingModal = () => {
    loadingProgress.value = 100;
    loadingHideTimer = window.setTimeout(() => {
        showLoadingModal.value = false;
    }, 400);
};

const validateFile = () => {
    if (!importForm.file || !props.selectedExamId) return;

    pendingAction.value = 'validate';
    startLoadingModal(t('admin.examScores.validating'));
    importForm.post(route('admin.exams.import-scores.validate', props.selectedExamId), {
        preserveScroll: true,
        onFinish: () => {
            pendingAction.value = null;
            finishLoadingModal();
        },
    });
};

const canSaveImport = computed(() => !!props.importPreview && props.importPreview.invalidCount === 0 && props.importPreview.validCount > 0);

const saveImport = () => {
    if (!importForm.file || !props.selectedExamId || !canSaveImport.value) return;

    pendingAction.value = 'save';
    startLoadingModal(t('admin.examScores.savingImport'));
    importForm.post(route('admin.exams.import-scores', props.selectedExamId), {
        preserveScroll: true,
        onSuccess: () => {
            importForm.reset();
            if (fileInput.value) fileInput.value.value = '';
        },
        onFinish: () => {
            pendingAction.value = null;
            finishLoadingModal();
        },
    });
};

const invalidPreviewRows = computed(() => props.importPreview?.rows.filter((row) => !row.valid) ?? []);
</script>

<template>
    <div class="flex flex-col flex-1 h-full gap-4 p-4 rounded-xl">
        <Head :title="t('nav.admin.examScores')" />

        <div class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200">
        <div class="flex flex-col justify-between gap-3 pb-2 border-b border-slate-100 md:flex-row md:items-center">
            <div>
                <h2 class="flex items-center gap-1.5 text-sm font-bold text-slate-800">
                    <ClipboardCheck class="w-4 h-4 text-indigo-600" />
                    {{ t('admin.examScores.title') }}
                </h2>
                <p class="text-[10px] text-slate-500">{{ t('admin.examScores.description') }}</p>
            </div>
            <select class="px-3 text-sm border rounded-md h-9 bg-background" :value="selectedExamId ?? ''" @change="changeExam">
                <option v-for="exam in exams" :key="exam.id" :value="exam.id">{{ exam.name_th }} ({{ exam.type }})</option>
            </select>
        </div>

        <div class="flex flex-col gap-4 mt-4">

        <div v-if="selectedExamId" class="p-4 space-y-3 border rounded-xl border-slate-200">
            <h4 class="text-sm font-bold">{{ t('admin.examScores.importTitle') }}</h4>
            <p class="text-xs text-muted-foreground">
                {{ t('admin.examScores.importDescription') }}
                <code class="px-1 rounded bg-muted">email, room, seat_number, listening, reading, conversation, grammar</code>
            </p>

            <div class="flex flex-wrap items-center gap-2">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="border-transparent bg-[#217346] text-white hover:bg-[#1a5c38] hover:text-white"
                    @click="downloadTemplate"
                >
                    <Download class="w-4 h-4" />
                    {{ t('admin.examScores.downloadTemplate') }}
                </Button>

                <input
                    ref="fileInput"
                    type="file"
                    accept=".csv,.txt,.xlsx,.xls"
                    class="text-xs file:mr-3 file:rounded-md file:border file:bg-background file:px-3 file:py-1.5 file:text-xs"
                    @change="onFileChange"
                />

                <Button type="button" size="sm" variant="outline" :disabled="!importForm.file || importForm.processing" @click="validateFile">
                    {{ t('admin.examScores.validateFile') }}
                </Button>

                <Button type="button" size="sm" :disabled="!canSaveImport || importForm.processing" @click="saveImport">
                    {{ t('admin.examScores.saveImport') }}
                </Button>
            </div>

            <div
                v-if="importPreview"
                class="p-3 space-y-2 border rounded-lg"
                :class="importPreview.invalidCount === 0 ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50'"
            >
                <p class="text-sm font-medium" :class="importPreview.invalidCount === 0 ? 'text-emerald-800' : 'text-amber-800'">
                    {{ t('admin.examScores.previewSummary', { valid: importPreview.validCount, invalid: importPreview.invalidCount }) }}
                </p>
                <ul v-if="invalidPreviewRows.length > 0" class="space-y-1 overflow-y-auto text-xs max-h-48">
                    <li v-for="row in invalidPreviewRows" :key="row.row" class="text-red-700">
                        {{ t('admin.examScores.previewRow', { row: row.row }) }}: {{ row.errors.join(', ') }}
                    </li>
                </ul>
            </div>
        </div>

        <div class="flex items-center justify-between gap-2">
            <div class="relative">
                <Search class="absolute top-1/2 left-2.5 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                <Input v-model="search" type="text" :placeholder="t('admin.examScores.searchPlaceholder')" class="max-w-xs pl-8 text-xs" />
            </div>

            <Button
                v-if="selectedExamId"
                type="button"
                size="sm"
                variant="outline"
                class="border-[#217346] text-[#217346] hover:bg-[#217346]/10 hover:text-[#217346]"
                @click="exportRegistrants"
            >
                <FileSpreadsheet class="w-4 h-4" />
                {{ t('admin.examScores.export') }}
            </Button>
        </div>

        <Transition name="fade" mode="out-in">
            <div :key="selectedExamId ?? 'none'">
                <div v-if="registrations.length === 0" class="p-8 text-sm text-center border border-dashed rounded-xl text-muted-foreground">
                    {{ t('admin.examScores.empty') }}
                </div>

                <div
                    v-else-if="filteredRegistrations.length === 0"
                    class="p-8 text-sm text-center border border-dashed rounded-xl text-muted-foreground"
                >
                    {{ t('admin.examScores.noSearchResults') }}
                </div>

                <div v-else class="overflow-x-auto border rounded-xl border-slate-200">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="p-2">{{ t('admin.examScores.colExaminee') }}</th>
                                <th class="p-2 text-center">{{ t('admin.examScores.colRoom') }}</th>
                                <th class="p-2 text-center">{{ t('admin.examScores.colSeatNumber') }}</th>
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
                            <tr v-for="r in filteredRegistrations" :key="r.id" class="border-t border-slate-200">
                                <td class="p-2">
                                    <p class="font-medium">{{ r.user_name }}</p>
                                    <p class="font-mono text-xs text-muted-foreground">{{ r.user_email }}</p>
                                </td>
                                <td class="p-2">
                                    <input
                                        v-if="scoreDrafts[r.id]"
                                        v-model="scoreDrafts[r.id].room"
                                        type="text"
                                        class="w-20 px-2 py-1 text-center border rounded"
                                    />
                                </td>
                                <td class="p-2">
                                    <input
                                        v-if="scoreDrafts[r.id]"
                                        v-model="scoreDrafts[r.id].seat_number"
                                        type="text"
                                        class="w-20 px-2 py-1 text-center border rounded"
                                    />
                                </td>
                                <td class="p-2">
                                    <input
                                        v-if="scoreDrafts[r.id]"
                                        v-model.number="scoreDrafts[r.id].listening_score"
                                        type="number"
                                        min="0"
                                        max="25"
                                        class="w-16 px-2 py-1 text-center border rounded"
                                    />
                                </td>
                                <td class="p-2">
                                    <input
                                        v-if="scoreDrafts[r.id]"
                                        v-model.number="scoreDrafts[r.id].reading_score"
                                        type="number"
                                        min="0"
                                        max="25"
                                        class="w-16 px-2 py-1 text-center border rounded"
                                    />
                                </td>
                                <td class="p-2">
                                    <input
                                        v-if="scoreDrafts[r.id]"
                                        v-model.number="scoreDrafts[r.id].conversation_score"
                                        type="number"
                                        min="0"
                                        max="25"
                                        class="w-16 px-2 py-1 text-center border rounded"
                                    />
                                </td>
                                <td class="p-2">
                                    <input
                                        v-if="scoreDrafts[r.id]"
                                        v-model.number="scoreDrafts[r.id].grammar_score"
                                        type="number"
                                        min="0"
                                        max="25"
                                        class="w-16 px-2 py-1 text-center border rounded"
                                    />
                                </td>
                                <td class="p-2 font-mono text-center">{{ r.total_score ?? '-' }}</td>
                                <td class="p-2 font-mono text-center">{{ r.cefr_level ?? '-' }}</td>
                                <td class="p-2 text-center"><Button size="sm" @click="saveScore(r.id)">{{ t('admin.examScores.save') }}</Button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </Transition>
        </div>
        </div>

        <Teleport to="body">
            <Transition name="fade">
                <div v-if="showLoadingModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
                    <div class="w-full max-w-sm p-6 shadow-lg rounded-xl bg-background">
                        <p class="mb-3 text-sm font-medium text-center">{{ loadingLabel }}</p>
                        <div class="w-full h-2 overflow-hidden rounded-full bg-slate-200">
                            <div
                                class="h-full rounded-full bg-primary"
                                :style="{
                                    width: loadingProgress + '%',
                                    transitionProperty: 'width',
                                    transitionDuration: loadingTransitionDuration,
                                    transitionTimingFunction: 'ease-out',
                                }"
                            />
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
