<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { TabsContentProps } from 'radix-vue';
import { TabsContent, useForwardProps } from 'radix-vue';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<TabsContentProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;
    return delegated;
});

const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
    <TabsContent
        v-bind="forwardedProps"
        :class="cn('mt-4 ring-offset-background focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2', props.class)"
    >
        <slot />
    </TabsContent>
</template>
