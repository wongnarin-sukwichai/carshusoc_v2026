<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import { Badge, type BadgeVariants } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { confirmDialog } from '@/lib/swal';
import { type Paginated } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Lock, Plus, Power, Ruler, Trash2, X } from 'lucide-vue-next';
import { reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AdminLayout });

interface BandRow {
    id?: number;
    cefr_level: string;
    toeic_min: number | null;
    toeic_max: number | null;
    ept_min: number | null;
    ept_max: number | null;
}

interface ScoreScaleRow {
    id: number;
    name: string;
    version: number;
    is_active: boolean;
    effective_from: string;
    in_use: boolean;
    bands: BandRow[];
}

const props = defineProps<{
    scales: Paginated<ScoreScaleRow>;
    nextVersion: number;
}>();

const { t } = useI18n();

// A reasonable starting point matching the standard CEFR bands already used
// by DemoContentSeeder — admins can freely add/remove/edit rows from here.
const defaultBands = (): BandRow[] => [
    { cefr_level: 'C2', toeic_min: 975, toeic_max: 990, ept_min: 98, ept_max: 100 },
    { cefr_level: 'C1', toeic_min: 945, toeic_max: 965, ept_min: 96, ept_max: 97 },
    { cefr_level: 'B2', toeic_min: 785, toeic_max: 940, ept_min: 68, ept_max: 95 },
    { cefr_level: 'B1', toeic_min: 550, toeic_max: 665, ept_min: 56, ept_max: 67 },
    { cefr_level: 'A2', toeic_min: 225, toeic_max: 545, ept_min: 23, ept_max: 55 },
    { cefr_level: 'A1', toeic_min: 120, toeic_max: 170, ept_min: 12, ept_max: 17 },
];

const addBandRow = (bands: BandRow[]) => {
    bands.push({ cefr_level: '', toeic_min: null, toeic_max: null, ept_min: null, ept_max: null });
};

const removeBandRow = (bands: BandRow[], index: number) => {
    bands.splice(index, 1);
};

const showNewForm = ref(false);

const newForm = useForm({
    name: '',
    version: props.nextVersion,
    effective_from: new Date().toISOString().slice(0, 10),
    is_active: true,
    bands: defaultBands(),
});

const createScale = () => {
    newForm.post(route('admin.score-scales.store'), {
        preserveScroll: true,
        onSuccess: () => {
            newForm.reset();
            newForm.bands = defaultBands();
            showNewForm.value = false;
        },
    });
};

const expandedId = ref<number | null>(null);

const toggleExpand = (id: number) => {
    expandedId.value = expandedId.value === id ? null : id;
};

const expandedScale = () => props.scales.data.find((s) => s.id === expandedId.value) ?? null;

// A plain Object.fromEntries built once at setup wouldn't pick up rows from
// pages fetched after the initial load — Inertia keeps this component
// mounted across same-page navigations (only props change), so switching
// pages needs a form created for those newly-arrived rows too.
const editForms: Record<number, ReturnType<typeof useForm<Record<string, unknown>>>> = reactive({});

watch(
    () => props.scales.data,
    (rows) => {
        rows.forEach((scale) => {
            if (scale.id in editForms) return;

            editForms[scale.id] = useForm({
                name: scale.name,
                version: scale.version,
                effective_from: scale.effective_from,
                is_active: scale.is_active,
                bands: scale.bands.map((band) => ({ ...band })),
            });
        });
    },
    { immediate: true },
);

const saveScale = (id: number) => {
    editForms[id].put(route('admin.score-scales.update', id), { preserveScroll: true });
};

const toggleActive = (scale: ScoreScaleRow) => {
    router.patch(route('admin.score-scales.toggle-active', scale.id), {}, { preserveScroll: true });
};

const remove = async (scale: ScoreScaleRow) => {
    const confirmed = await confirmDialog({
        title: t('common.areYouSure'),
        text: t('admin.scoreScales.deleteConfirmText', { name: scale.name }),
    });

    if (!confirmed) return;

    router.delete(route('admin.score-scales.destroy', scale.id), { preserveScroll: true });
};

const statusVariant = (isActive: boolean): NonNullable<BadgeVariants['variant']> => (isActive ? 'success' : 'neutral');
</script>

<template>
    <div class="flex flex-col flex-1 h-full gap-4 p-4 rounded-xl">
        <Head :title="t('nav.admin.scoreScales')" />

        <div class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200">
            <div class="flex flex-col justify-between gap-3 pb-2 border-b border-slate-100 md:flex-row md:items-center">
                <div>
                    <h2 class="flex items-center gap-1.5 text-sm font-bold text-slate-800">
                        <Ruler class="w-4 h-4 text-indigo-600" />
                        {{ t('admin.scoreScales.title') }}
                    </h2>
                    <p class="text-[10px] text-slate-500">{{ t('admin.scoreScales.description') }}</p>
                </div>
                <Button type="button" size="sm" variant="outline" @click="showNewForm = !showNewForm">
                    <Plus class="h-3.5 w-3.5" />{{ t('admin.scoreScales.addNew') }}
                </Button>
            </div>

            <div class="mt-4 space-y-4">
                <Transition name="fade">
                    <form v-if="showNewForm" class="p-4 space-y-3 border-2 border-dashed rounded-xl bg-slate-50" @submit.prevent="createScale">
                        <div class="grid grid-cols-2 gap-2">
                            <div class="grid gap-1">
                                <Label for="new-scale-name">{{ t('admin.scoreScales.fieldName') }}</Label>
                                <Input
                                    id="new-scale-name"
                                    v-model="newForm.name"
                                    :placeholder="t('admin.scoreScales.namePlaceholder')"
                                />
                                <p v-if="newForm.errors.name" class="text-xs text-destructive">{{ newForm.errors.name }}</p>
                            </div>
                            <div class="grid gap-1">
                                <Label for="new-scale-version">{{ t('admin.scoreScales.fieldVersion') }}</Label>
                                <Input id="new-scale-version" v-model.number="newForm.version" type="number" min="1" />
                                <p v-if="newForm.errors.version" class="text-xs text-destructive">{{ newForm.errors.version }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div class="grid gap-1">
                                <Label for="new-scale-effective-from">{{ t('admin.scoreScales.fieldEffectiveFrom') }}</Label>
                                <Input id="new-scale-effective-from" v-model="newForm.effective_from" type="date" />
                                <p v-if="newForm.errors.effective_from" class="text-xs text-destructive">{{ newForm.errors.effective_from }}</p>
                            </div>
                            <label class="flex items-center self-end gap-2 pb-2 text-sm">
                                <Checkbox v-model:checked="newForm.is_active" />
                                {{ t('admin.scoreScales.fieldActive') }}
                            </label>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold text-slate-700">{{ t('admin.scoreScales.bandsTitle') }}</p>
                                <Button type="button" size="sm" variant="outline" @click="addBandRow(newForm.bands)">
                                    <Plus class="h-3.5 w-3.5" />{{ t('admin.scoreScales.addBand') }}
                                </Button>
                            </div>
                            <div class="overflow-x-auto bg-white border rounded-lg border-slate-200">
                                <table class="w-full text-xs text-left">
                                    <thead class="bg-slate-100">
                                        <tr>
                                            <th class="p-2">{{ t('admin.scoreScales.colCefr') }}</th>
                                            <th class="p-2 text-center">{{ t('admin.scoreScales.colToeicMin') }}</th>
                                            <th class="p-2 text-center">{{ t('admin.scoreScales.colToeicMax') }}</th>
                                            <th class="p-2 text-center">{{ t('admin.scoreScales.colEptMin') }}</th>
                                            <th class="p-2 text-center">{{ t('admin.scoreScales.colEptMax') }}</th>
                                            <th class="p-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(band, index) in newForm.bands" :key="index" class="border-t border-slate-200">
                                            <td class="p-2"><input v-model="band.cefr_level" class="w-20 px-2 py-1 border rounded" /></td>
                                            <td class="p-2">
                                                <input v-model.number="band.toeic_min" type="number" class="w-20 px-2 py-1 text-center border rounded" />
                                            </td>
                                            <td class="p-2">
                                                <input v-model.number="band.toeic_max" type="number" class="w-20 px-2 py-1 text-center border rounded" />
                                            </td>
                                            <td class="p-2">
                                                <input v-model.number="band.ept_min" type="number" class="w-20 px-2 py-1 text-center border rounded" />
                                            </td>
                                            <td class="p-2">
                                                <input v-model.number="band.ept_max" type="number" class="w-20 px-2 py-1 text-center border rounded" />
                                            </td>
                                            <td class="p-2 text-center">
                                                <button
                                                    type="button"
                                                    class="p-1 rounded text-destructive hover:bg-muted"
                                                    :title="t('admin.scoreScales.removeBand')"
                                                    @click="removeBandRow(newForm.bands, index)"
                                                >
                                                    <Trash2 class="h-3.5 w-3.5" />
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p v-if="newForm.errors.bands" class="text-xs text-destructive">{{ newForm.errors.bands }}</p>
                        </div>

                        <div class="flex gap-2">
                            <Button type="submit" class="flex-1" :disabled="newForm.processing">{{ t('admin.scoreScales.saveNew') }}</Button>
                            <Button type="button" variant="outline" @click="showNewForm = false">{{ t('admin.scoreScales.cancel') }}</Button>
                        </div>
                    </form>
                </Transition>

                <p v-if="scales.data.length === 0" class="p-8 text-sm text-center border border-dashed rounded-xl text-muted-foreground">
                    {{ t('admin.scoreScales.empty') }}
                </p>

                <div v-else class="overflow-x-auto border rounded-xl border-slate-200">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="p-2.5">{{ t('admin.scoreScales.colName') }}</th>
                                <th class="p-2.5 text-center">{{ t('admin.scoreScales.colVersion') }}</th>
                                <th class="p-2.5">{{ t('admin.scoreScales.colEffectiveFrom') }}</th>
                                <th class="p-2.5 text-center">{{ t('admin.scoreScales.colBands') }}</th>
                                <th class="p-2.5 text-center">{{ t('admin.scoreScales.colStatus') }}</th>
                                <th class="p-2.5 text-center">{{ t('admin.scoreScales.colActions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="scale in scales.data"
                                :key="scale.id"
                                class="border-t cursor-pointer border-slate-200 hover:bg-slate-50"
                                @click="toggleExpand(scale.id)"
                            >
                                <td class="p-2.5 font-semibold">
                                    {{ scale.name }}
                                    <span
                                        v-if="scale.in_use"
                                        class="ml-1 inline-flex items-center gap-1 rounded-full bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold text-slate-500"
                                    >
                                        <Lock class="h-2.5 w-2.5" />{{ t('admin.scoreScales.inUseBadge') }}
                                    </span>
                                </td>
                                <td class="p-2.5 text-center font-mono">v{{ scale.version }}</td>
                                <td class="p-2.5 text-muted-foreground">{{ scale.effective_from }}</td>
                                <td class="p-2.5 text-center">{{ scale.bands.length }}</td>
                                <td class="p-2.5 text-center">
                                    <Badge :variant="statusVariant(scale.is_active)">{{
                                        scale.is_active ? t('admin.scoreScales.statusActive') : t('admin.scoreScales.statusInactive')
                                    }}</Badge>
                                </td>
                                <td class="p-2.5 text-center" @click.stop>
                                    <div class="flex justify-center gap-1">
                                        <button
                                            type="button"
                                            class="p-1 rounded hover:bg-muted"
                                            :title="scale.is_active ? t('admin.scoreScales.statusInactive') : t('admin.scoreScales.statusActive')"
                                            @click="toggleActive(scale)"
                                        >
                                            <Power class="h-3.5 w-3.5" :class="scale.is_active ? 'text-emerald-600' : 'text-slate-400'" />
                                        </button>
                                        <button
                                            type="button"
                                            class="p-1 rounded text-destructive hover:bg-muted"
                                            :title="t('admin.scoreScales.deleteTitle')"
                                            @click="remove(scale)"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-if="scales.data.length > 0" class="text-[10px] text-muted-foreground">{{ t('admin.scoreScales.tableHint') }}</p>
                <Pagination
                    :current-page="scales.current_page"
                    :last-page="scales.last_page"
                    :prev-page-url="scales.prev_page_url"
                    :next-page-url="scales.next_page_url"
                />

                <Transition name="fade">
                    <div
                        v-if="expandedScale()"
                        :key="expandedScale()!.id"
                        class="p-4 space-y-3 bg-white border shadow-sm rounded-xl border-slate-200"
                    >
                        <template v-for="editing in [expandedScale()!]" :key="editing.id">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                <h4 class="text-xs font-bold text-slate-700">{{ editing.name }} (v{{ editing.version }})</h4>
                                <button type="button" class="p-1 rounded text-muted-foreground hover:bg-muted" @click="expandedId = null">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>

                            <template v-if="editing.in_use">
                                <p class="p-2 text-xs border rounded-lg border-amber-200 bg-amber-50 text-amber-800">
                                    {{ t('admin.scoreScales.editBlockedNotice') }}
                                </p>
                                <div class="overflow-x-auto border rounded-lg border-slate-200">
                                    <table class="w-full text-xs text-left">
                                        <thead class="bg-slate-100">
                                            <tr>
                                                <th class="p-2">{{ t('admin.scoreScales.colCefr') }}</th>
                                                <th class="p-2 text-center">{{ t('admin.scoreScales.colToeicMin') }}</th>
                                                <th class="p-2 text-center">{{ t('admin.scoreScales.colToeicMax') }}</th>
                                                <th class="p-2 text-center">{{ t('admin.scoreScales.colEptMin') }}</th>
                                                <th class="p-2 text-center">{{ t('admin.scoreScales.colEptMax') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="band in editing.bands" :key="band.id" class="border-t border-slate-200">
                                                <td class="p-2 font-semibold">{{ band.cefr_level }}</td>
                                                <td class="p-2 font-mono text-center">{{ band.toeic_min ?? '—' }}</td>
                                                <td class="p-2 font-mono text-center">{{ band.toeic_max ?? '—' }}</td>
                                                <td class="p-2 font-mono text-center">{{ band.ept_min ?? '—' }}</td>
                                                <td class="p-2 font-mono text-center">{{ band.ept_max ?? '—' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </template>

                            <form v-else class="space-y-3" @submit.prevent="saveScale(editing.id)">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="grid gap-1">
                                        <Label :for="`scale-name-${editing.id}`">{{ t('admin.scoreScales.fieldName') }}</Label>
                                        <Input :id="`scale-name-${editing.id}`" v-model="editForms[editing.id].name" />
                                        <p v-if="editForms[editing.id].errors.name" class="text-xs text-destructive">
                                            {{ editForms[editing.id].errors.name }}
                                        </p>
                                    </div>
                                    <div class="grid gap-1">
                                        <Label :for="`scale-version-${editing.id}`">{{ t('admin.scoreScales.fieldVersion') }}</Label>
                                        <Input :id="`scale-version-${editing.id}`" v-model.number="editForms[editing.id].version" type="number" min="1" />
                                        <p v-if="editForms[editing.id].errors.version" class="text-xs text-destructive">
                                            {{ editForms[editing.id].errors.version }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div class="grid gap-1">
                                        <Label :for="`scale-effective-from-${editing.id}`">{{ t('admin.scoreScales.fieldEffectiveFrom') }}</Label>
                                        <Input :id="`scale-effective-from-${editing.id}`" v-model="editForms[editing.id].effective_from" type="date" />
                                        <p v-if="editForms[editing.id].errors.effective_from" class="text-xs text-destructive">
                                            {{ editForms[editing.id].errors.effective_from }}
                                        </p>
                                    </div>
                                    <label class="flex items-center self-end gap-2 pb-2 text-sm">
                                        <Checkbox v-model:checked="editForms[editing.id].is_active" />
                                        {{ t('admin.scoreScales.fieldActive') }}
                                    </label>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs font-bold text-slate-700">{{ t('admin.scoreScales.bandsTitle') }}</p>
                                        <Button type="button" size="sm" variant="outline" @click="addBandRow(editForms[editing.id].bands)">
                                            <Plus class="h-3.5 w-3.5" />{{ t('admin.scoreScales.addBand') }}
                                        </Button>
                                    </div>
                                    <div class="overflow-x-auto border rounded-lg border-slate-200">
                                        <table class="w-full text-xs text-left">
                                            <thead class="bg-slate-100">
                                                <tr>
                                                    <th class="p-2">{{ t('admin.scoreScales.colCefr') }}</th>
                                                    <th class="p-2 text-center">{{ t('admin.scoreScales.colToeicMin') }}</th>
                                                    <th class="p-2 text-center">{{ t('admin.scoreScales.colToeicMax') }}</th>
                                                    <th class="p-2 text-center">{{ t('admin.scoreScales.colEptMin') }}</th>
                                                    <th class="p-2 text-center">{{ t('admin.scoreScales.colEptMax') }}</th>
                                                    <th class="p-2"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(band, index) in editForms[editing.id].bands" :key="index" class="border-t border-slate-200">
                                                    <td class="p-2"><input v-model="band.cefr_level" class="w-20 px-2 py-1 border rounded" /></td>
                                                    <td class="p-2">
                                                        <input v-model.number="band.toeic_min" type="number" class="w-20 px-2 py-1 text-center border rounded" />
                                                    </td>
                                                    <td class="p-2">
                                                        <input v-model.number="band.toeic_max" type="number" class="w-20 px-2 py-1 text-center border rounded" />
                                                    </td>
                                                    <td class="p-2">
                                                        <input v-model.number="band.ept_min" type="number" class="w-20 px-2 py-1 text-center border rounded" />
                                                    </td>
                                                    <td class="p-2">
                                                        <input v-model.number="band.ept_max" type="number" class="w-20 px-2 py-1 text-center border rounded" />
                                                    </td>
                                                    <td class="p-2 text-center">
                                                        <button
                                                            type="button"
                                                            class="p-1 rounded text-destructive hover:bg-muted"
                                                            :title="t('admin.scoreScales.removeBand')"
                                                            @click="removeBandRow(editForms[editing.id].bands, index)"
                                                        >
                                                            <Trash2 class="h-3.5 w-3.5" />
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <p v-if="editForms[editing.id].errors.bands" class="text-xs text-destructive">
                                        {{ editForms[editing.id].errors.bands }}
                                    </p>
                                </div>

                                <Button type="submit" class="w-full" :disabled="editForms[editing.id].processing">{{ t('admin.scoreScales.save') }}</Button>
                            </form>
                        </template>
                    </div>
                </Transition>
            </div>
        </div>
    </div>
</template>
