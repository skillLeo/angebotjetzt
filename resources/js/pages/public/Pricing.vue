<script setup lang="ts">
import SectionHeading from '@/components/marketing/SectionHeading.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const customerPoints = computed(() => [
    t('public.pricing.customerPoint1'),
    t('public.pricing.customerPoint2'),
    t('public.pricing.customerPoint3'),
    t('public.pricing.customerPoint4'),
    t('public.pricing.customerPoint5'),
]);
const inspectorPoints = computed(() => [
    t('public.pricing.inspectorPoint1'),
    t('public.pricing.inspectorPoint2'),
    t('public.pricing.inspectorPoint3'),
    t('public.pricing.inspectorPoint4'),
    t('public.pricing.inspectorPoint5'),
]);
</script>

<template>
    <Head>
        <title>{{ t('public.pricing.metaTitle') }}</title>
        <meta name="description" :content="t('public.pricing.metaDescription')" />
    </Head>

    <section class="bg-sand-100 px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl text-center">
            <p class="text-eyebrow mb-4 text-green-600">{{ t('public.pricing.eyebrow') }}</p>
            <h1 class="text-hero mx-auto max-w-3xl text-navy-700">{{ t('public.pricing.title') }}</h1>
            <p class="text-lead mx-auto mt-6 max-w-2xl text-ink-700">
                {{ t('public.pricing.description') }}
            </p>
        </div>
    </section>

    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto grid max-w-5xl gap-6 px-4 sm:px-6 md:grid-cols-2 lg:px-8">
            <div class="rounded-panel border border-ink-100 bg-white p-8 shadow-card">
                <p class="text-eyebrow text-green-600">{{ t('public.pricing.forCustomers') }}</p>
                <p class="mt-4 font-display text-5xl font-extrabold text-navy-700">0 €</p>
                <p class="mt-1 text-ink-500">{{ t('public.pricing.customerPriceLabel') }}</p>
                <ul class="mt-7 space-y-3">
                    <li v-for="p in customerPoints" :key="p" class="flex items-start gap-3">
                        <Check :size="20" class="mt-0.5 shrink-0 text-green-500" aria-hidden="true" />
                        <span class="text-[15px] text-ink-700">{{ p }}</span>
                    </li>
                </ul>
                <Link href="/anfrage" class="mt-8 block rounded-pill bg-green-500 py-3.5 text-center font-bold text-white transition hover:bg-green-600">
                    {{ t('public.pricing.customerCta') }}
                </Link>
            </div>

            <div class="rounded-panel border-2 border-navy-700 bg-navy-900 p-8 text-white shadow-lift">
                <p class="text-eyebrow text-green-400">{{ t('public.pricing.forInspectors') }}</p>
                <p class="mt-4 font-display text-5xl font-extrabold">10 %</p>
                <p class="mt-1 text-navy-100">{{ t('public.pricing.inspectorPriceLabel') }}</p>
                <ul class="mt-7 space-y-3">
                    <li v-for="p in inspectorPoints" :key="p" class="flex items-start gap-3">
                        <Check :size="20" class="mt-0.5 shrink-0 text-green-400" aria-hidden="true" />
                        <span class="text-[15px] text-navy-100">{{ p }}</span>
                    </li>
                </ul>
                <Link href="/fuer-gutachter" class="mt-8 block rounded-pill bg-green-500 py-3.5 text-center font-bold text-white transition hover:bg-green-600">
                    {{ t('public.pricing.inspectorCta') }}
                </Link>
            </div>
        </div>
    </section>

    <section class="bg-sand-50 py-16">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6">
            <SectionHeading centered :line1="t('public.pricing.exampleLine1')" :line2="t('public.pricing.exampleLine2')" />
            <div class="mt-8 rounded-panel border border-ink-100 bg-white p-8 text-left shadow-card">
                <dl class="space-y-3 text-[15px]">
                    <div class="flex justify-between border-b border-ink-100 pb-3">
                        <dt class="text-ink-500">{{ t('public.pricing.examplePrice') }}</dt>
                        <dd class="font-bold text-navy-700">300,00 €</dd>
                    </div>
                    <div class="flex justify-between border-b border-ink-100 pb-3">
                        <dt class="text-ink-500">{{ t('public.pricing.exampleCommission') }}</dt>
                        <dd class="font-bold text-navy-700">30,00 €</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-500">{{ t('public.pricing.examplePayout') }}</dt>
                        <dd class="font-display text-lg font-extrabold text-green-600">270,00 €</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>
</template>
