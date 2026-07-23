<script setup lang="ts">
import ServiceGrid from '@/components/home/ServiceGrid.vue';
import SectionHeading from '@/components/marketing/SectionHeading.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    serviceTypes: Array<{ id: number; name: string; slug: string; description: string; image: string }>;
    inspectorCount: number;
}>();
</script>

<template>
    <Head>
        <title>{{ t('public.category.metaTitle') }}</title>
        <meta name="description" :content="t('public.category.metaDescription')" />
    </Head>

    <section class="bg-sand-100 px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="mx-auto max-w-7xl">
            <p class="text-eyebrow mb-4 text-green-600">{{ t('public.category.eyebrow') }}</p>
            <h1 class="text-hero max-w-3xl text-navy-700">{{ t('public.category.title') }}</h1>
            <p class="text-lead mt-6 max-w-2xl text-ink-700">
                {{ t('public.category.description', { count: inspectorCount }) }}
            </p>
            <Link
                href="/request"
                class="mt-8 inline-block rounded-pill bg-green-500 px-8 py-4 text-base font-bold text-white transition hover:bg-green-600"
            >
                {{ t('public.category.cta') }}
            </Link>
        </div>
    </section>

    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading :line1="t('public.category.chooseLine1')" :line2="t('public.category.chooseLine2')" />
            <div class="mt-12">
                <ServiceGrid :service-types="serviceTypes" />
            </div>
        </div>
    </section>
</template>
