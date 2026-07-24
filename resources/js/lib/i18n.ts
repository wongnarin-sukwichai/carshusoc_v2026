import en from '@/lang/en';
import th from '@/lang/th';
import { createI18n } from 'vue-i18n';

export const SUPPORTED_LOCALES = ['th', 'en'] as const;
export type SupportedLocale = (typeof SUPPORTED_LOCALES)[number];

function readLocaleCookie(): SupportedLocale {
    if (typeof document === 'undefined') return 'th';

    const match = document.cookie.match(/(?:^|; )locale=([^;]*)/);
    const value = match ? decodeURIComponent(match[1]) : null;

    return (SUPPORTED_LOCALES as readonly string[]).includes(value ?? '') ? (value as SupportedLocale) : 'th';
}

export const i18n = createI18n({
    legacy: false,
    locale: readLocaleCookie(),
    fallbackLocale: 'th',
    messages: { th, en },
});
