<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import logoSrc from '../../images/logo-cars-husoc.png';

const props = withDefaults(
    defineProps<{
        homeHref?: string;
        // Scoped color trial (see CLAUDE.md) — homepage passes "blue" to preview
        // the carshusoc.com brand accent without touching the shared indigo
        // theme used by the user/admin portals.
        accent?: 'indigo' | 'blue';
    }>(),
    { accent: 'indigo' },
);

const { t } = useI18n();

const badgeClass = props.accent === 'blue' ? 'bg-blue-600' : 'bg-indigo-500';
</script>

<template>
    <header class="sticky top-0 z-40 flex h-16 items-center border-b border-slate-800 bg-slate-900 px-6 text-white shadow-md">
        <div class="flex w-full flex-wrap items-center justify-between gap-4">
            <Component :is="homeHref ? Link : 'div'" :href="homeHref" class="flex items-center gap-3">
                <div class="flex aspect-square size-10 items-center justify-center rounded-xl bg-white p-1 shadow-inner">
                    <img :src="logoSrc" alt="CARS-HUSOC" class="size-full object-contain" />
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-lg font-extrabold tracking-tight">{{ t('app.name') }}</span>
                        <span class="rounded-full px-2 py-0.5 text-[9px] font-black tracking-wider text-white uppercase" :class="badgeClass">
                            Portal
                        </span>
                    </div>
                    <p class="text-[11px] leading-tight text-slate-400">{{ t('app.tagline') }}</p>
                </div>
            </Component>

            <div class="flex items-center gap-2">
                <slot />
            </div>
        </div>
    </header>
</template>
