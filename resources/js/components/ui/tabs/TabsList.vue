<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { TabsListProps } from 'radix-vue';
import { TabsList, useForwardProps } from 'radix-vue';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<TabsListProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;
    return delegated;
});

const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
    <TabsList
        v-bind="forwardedProps"
        :class="cn('inline-flex h-10 items-center justify-center rounded-md bg-muted p-1 text-muted-foreground', props.class)"
    >
        <slot />
    </TabsList>
</template>
