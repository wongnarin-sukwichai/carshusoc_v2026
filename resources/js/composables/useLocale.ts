import { i18n, SUPPORTED_LOCALES, type SupportedLocale } from '@/lib/i18n';
import { computed } from 'vue';

const ONE_YEAR_IN_SECONDS = 60 * 60 * 24 * 365;

export function useLocale() {
    const locale = computed<SupportedLocale>({
        get: () => i18n.global.locale.value as SupportedLocale,
        set: (value) => {
            i18n.global.locale.value = value;
            document.cookie = `locale=${value}; max-age=${ONE_YEAR_IN_SECONDS}; path=/`;
        },
    });

    const toggle = () => {
        const currentIndex = SUPPORTED_LOCALES.indexOf(locale.value);
        locale.value = SUPPORTED_LOCALES[(currentIndex + 1) % SUPPORTED_LOCALES.length];
    };

    return { locale, toggle, locales: SUPPORTED_LOCALES };
}
