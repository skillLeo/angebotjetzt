<script setup lang="ts">
import LegalPage from '@/components/marketing/LegalPage.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <LegalPage :title="t('public.legalContent.privacy.title')" :updated="t('public.legal.updatedJuly2026')">
        <p>{{ t('public.legalContent.privacy.intro') }}</p>

        <h2>{{ t('public.legalContent.privacy.h1') }}</h2>
        <p>{{ t('public.legalContent.privacy.p1') }}</p>

        <h2>{{ t('public.legalContent.privacy.h2') }}</h2>
        <p>{{ t('public.legalContent.privacy.p2') }}</p>

        <h2>{{ t('public.legalContent.privacy.h3') }}</h2>
        <p>{{ t('public.legalContent.privacy.p3') }}</p>

        <h2>{{ t('public.legalContent.privacy.h4') }}</h2>
        <p>{{ t('public.legalContent.privacy.p4') }}</p>

        <h2>{{ t('public.legalContent.privacy.h5') }}</h2>
        <p>{{ t('public.legalContent.privacy.p5') }}</p>

        <h2>{{ t('public.legalContent.privacy.h6') }}</h2>
        <p>{{ t('public.legalContent.privacy.p6') }}</p>

        <h2>{{ t('public.legalContent.privacy.h7') }}</h2>
        <p>{{ t('public.legalContent.privacy.p7') }}</p>

        <h2>{{ t('public.legalContent.privacy.h8') }}</h2>
        <p>
            {{ t('public.legalContent.privacy.p8') }}
            <a href="/cookie-policy">{{ t('public.legalContent.privacy.p8Link') }}</a>{{ t('public.legalContent.privacy.p8End') }}
        </p>
    </LegalPage>
</template>
