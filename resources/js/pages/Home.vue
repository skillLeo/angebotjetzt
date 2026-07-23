<script setup lang="ts">
import FutureCategories from '@/components/home/FutureCategories.vue';
import HeroSection from '@/components/home/HeroSection.vue';
import HowItWorks from '@/components/home/HowItWorks.vue';
import LiveRequestsCarousel from '@/components/home/LiveRequestsCarousel.vue';
import MapSection from '@/components/home/MapSection.vue';
import ProviderCarousel from '@/components/home/ProviderCarousel.vue';
import RecruitmentCta from '@/components/home/RecruitmentCta.vue';
import ServiceGrid from '@/components/home/ServiceGrid.vue';
import StatsBand from '@/components/home/StatsBand.vue';
import TestimonialBand from '@/components/home/TestimonialBand.vue';
import FaqAccordion from '@/components/marketing/FaqAccordion.vue';
import Reveal from '@/components/marketing/Reveal.vue';
import SectionHeading from '@/components/marketing/SectionHeading.vue';
import StarRating from '@/components/marketing/StarRating.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BadgeCheck, CircleDollarSign } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    categories: Array<{ id: number; name: string; slug: string; icon: string; description: string; is_active: boolean }>;
    serviceTypes: Array<{ id: number; name: string; slug: string; description: string; image: string }>;
    stats: { bookings: number; inspectors: number; avgOffers: number; avgResponseHours: number };
    providers: Array<{ name: string; city: string | null; reviews: number; rating: number | null; jobs: number; since: string | null; photo: string }>;
    recentRequests: Array<{ title: string; service: string; ort: string; plz: string; price: number | null; photo: string }>;
    reviews: Array<{ name: string; text: string | null; rating: number; service: string | null; city: string | null }>;
    cityCounts: Record<string, number>;
    totalReviews: number;
}>();

const heroImage = 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?q=80&w=1200&auto=format&fit=crop';
const testimonialPerson = 'https://images.unsplash.com/photo-1554151228-14d9def656e4?q=80&w=700&auto=format&fit=crop';
const recruitmentImage = 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?q=80&w=900&auto=format&fit=crop';

const faqItems = computed(() => [
    { q: t('home.faq.q1'), a: t('home.faq.a1') },
    { q: t('home.faq.q2'), a: t('home.faq.a2') },
    { q: t('home.faq.q3'), a: t('home.faq.a3') },
    { q: t('home.faq.q4'), a: t('home.faq.a4') },
    { q: t('home.faq.q5'), a: t('home.faq.a5') },
    { q: t('home.faq.q6'), a: t('home.faq.a6') },
]);

const cities = ['Berlin', 'Hamburg', 'München', 'Köln', 'Frankfurt am Main', 'Stuttgart', 'Düsseldorf', 'Dortmund', 'Essen', 'Leipzig', 'Bremen', 'Dresden', 'Hannover', 'Nürnberg', 'Duisburg', 'Bochum', 'Wuppertal', 'Bonn', 'Münster', 'Mannheim', 'Karlsruhe', 'Augsburg'];
const press = ['Handelsblatt', 'auto motor sport', 'ADAC', 'FOCUS', 'WirtschaftsWoche', 'kfz-betrieb'];
</script>

<template>
    <Head>
        <title>{{ t('home.meta.title') }}</title>
        <meta name="description" :content="t('home.meta.description')" />
    </Head>

    <!-- 02 HERO -->
    <HeroSection :service-types="serviceTypes" :hero-image="heroImage" />

    <!-- 03 TRUST STRIP -->
    <section class="bg-white pb-4">
        <div class="mx-auto grid max-w-7xl gap-4 px-4 sm:px-6 md:grid-cols-3 lg:px-8">
            <div class="flex items-center gap-4 rounded-card border border-ink-100 bg-white p-5">
                <span class="font-display text-3xl font-extrabold text-navy-700">4,9</span>
                <div>
                    <StarRating :rating="5" :size="15" />
                    <p class="mt-1 text-sm text-ink-500">{{ totalReviews }}{{ t('home.trustStrip.reviewsSuffix') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-card border border-ink-100 bg-white p-5">
                <span class="flex h-11 w-11 items-center justify-center rounded-pill bg-green-50 text-green-600">
                    <CircleDollarSign :size="22" aria-hidden="true" />
                </span>
                <div>
                    <p class="font-display text-lg font-bold text-navy-700">{{ t('home.trustStrip.freeTitle') }}</p>
                    <p class="text-sm text-ink-500">{{ t('home.trustStrip.freeSubtitle') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-card border border-ink-100 bg-white p-5">
                <span class="flex h-11 w-11 items-center justify-center rounded-pill bg-green-50 text-green-600">
                    <BadgeCheck :size="22" aria-hidden="true" />
                </span>
                <div>
                    <p class="font-display text-lg font-bold text-navy-700">{{ t('home.trustStrip.verifiedTitle') }}</p>
                    <p class="text-sm text-ink-500">{{ t('home.trustStrip.verifiedSubtitle') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 04 SERVICE CATEGORY GRID -->
    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading :eyebrow="t('home.services.eyebrow')" :line1="t('home.services.line1')" :line2="t('home.services.line2')" />
            <div class="mt-10">
                <ServiceGrid :service-types="serviceTypes" />
            </div>
        </div>
    </section>

    <!-- 05 HOW IT WORKS -->
    <HowItWorks />

    <!-- 06 LIVE REQUESTS CAROUSEL -->
    <section v-if="recentRequests.length" class="bg-sand-50 py-14 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading :eyebrow="t('home.liveRequests.eyebrow')" :line1="t('home.liveRequests.line1')" :line2="t('home.liveRequests.line2')" />
            <div class="mt-8">
                <LiveRequestsCarousel :requests="recentRequests" />
            </div>
        </div>
    </section>

    <!-- 07 MAP SECTION -->
    <MapSection :city-counts="cityCounts" />

    <!-- 08 STATISTICS BAND -->
    <StatsBand :stats="stats" />

    <!-- 09 TESTIMONIAL BAND -->
    <TestimonialBand v-if="reviews.length" :reviews="reviews" :person-image="testimonialPerson" />

    <!-- 10 PROVIDER CARDS CAROUSEL -->
    <section v-if="providers.length" class="bg-white py-14 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading :eyebrow="t('home.providers.eyebrow')" :line1="t('home.providers.line1')" :line2="t('home.providers.line2')" />
            <div class="mt-8">
                <ProviderCarousel :providers="providers" />
            </div>
        </div>
    </section>

    <!-- 11 FUTURE CATEGORIES -->
    <FutureCategories :categories="categories" />

    <!-- 12 RECRUITMENT CTA -->
    <RecruitmentCta :image="recruitmentImage" />

    <!-- 13 PRESS STRIP -->
    <section class="bg-sand-50 py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-panel bg-white px-6 py-8 shadow-card">
                <p class="text-eyebrow mb-6 text-center text-ink-500">{{ t('home.press.knownFrom') }}</p>
                <div class="flex flex-wrap items-center justify-center gap-x-10 gap-y-5">
                    <span
                        v-for="name in press"
                        :key="name"
                        class="font-display text-xl font-bold text-ink-300 grayscale transition hover:text-ink-500"
                    >
                        {{ name }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- 14 FAQ ACCORDION -->
    <section class="bg-white py-16 lg:py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <SectionHeading centered :eyebrow="t('home.faq.eyebrow')" :line1="t('home.faq.line1')" :line2="t('home.faq.line2')" />
            <div class="mt-10">
                <FaqAccordion :items="faqItems" />
            </div>
        </div>
    </section>

    <!-- 15 CITY SEO GRID -->
    <section class="bg-sand-50 py-14 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading :line1="t('home.citySeo.line1')" :line2="t('home.citySeo.line2')" />
            <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3">
                <Reveal v-for="(city, i) in cities" :key="city" :delay="(i % 6) * 0.03">
                    <Link
                        href="/vehicle-reports"
                        class="block rounded-pill border border-ink-100 bg-white px-5 py-3 text-center text-sm font-semibold text-navy-700 transition hover:border-green-500 hover:text-green-600"
                    >
                        {{ t('home.citySeo.inspectorPrefix') }} {{ city }}
                    </Link>
                </Reveal>
            </div>
        </div>
    </section>
</template>
