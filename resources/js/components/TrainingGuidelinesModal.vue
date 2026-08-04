<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogFooter, DialogHeader, DialogScrollContent, DialogTitle } from '@/components/ui/dialog';
import { BookOpenCheck } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const open = defineModel<boolean>('open', { default: false });

const { t } = useI18n();
</script>

<template>
    <Dialog v-model:open="open">
        <DialogScrollContent class="max-w-lg">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 text-indigo-600 bg-indigo-100 rounded-full">
                        <BookOpenCheck class="w-4 h-4" />
                    </span>
                    {{ t('components.trainingGuidelines.title') }}
                </DialogTitle>
            </DialogHeader>

            <div class="pt-4 space-y-4 text-sm border-t">
                <div>
                    <p class="font-bold">{{ t('components.trainingGuidelines.section1Title') }}</p>
                    <p class="mt-1 text-muted-foreground">{{ t('components.trainingGuidelines.section1Body') }}</p>
                </div>

                <div>
                    <p class="font-bold">{{ t('components.trainingGuidelines.section2Title') }}</p>
                    <p class="mt-1 text-muted-foreground">
                        {{ t('components.trainingGuidelines.section2BodyBefore') }}
                        <span class="font-bold text-red-600">{{ t('components.trainingGuidelines.section2BodyHighlight') }}</span>
                        {{ t('components.trainingGuidelines.section2BodyAfter') }}
                    </p>
                </div>

                <div>
                    <p class="font-bold">{{ t('components.trainingGuidelines.section3Title') }}</p>
                    <p class="mt-1 text-muted-foreground">{{ t('components.trainingGuidelines.section3Body') }}</p>
                </div>

                <div>
                    <p class="font-bold">{{ t('components.trainingGuidelines.section4Title') }}</p>
                    <p class="mt-1 font-medium">{{ t('components.trainingGuidelines.totalScore') }}</p>
                    <ul class="mt-1 list-disc space-y-0.5 pl-5 text-muted-foreground">
                        <li>{{ t('components.trainingGuidelines.masterLevel') }}</li>
                        <li>{{ t('components.trainingGuidelines.doctoralLevel') }}</li>
                    </ul>
                    <p class="mt-2 font-medium">{{ t('components.trainingGuidelines.basicCourseTitle') }}</p>
                    <ul class="mt-1 list-disc space-y-0.5 pl-5 text-muted-foreground">
                        <li>{{ t('components.trainingGuidelines.basicCourseExam') }}</li>
                    </ul>
                    <p class="mt-2 font-medium">{{ t('components.trainingGuidelines.academicCourseTitle') }}</p>
                    <ul class="mt-1 list-disc space-y-0.5 pl-5 text-muted-foreground">
                        <li>{{ t('components.trainingGuidelines.academicCoursePractical') }}</li>
                        <li>{{ t('components.trainingGuidelines.academicCourseExam') }}</li>
                    </ul>
                </div>

                <div class="p-3 text-xs border rounded-lg border-amber-200 bg-amber-50 text-amber-800">
                    <p class="font-bold">{{ t('components.trainingGuidelines.noteLabel') }}</p>
                    <p class="mt-0.5">{{ t('components.trainingGuidelines.note') }}</p>
                </div>
            </div>

            <DialogFooter>
                <Button type="button" class="w-full" @click="open = false">{{ t('components.trainingGuidelines.close') }}</Button>
            </DialogFooter>
        </DialogScrollContent>
    </Dialog>
</template>
