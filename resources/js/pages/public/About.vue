<script setup lang="ts">
import SectionHeading from '@/components/marketing/SectionHeading.vue';
import { Head } from '@inertiajs/vue3';
import { Handshake, ShieldCheck, Zap } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const values = computed(() => [
    { icon: ShieldCheck, title: t('public.about.value1Title'), text: t('public.about.value1Text') },
    { icon: Zap, title: t('public.about.value2Title'), text: t('public.about.value2Text') },
    { icon: Handshake, title: t('public.about.value3Title'), text: t('public.about.value3Text') },
]);
</script>

<template>
    <Head>
        <title>{{ t('public.about.metaTitle') }}</title>
        <meta name="description" :content="t('public.about.metaDescription')" />
    </Head>

    <section class="bg-sand-100 px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="mx-auto max-w-7xl">
            <p class="text-eyebrow mb-4 text-green-600">{{ t('public.about.eyebrow') }}</p>
            <h1 class="text-hero max-w-3xl text-navy-700">{{ t('public.about.title') }}</h1>
            <p class="text-lead mt-6 max-w-2xl text-ink-700">
                {{ t('public.about.description') }}
            </p>
        </div>
    </section>

    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading :line1="t('public.about.valuesLine1')" :line2="t('public.about.valuesLine2')" />
            <div class="mt-12 grid gap-5 md:grid-cols-3">
                <div v-for="v in values" :key="v.title" class="rounded-card border border-ink-100 bg-white p-8">
                    <span class="flex h-12 w-12 items-center justify-center rounded-card bg-green-50 text-green-600">
                        <component :is="v.icon" :size="24" aria-hidden="true" />
                    </span>
                    <h3 class="mt-5 font-display text-lg font-bold text-navy-700">{{ v.title }}</h3>
                    <p class="mt-2 text-[15px] leading-relaxed text-ink-500">{{ v.text }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-navy-900 py-16 lg:py-20">
        <div class="mx-auto grid max-w-7xl grid-cols-2 gap-y-10 px-4 sm:px-6 lg:grid-cols-4 lg:px-8">
            <div class="border-l-4 border-green-500 pl-5">
                <p class="font-display text-4xl font-extrabold text-white">8.000+</p>
                <p class="mt-1 text-sm text-navy-100">{{ t('public.about.stat1Label') }}</p>
            </div>
            <div class="border-l-4 border-green-500 pl-5">
                <p class="font-display text-4xl font-extrabold text-white">25+</p>
                <p class="mt-1 text-sm text-navy-100">{{ t('public.about.stat2Label') }}</p>
            </div>
            <div class="border-l-4 border-green-500 pl-5">
                <p class="font-display text-4xl font-extrabold text-white">4,9</p>
                <p class="mt-1 text-sm text-navy-100">{{ t('public.about.stat3Label') }}</p>
            </div>
            <div class="border-l-4 border-green-500 pl-5">
                <p class="font-display text-4xl font-extrabold text-white">100 %</p>
                <p class="mt-1 text-sm text-navy-100">{{ t('public.about.stat4Label') }}</p>
            </div>
        </div>
    </section>
</template>
