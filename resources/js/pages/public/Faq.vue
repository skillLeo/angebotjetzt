<script setup lang="ts">
import FaqAccordion from '@/components/marketing/FaqAccordion.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const sections = computed(() => [
    {
        title: t('public.faq.customersTitle'),
        items: [
            { q: t('public.faq.customerQ1'), a: t('public.faq.customerA1') },
            { q: t('public.faq.customerQ2'), a: t('public.faq.customerA2') },
            { q: t('public.faq.customerQ3'), a: t('public.faq.customerA3') },
            { q: t('public.faq.customerQ4'), a: t('public.faq.customerA4') },
        ],
    },
    {
        title: t('public.faq.inspectorsTitle'),
        items: [
            { q: t('public.faq.inspectorQ1'), a: t('public.faq.inspectorA1') },
            { q: t('public.faq.inspectorQ2'), a: t('public.faq.inspectorA2') },
            { q: t('public.faq.inspectorQ3'), a: t('public.faq.inspectorA3') },
        ],
    },
]);
</script>

<template>
    <Head>
        <title>{{ t('public.faq.metaTitle') }}</title>
        <meta name="description" :content="t('public.faq.metaDescription')" />
    </Head>

    <section class="bg-sand-100 px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <p class="text-eyebrow mb-4 text-green-600">{{ t('public.faq.eyebrow') }}</p>
            <h1 class="text-hero max-w-3xl text-navy-700">{{ t('public.faq.title') }}</h1>
        </div>
    </section>

    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-3xl space-y-12 px-4 sm:px-6 lg:px-8">
            <div v-for="section in sections" :key="section.title">
                <h2 class="text-subsection mb-5 text-navy-700">{{ section.title }}</h2>
                <FaqAccordion :items="section.items" />
            </div>
            <div class="rounded-panel bg-navy-900 p-8 text-center">
                <p class="font-display text-xl font-bold text-white">{{ t('public.faq.noAnswer') }}</p>
                <Link href="/contact" class="mt-5 inline-block rounded-pill bg-green-500 px-7 py-3 font-bold text-white transition hover:bg-green-600">
                    {{ t('public.faq.contactCta') }}
                </Link>
            </div>
        </div>
    </section>
</template>
