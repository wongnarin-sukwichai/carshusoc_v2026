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
        <Languages class="w-4 h-4 text-black" />
        <span class="text-xs font-medium text-black">{{ targetLabel }}</span>
    </Button>
</template>
