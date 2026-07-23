<script setup lang="ts">
import LegalPage from '@/components/marketing/LegalPage.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <LegalPage :title="t('public.legalContent.terms.title')" :updated="t('public.legal.updatedJuly2026')">
        <h2>{{ t('public.legalContent.terms.h1') }}</h2>
        <p>{{ t('public.legalContent.terms.p1') }}</p>

        <h2>{{ t('public.legalContent.terms.h2') }}</h2>
        <p>{{ t('public.legalContent.terms.p2') }}</p>

        <h2>{{ t('public.legalContent.terms.h3') }}</h2>
        <p>{{ t('public.legalContent.terms.p3') }}</p>

        <h2>{{ t('public.legalContent.terms.h4') }}</h2>
        <p>{{ t('public.legalContent.terms.p4') }}</p>

        <h2>{{ t('public.legalContent.terms.h5') }}</h2>
        <p>{{ t('public.legalContent.terms.p5') }}</p>

        <h2>{{ t('public.legalContent.terms.h6') }}</h2>
        <p>{{ t('public.legalContent.terms.p6') }}</p>

        <h2>{{ t('public.legalContent.terms.h7') }}</h2>
        <p>{{ t('public.legalContent.terms.p7') }}</p>

        <h2>{{ t('public.legalContent.terms.h8') }}</h2>
        <p>{{ t('public.legalContent.terms.p8') }}</p>

        <h2>{{ t('public.legalContent.terms.h9') }}</h2>
        <p>{{ t('public.legalContent.terms.p9') }}</p>
    </LegalPage>
</template>
