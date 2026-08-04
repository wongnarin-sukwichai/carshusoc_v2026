<script setup lang="ts">
import { Dialog, DialogHeader, DialogScrollContent, DialogTitle } from '@/components/ui/dialog';
import { useI18n } from 'vue-i18n';

const open = defineModel<boolean>('open', { default: false });

const { t } = useI18n();
</script>

<template>
    <Dialog v-model:open="open">
        <DialogScrollContent class="max-w-3xl">
            <DialogHeader>
                <DialogTitle>{{ t('components.examRequiredDocuments.title') }}</DialogTitle>
            </DialogHeader>

            <div class="space-y-4 text-sm">
                <div>
                    <p class="mb-2 rounded-lg bg-slate-800 px-3 py-1.5 text-center text-xs font-bold text-white">
                        {{ t('components.examRequiredDocuments.thaiTitle') }}
                    </p>
                    <div class="overflow-x-auto border rounded-lg border-slate-200">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th class="w-1/3 p-2 font-bold">{{ t('components.examRequiredDocuments.colType') }}</th>
                                    <th class="p-2 font-bold">{{ t('components.examRequiredDocuments.colDocs') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-t border-slate-200">
                                    <td class="p-2 font-medium align-top">1. {{ t('components.examRequiredDocuments.typeStudent') }}</td>
                                    <td class="p-2 align-top text-muted-foreground">{{ t('components.examRequiredDocuments.thaiDocsStudent') }}</td>
                                </tr>
                                <tr class="border-t border-slate-200 bg-slate-50">
                                    <td class="p-2 font-medium align-top">2. {{ t('components.examRequiredDocuments.typeStaff') }}</td>
                                    <td class="p-2 align-top text-muted-foreground">{{ t('components.examRequiredDocuments.thaiDocsStaff') }}</td>
                                </tr>
                                <tr class="border-t border-slate-200">
                                    <td class="p-2 font-medium align-top">3. {{ t('components.examRequiredDocuments.typeGeneral') }}</td>
                                    <td class="p-2 align-top text-muted-foreground">{{ t('components.examRequiredDocuments.thaiDocsGeneral') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <p class="mb-2 rounded-lg bg-slate-800 px-3 py-1.5 text-center text-xs font-bold text-white">
                        {{ t('components.examRequiredDocuments.nonThaiTitle') }}
                    </p>
                    <div class="overflow-x-auto border rounded-lg border-slate-200">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th class="w-1/3 p-2 font-bold">{{ t('components.examRequiredDocuments.colType') }}</th>
                                    <th class="p-2 font-bold">{{ t('components.examRequiredDocuments.colDocs') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-t border-slate-200">
                                    <td class="space-y-0.5 p-2 align-top font-medium">
                                        <p>1. {{ t('components.examRequiredDocuments.typeStudent') }}</p>
                                        <p>2. {{ t('components.examRequiredDocuments.typeStaff') }}</p>
                                        <p>3. {{ t('components.examRequiredDocuments.typeGeneral') }}</p>
                                    </td>
                                    <td class="p-2 align-top text-muted-foreground">{{ t('components.examRequiredDocuments.nonThaiDocs') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </DialogScrollContent>
    </Dialog>
</template>
