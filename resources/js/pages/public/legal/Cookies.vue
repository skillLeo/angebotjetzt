<script setup lang="ts">
import LegalPage from '@/components/marketing/LegalPage.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <LegalPage :title="t('public.legalContent.cookies.title')" :updated="t('public.legal.updatedJuly2026')">
        <p>{{ t('public.legalContent.cookies.intro') }}</p>

        <h2>{{ t('public.legalContent.cookies.h1') }}</h2>
        <p>{{ t('public.legalContent.cookies.p1') }}</p>

        <h2>{{ t('public.legalContent.cookies.h2') }}</h2>
        <h3>{{ t('public.legalContent.cookies.h2a') }}</h3>
        <p>{{ t('public.legalContent.cookies.p2a') }}</p>
        <h3>{{ t('public.legalContent.cookies.h2b') }}</h3>
        <p>{{ t('public.legalContent.cookies.p2b') }}</p>
        <h3>{{ t('public.legalContent.cookies.h2c') }}</h3>
        <p>{{ t('public.legalContent.cookies.p2c') }}</p>

        <h2>{{ t('public.legalContent.cookies.h3') }}</h2>
        <p>{{ t('public.legalContent.cookies.p3') }}</p>
    </LegalPage>
</template>
