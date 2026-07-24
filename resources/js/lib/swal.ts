import { i18n } from '@/lib/i18n';
import Swal from 'sweetalert2';

const swal = Swal.mixin({
    buttonsStyling: false,
    customClass: {
        confirmButton:
            'mx-1 inline-flex h-9 items-center justify-center gap-2 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90',
        cancelButton:
            'mx-1 inline-flex h-9 items-center justify-center gap-2 rounded-md border border-input bg-background px-4 text-sm font-medium shadow-sm hover:bg-accent',
    },
});

interface ConfirmOptions {
    title: string;
    text?: string;
    icon?: 'warning' | 'question' | 'info';
    confirmButtonText?: string;
}

/**
 * Replaces the browser's native confirm() with a themed SweetAlert2 dialog.
 * Resolves true only if the user clicked confirm.
 */
export async function confirmDialog(options: ConfirmOptions): Promise<boolean> {
    const { t } = i18n.global;

    const result = await swal.fire({
        title: options.title,
        text: options.text,
        icon: options.icon ?? 'warning',
        showCancelButton: true,
        confirmButtonText: options.confirmButtonText ?? t('common.confirm'),
        cancelButtonText: t('common.cancel'),
        reverseButtons: true,
    });

    return result.isConfirmed;
}

const TOAST_TIMER_MS = 4500;

function toast(message: string, icon: 'success' | 'error') {
    swal.fire({
        toast: true,
        position: 'bottom-end',
        icon,
        title: message,
        showConfirmButton: false,
        timer: TOAST_TIMER_MS,
        timerProgressBar: true,
    });
}

export const successToast = (message: string) => toast(message, 'success');
export const errorToast = (message: string) => toast(message, 'error');
