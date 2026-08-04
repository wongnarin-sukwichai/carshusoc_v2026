<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogFooter, DialogHeader, DialogScrollContent, DialogTitle } from '@/components/ui/dialog';
import { ShieldAlert } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const open = defineModel<boolean>('open', { default: false });

const { t } = useI18n();

// Keys for the plain rules (1, 2, 5, 6, 7, 8, 9) — rule 1 and rule 10 are
// rendered separately below since each has an embedded highlighted phrase.
const plainRuleKeys = ['rule2', 'rule3', 'rule4', 'rule5', 'rule6', 'rule7', 'rule8', 'rule9'] as const;
</script>

<template>
    <Dialog v-model:open="open">
        <DialogScrollContent class="max-w-lg">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 text-red-600 bg-red-100 rounded-full">
                        <ShieldAlert class="w-4 h-4" />
                    </span>
                    {{ t('components.examRules.title') }}
                </DialogTitle>
                <p class="text-xs text-muted-foreground">{{ t('components.examRules.asOf') }}</p>
            </DialogHeader>

            <ol class="py-4 pl-4 space-y-3 text-sm list-decimal border-y">
                <li>
                    {{ t('components.examRules.rule1') }}
                    <span class="font-bold text-red-600">{{ t('components.examRules.rule1Warning') }}</span>
                </li>
                <li v-for="key in plainRuleKeys" :key="key">{{ t(`components.examRules.${key}`) }}</li>
                <li>
                    {{ t('components.examRules.rule10') }}
                    <span class="font-bold text-red-600">{{ t('components.examRules.rule10Cheat') }}</span>
                    {{ t('components.examRules.rule10End') }}
                </li>
            </ol>

            <DialogFooter>
                <Button type="button" class="w-full" @click="open = false">{{ t('components.examRules.close') }}</Button>
            </DialogFooter>
        </DialogScrollContent>
    </Dialog>
</template>
