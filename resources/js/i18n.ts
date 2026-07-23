import { createI18n } from 'vue-i18n';
import de from '@/lang/de';
import en from '@/lang/en';

export const i18n = createI18n({
    legacy: true,
    locale: 'de',
    fallbackLocale: 'de',
    messages: { de, en },
});

export function setI18nLocale(locale: string) {
    if (locale === 'de' || locale === 'en') {
        i18n.global.locale = locale;
    }
}
