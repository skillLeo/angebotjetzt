<script setup lang="ts">
import FutureCategories from '@/components/home/FutureCategories.vue';
import HeroSection from '@/components/home/HeroSection.vue';
import HowItWorks from '@/components/home/HowItWorks.vue';
import LiveRequestsCarousel from '@/components/home/LiveRequestsCarousel.vue';
import MapSection from '@/components/home/MapSection.vue';
import ProviderCarousel from '@/components/home/ProviderCarousel.vue';
import RecruitmentCta from '@/components/home/RecruitmentCta.vue';
import StatsBand from '@/components/home/StatsBand.vue';
import TestimonialBand from '@/components/home/TestimonialBand.vue';
import FaqAccordion from '@/components/marketing/FaqAccordion.vue';
import SectionHeading from '@/components/marketing/SectionHeading.vue';
import StarRating from '@/components/marketing/StarRating.vue';
import { Head } from '@inertiajs/vue3';
import { BadgeCheck, CircleDollarSign } from 'lucide-vue-next';
import { computed } from 'vue';


defineProps<{
    categories: Array<{ id: number; name: string; slug: string; icon: string; description: string; is_active: boolean }>;
    serviceTypes: Array<{ id: number; name: string; slug: string; description: string; image: string }>;
    stats: { bookings: number; inspectors: number; avgOffers: number; avgResponseHours: number };
    providers: Array<{ name: string; city: string | null; reviews: number; rating: number | null; jobs: number; since: string | null; photo: string | null; isPlaceholder: boolean }>;
    recentRequests: Array<{ title: string; service: string; ort: string; plz: string; price: number | null; photo: string }>;
    reviews: Array<{ name: string; text: string | null; rating: number; service: string | null; city: string | null; isPlaceholder: boolean }>;
    cityCounts: Record<string, number>;
    totalReviews: number;
}>();

const heroImage = 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?q=80&w=1200&auto=format&fit=crop';
const testimonialPerson = 'https://images.unsplash.com/photo-1554151228-14d9def656e4?q=80&w=700&auto=format&fit=crop';
const recruitmentImage = 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?q=80&w=900&auto=format&fit=crop';

const faqItems = computed(() => [
    { q: 'Was kostet mich eine Anfrage?', a: 'Ihre Anfrage und der Angebotsvergleich sind für Sie als Kunde vollständig kostenlos und unverbindlich. Sie zahlen ausschließlich den Preis des Anbieters, den Sie beauftragen.' },
    { q: 'Wie schnell erhalte ich Angebote?', a: 'In der Regel treffen die ersten Angebote innerhalb weniger Stunden ein. Da wir alle passenden Anbieter aus Ihrer Region automatisch benachrichtigen, erhalten Sie meist mehrere Angebote zum Vergleich.' },
    { q: 'Sind die Anbieter geprüft?', a: 'Ja. Alle Anbieter auf AngebotJetzt durchlaufen eine Prüfung, bevor sie Anfragen erhalten. Viele stammen aus unserem etablierten Netzwerk und sind seit Jahren aktiv.' },
    { q: 'Wie funktioniert die Bezahlung?', a: 'Sie bezahlen sicher online im Moment der Beauftragung – direkt über unsere verschlüsselte Zahlungsabwicklung. Kein Papierkram, keine Vorkasse per Überweisung.' },
    { q: 'Kann ich zwischen mehreren Angeboten wählen?', a: 'Selbstverständlich. Sie sehen alle eingegangenen Angebote übersichtlich nebeneinander – mit Preis, Anbieterprofil und Bewertung – und entscheiden in Ruhe, wen Sie beauftragen.' },
]);

const press = ['Handelsblatt', 'Gründerszene', 't3n', 'FOCUS', 'WirtschaftsWoche', 'Capital'];
</script>

<template>
    <Head>
        <title>{{ 'Angebote vergleichen und Anbieter beauftragen' }}</title>
        <meta name="description" :content="'AngebotJetzt: Geprüfte Anbieter aus Ihrer Region senden Ihnen individuelle Angebote. Vergleichen Sie und beauftragen Sie online – kostenlos und unverbindlich.'" />
    </Head>

    <!-- 02 HERO -->
    <HeroSection :categories="categories" :service-types="serviceTypes" :hero-image="heroImage" />

    <!-- 03 TRUST STRIP -->
    <section class="bg-white pt-10 pb-4 lg:pt-14">
        <div class="mx-auto grid max-w-7xl gap-4 px-4 sm:px-6 md:grid-cols-3 lg:px-8">
            <div class="flex items-center gap-4 rounded-card border border-ink-100 bg-white p-5">
                <span class="font-display text-3xl font-extrabold text-navy-700">4,9</span>
                <div>
                    <StarRating :rating="5" :size="15" />
                    <p class="mt-1 text-sm text-ink-500">{{ totalReviews }}{{ '+ Bewertungen' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-card border border-ink-100 bg-white p-5">
                <span class="flex h-11 w-11 items-center justify-center rounded-pill bg-green-50 text-green-600">
                    <CircleDollarSign :size="22" aria-hidden="true" />
                </span>
                <div>
                    <p class="font-display text-lg font-bold text-navy-700">{{ '0 € für Ihre Anfrage' }}</p>
                    <p class="text-sm text-ink-500">{{ 'Kostenlos & unverbindlich' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-card border border-ink-100 bg-white p-5">
                <span class="flex h-11 w-11 items-center justify-center rounded-pill bg-green-50 text-green-600">
                    <BadgeCheck :size="22" aria-hidden="true" />
                </span>
                <div>
                    <p class="font-display text-lg font-bold text-navy-700">{{ 'Geprüfte Anbieter' }}</p>
                    <p class="text-sm text-ink-500">{{ 'In ganz Deutschland' }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 04 SERVICE CATEGORY GRID -->
    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading :eyebrow="'Unsere Services'" :line1="'Für jeden Bedarf'" :line2="'der passende Anbieter'" />
            <div class="mt-10">
                <FutureCategories :categories="categories" />
            </div>
        </div>
    </section>

    <!-- 05 HOW IT WORKS -->
    <HowItWorks />

    <!-- 06 LIVE REQUESTS CAROUSEL -->
    <section v-if="recentRequests.length" class="bg-sand-50 py-14 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading :eyebrow="'Aktuelle Anfragen'" :line1="'Diese Anfragen suchen'" :line2="'gerade den passenden Anbieter'" />
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
            <SectionHeading :eyebrow="'Unser Netzwerk'" :line1="'Unsere geprüften'" :line2="'Anbieter'" />
            <div class="mt-8">
                <ProviderCarousel :providers="providers" />
            </div>
        </div>
    </section>

    <!-- 12 RECRUITMENT CTA -->
    <RecruitmentCta :image="recruitmentImage" />

    <!-- 13 PRESS STRIP -->
    <section class="bg-sand-50 py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-panel bg-white px-6 py-8 shadow-card">
                <p class="text-eyebrow mb-6 text-center text-ink-500">{{ 'Bekannt aus' }}</p>
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
            <SectionHeading centered :eyebrow="'Häufige Fragen'" :line1="'Alles, was Sie'" :line2="'wissen müssen'" />
            <div class="mt-10">
                <FaqAccordion :items="faqItems" />
            </div>
        </div>
    </section>
</template>
