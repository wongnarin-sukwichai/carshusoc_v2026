<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useLocale } from '@/composables/useLocale';
import { Languages } from 'lucide-vue-next';
import { computed } from 'vue';

const { locale, toggle } = useLocale();

// Shows the language you'll switch *to*, not the current one — a common
// convention for two-way toggles (e.g. Wikipedia's language links).
const targetLabel = computed(() => (locale.value === 'th' ? 'EN' : 'ไทย'));
</script>

<template>
    <Button variant="ghost" size="sm" :title="$t('locale.switchTo')" @click="toggle">
        <Languages class="h-4 w-4" />
        <span class="text-xs font-medium">{{ targetLabel }}</span>
    </Button>
</template>
