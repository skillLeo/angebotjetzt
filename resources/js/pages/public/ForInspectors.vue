<script setup lang="ts">
import HandDrawnFrame from '@/components/marketing/HandDrawnFrame.vue';
import SectionHeading from '@/components/marketing/SectionHeading.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Bell, Check, MapPin, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{ stats: { inspectors: number; requests: number } }>();

const steps = computed(() => [
    { icon: Bell, title: t('public.forInspectors.step1Title'), text: t('public.forInspectors.step1Text') },
    { icon: MapPin, title: t('public.forInspectors.step2Title'), text: t('public.forInspectors.step2Text') },
    { icon: Wallet, title: t('public.forInspectors.step3Title'), text: t('public.forInspectors.step3Text') },
]);
const benefits = computed(() => [
    t('public.forInspectors.benefit1'),
    t('public.forInspectors.benefit2'),
    t('public.forInspectors.benefit3'),
    t('public.forInspectors.benefit4'),
    t('public.forInspectors.benefit5'),
    t('public.forInspectors.benefit6'),
]);
const recruitmentImage = 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?q=80&w=900&auto=format&fit=crop';
</script>

<template>
    <Head>
        <title>{{ t('public.forInspectors.metaTitle') }}</title>
        <meta name="description" :content="t('public.forInspectors.metaDescription')" />
    </Head>

    <section class="bg-navy-900 px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-2">
            <div>
                <p class="text-eyebrow mb-4 text-green-400">{{ t('public.forInspectors.eyebrow') }}</p>
                <h1 class="text-hero text-white">{{ t('public.forInspectors.title') }}</h1>
                <p class="text-lead mt-6 max-w-lg text-navy-100">
                    {{ t('public.forInspectors.description', { count: stats.inspectors }) }}
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <Link href="/registrieren/gutachter" class="rounded-pill bg-green-500 px-7 py-3.5 text-center font-bold text-white transition hover:bg-green-600">
                        {{ t('public.forInspectors.register') }}
                    </Link>
                    <Link href="/gutachter/login" class="rounded-pill border border-navy-500 px-7 py-3.5 text-center font-bold text-white transition hover:bg-navy-800">
                        {{ t('public.forInspectors.toLogin') }}
                    </Link>
                </div>
            </div>
            <div class="relative">
                <img :src="recruitmentImage" width="600" height="440" :alt="t('public.forInspectors.imageAlt')" class="aspect-[16/11] w-full rounded-panel object-cover shadow-lift" />
                <HandDrawnFrame :label="t('public.forInspectors.badgeLabel')" />
            </div>
        </div>
    </section>

    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading :line1="t('public.forInspectors.howLine1')" :line2="t('public.forInspectors.howLine2')" />
            <div class="mt-12 grid gap-5 md:grid-cols-3">
                <div v-for="s in steps" :key="s.title" class="rounded-card border border-ink-100 bg-white p-8">
                    <span class="flex h-12 w-12 items-center justify-center rounded-card bg-green-50 text-green-600">
                        <component :is="s.icon" :size="24" aria-hidden="true" />
                    </span>
                    <h3 class="mt-5 font-display text-lg font-bold text-navy-700">{{ s.title }}</h3>
                    <p class="mt-2 text-[15px] leading-relaxed text-ink-500">{{ s.text }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-sand-50 py-16 lg:py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <SectionHeading centered :line1="t('public.forInspectors.benefitsLine1')" :line2="t('public.forInspectors.benefitsLine2')" />
            <ul class="mt-10 grid gap-3 sm:grid-cols-2">
                <li v-for="b in benefits" :key="b" class="flex items-center gap-3 rounded-card bg-white px-5 py-4 shadow-card">
                    <Check :size="20" class="shrink-0 text-green-500" aria-hidden="true" />
                    <span class="text-[15px] font-medium text-ink-700">{{ b }}</span>
                </li>
            </ul>
        </div>
    </section>
</template>
