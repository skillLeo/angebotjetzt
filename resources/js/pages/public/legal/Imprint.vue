<script setup lang="ts">
import LegalPage from '@/components/marketing/LegalPage.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <LegalPage :title="t('public.legalContent.imprint.title')">
        <h2>{{ t('public.legalContent.imprint.h1') }}</h2>
        <p v-html="t('public.legalContent.imprint.company')" />

        <h2>{{ t('public.legalContent.imprint.h2') }}</h2>
        <p>{{ t('public.legalContent.imprint.ceo') }}</p>

        <h2>{{ t('public.legalContent.imprint.h3') }}</h2>
        <p v-html="t('public.legalContent.imprint.contactInfo')" />

        <h2>{{ t('public.legalContent.imprint.h4') }}</h2>
        <p v-html="t('public.legalContent.imprint.registerInfo')" />

        <h2>{{ t('public.legalContent.imprint.h5') }}</h2>
        <p v-html="t('public.legalContent.imprint.vatInfo')" />

        <h2>{{ t('public.legalContent.imprint.h6') }}</h2>
        <p>{{ t('public.legalContent.imprint.responsible') }}</p>

        <h2>{{ t('public.legalContent.imprint.h7') }}</h2>
        <p>
            {{ t('public.legalContent.imprint.disputeText') }}
            <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener">https://ec.europa.eu/consumers/odr</a>.
            {{ t('public.legalContent.imprint.disputeText2') }}
        </p>
    </LegalPage>
</template>
