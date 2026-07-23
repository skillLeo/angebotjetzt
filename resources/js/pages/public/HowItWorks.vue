<script setup lang="ts">
import HowItWorks from '@/components/home/HowItWorks.vue';
import FaqAccordion from '@/components/marketing/FaqAccordion.vue';
import SectionHeading from '@/components/marketing/SectionHeading.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const faq = computed(() => [
    { q: t('public.howItWorks.faqQ1'), a: t('public.howItWorks.faqA1') },
    { q: t('public.howItWorks.faqQ2'), a: t('public.howItWorks.faqA2') },
    { q: t('public.howItWorks.faqQ3'), a: t('public.howItWorks.faqA3') },
]);
</script>

<template>
    <Head>
        <title>{{ t('public.howItWorks.metaTitle') }}</title>
        <meta name="description" :content="t('public.howItWorks.metaDescription')" />
    </Head>

    <section class="bg-sand-100 px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <p class="text-eyebrow mb-4 text-green-600">{{ t('public.howItWorks.eyebrow') }}</p>
            <h1 class="text-hero max-w-3xl text-navy-700">{{ t('public.howItWorks.title') }}</h1>
            <p class="text-lead mt-6 max-w-2xl text-ink-700">
                {{ t('public.howItWorks.description') }}
            </p>
        </div>
    </section>

    <HowItWorks />

    <section class="bg-sand-50 py-16 lg:py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <SectionHeading centered :line1="t('public.howItWorks.faqLine1')" :line2="t('public.howItWorks.faqLine2')" />
            <div class="mt-10">
                <FaqAccordion :items="faq" />
            </div>
            <div class="mt-10 text-center">
                <Link href="/request" class="inline-block rounded-pill bg-green-500 px-8 py-4 text-base font-bold text-white transition hover:bg-green-600">
                    {{ t('public.howItWorks.cta') }}
                </Link>
            </div>
        </div>
    </section>
</template>
