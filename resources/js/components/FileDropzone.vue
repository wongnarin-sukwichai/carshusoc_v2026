<script setup lang="ts">
import { UploadCloud, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    modelValue: File | null;
    id: string;
    accept: string;
    required?: boolean;
    hint?: string;
    formatsHint?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', file: File | null): void;
}>();

const { t } = useI18n();

const fileInput = ref<HTMLInputElement | null>(null);
const isDraggingOver = ref(false);

const onFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    emit('update:modelValue', target.files?.[0] ?? null);
};

const clearFile = () => {
    emit('update:modelValue', null);
    if (fileInput.value) fileInput.value.value = '';
};

const onFileDrop = (event: DragEvent) => {
    isDraggingOver.value = false;
    const file = event.dataTransfer?.files?.[0];
    if (file) emit('update:modelValue', file);
};
</script>

<template>
    <div>
        <label
            :for="id"
            :class="[
                'flex cursor-pointer flex-col items-center justify-center gap-1.5 rounded-xl border-2 border-dashed px-4 py-6 text-center transition-colors',
                isDraggingOver
                    ? 'border-indigo-500 bg-indigo-50'
                    : 'border-slate-300 bg-slate-50 hover:border-indigo-400 hover:bg-indigo-50/60',
            ]"
            @dragover.prevent="isDraggingOver = true"
            @dragleave.prevent="isDraggingOver = false"
            @drop.prevent="onFileDrop"
        >
            <UploadCloud class="h-8 w-8 text-indigo-500" />
            <span v-if="modelValue" class="max-w-full truncate text-sm font-medium text-indigo-700">{{ modelValue.name }}</span>
            <span v-else class="text-sm text-muted-foreground">{{ hint ?? t('components.fileDropzone.hint') }}</span>
            <span class="text-[11px] text-muted-foreground">{{ formatsHint ?? t('components.fileDropzone.formatsHint') }}</span>
        </label>

        <button
            v-if="modelValue"
            type="button"
            class="mt-1.5 flex w-fit items-center gap-1 text-[11px] text-destructive hover:underline"
            @click="clearFile"
        >
            <X class="h-3 w-3" />
            {{ t('components.fileDropzone.remove') }}
        </button>

        <input :id="id" ref="fileInput" type="file" :accept="accept" :required="required" class="hidden" @change="onFileChange" />
    </div>
</template>
