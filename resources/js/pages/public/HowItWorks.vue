<script setup lang="ts">
import HowItWorks from '@/components/home/HowItWorks.vue';
import FaqAccordion from '@/components/marketing/FaqAccordion.vue';
import SectionHeading from '@/components/marketing/SectionHeading.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';


const faq = computed(() => [
    { q: 'Ist die Anfrage wirklich kostenlos?', a: 'Ja. Anfrage und Angebotsvergleich sind für Kunden vollständig kostenlos und unverbindlich.' },
    { q: 'Wie viele Angebote erhalte ich?', a: 'Das hängt von den verfügbaren Gutachtern in Ihrer Region ab – in der Regel erhalten Sie mehrere Angebote zum Vergleich.' },
    { q: 'Wann bezahle ich?', a: 'Erst wenn Sie ein Angebot annehmen. Die Zahlung erfolgt sicher online im selben Schritt.' },
]);
</script>

<template>
    <Head>
        <title>{{ 'So funktioniert\'s' }}</title>
        <meta name="description" :content="'In drei einfachen Schritten zum passenden Kfz-Gutachten: Anfrage stellen, Angebote vergleichen, Gutachter beauftragen.'" />
    </Head>

    <section class="bg-sand-100 px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <p class="text-eyebrow mb-4 text-green-600">{{ 'In drei Schritten' }}</p>
            <h1 class="text-hero max-w-3xl text-navy-700">{{ 'So einfach kommen Sie zu Ihrem Gutachten' }}</h1>
            <p class="text-lead mt-6 max-w-2xl text-ink-700">
                {{ 'Kein Suchen, kein Telefonieren, kein Papierkram. Beschreiben Sie Ihr Anliegen einmal – den Rest übernimmt AngebotJetzt.' }}
            </p>
        </div>
    </section>

    <HowItWorks />

    <section class="bg-sand-50 py-16 lg:py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <SectionHeading centered :line1="'Noch Fragen?'" :line2="'Wir haben Antworten'" />
            <div class="mt-10">
                <FaqAccordion :items="faq" />
            </div>
            <div class="mt-10 text-center">
                <Link href="/request" class="inline-block rounded-pill bg-green-500 px-8 py-4 text-base font-bold text-white transition hover:bg-green-600">
                    {{ 'Jetzt Anfrage stellen' }}
                </Link>
            </div>
        </div>
    </section>
</template>
