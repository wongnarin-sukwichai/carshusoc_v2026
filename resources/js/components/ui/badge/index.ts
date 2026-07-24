import { cva, type VariantProps } from 'class-variance-authority';

export { default as Badge } from './Badge.vue';

export const badgeVariants = cva('inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-bold whitespace-nowrap', {
    variants: {
        variant: {
            success: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            warning: 'bg-amber-50 text-amber-700 border-amber-200',
            destructive: 'bg-red-50 text-red-700 border-red-200',
            info: 'bg-indigo-50 text-indigo-700 border-indigo-200',
            neutral: 'bg-slate-100 text-slate-700 border-slate-200',
        },
    },
    defaultVariants: {
        variant: 'neutral',
    },
});

export type BadgeVariants = VariantProps<typeof badgeVariants>;
