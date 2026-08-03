import dayjs from 'dayjs';

/**
 * Formats a date string for display. Defaults to ว/ด/ป (DD/MM/YYYY) since
 * that's the conventional Thai order — pass a different dayjs format
 * string for other contexts.
 */
export function formatDate(date: string | null | undefined, format = 'DD/MM/YYYY'): string {
    if (!date) return '-';

    const parsed = dayjs(date);

    return parsed.isValid() ? parsed.format(format) : '-';
}
