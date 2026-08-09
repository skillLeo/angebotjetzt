<script setup lang="ts">
import FaqAccordion from '@/components/marketing/FaqAccordion.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';


const sections = computed(() => [
    {
        title: 'Für Kunden',
        items: [
            { q: 'Was kostet mich eine Anfrage?', a: 'Ihre Anfrage und der Angebotsvergleich sind vollständig kostenlos und unverbindlich. Sie zahlen ausschließlich den Preis des beauftragten Anbieters.' },
            { q: 'Wie schnell erhalte ich Angebote?', a: 'In der Regel treffen die ersten Angebote innerhalb weniger Stunden ein.' },
            { q: 'Kann ich mehrere Angebote vergleichen?', a: 'Ja. Alle Angebote werden übersichtlich nebeneinander mit Preis, Anbieterprofil und Bewertung dargestellt.' },
            { q: 'Wie bezahle ich?', a: 'Nach der verbindlichen Annahme eines Angebots vereinbaren Sie die Zahlung direkt mit dem Dienstleister. Die Plattform ist an dieser Zahlung nicht beteiligt.' },
        ],
    },
    {
        title: 'Für Anbieter',
        items: [
            { q: 'Wie erhalte ich Anfragen?', a: 'Passende Anfragen aus Ihrem Servicegebiet werden Ihnen automatisch per E-Mail und im Dashboard angezeigt.' },
            { q: 'Wie hoch ist die Provision?', a: 'Für jeden erfolgreich vermittelten Auftrag stellt die Plattform Ihnen eine Provision von 10 % des Auftragswerts in Rechnung. Es gibt keine monatliche Grundgebühr.' },
            { q: 'Wie und wann bezahle ich die Provision?', a: 'Sobald Sie ein Angebot gewinnen, erhalten Sie automatisch eine Provisionsrechnung mit 14 Tagen Zahlungsfrist per E-Mail sowie jederzeit zum Abruf unter „Rechnungen" in Ihrem Dashboard.' },
        ],
    },
]);
</script>

<template>
    <Head>
        <title>{{ 'Häufige Fragen' }}</title>
        <meta name="description" :content="'Antworten auf die häufigsten Fragen rund um Anfragen, Angebote, Bezahlung und das Anbieter-Portal.'" />
    </Head>

    <section class="bg-sand-100 px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <p class="text-eyebrow mb-4 text-green-600">{{ 'Hilfe & Support' }}</p>
            <h1 class="text-hero max-w-3xl text-navy-700">{{ 'Häufig gestellte Fragen' }}</h1>
        </div>
    </section>

    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-3xl space-y-12 px-4 sm:px-6 lg:px-8">
            <div v-for="section in sections" :key="section.title">
                <h2 class="text-subsection mb-5 text-navy-700">{{ section.title }}</h2>
                <FaqAccordion :items="section.items" />
            </div>
            <div class="rounded-panel bg-navy-900 p-8 text-center">
                <p class="font-display text-xl font-bold text-white">{{ 'Ihre Frage ist nicht dabei?' }}</p>
                <Link href="/contact" class="mt-5 inline-block rounded-pill bg-green-500 px-7 py-3 font-bold text-white transition hover:bg-green-600">
                    {{ 'Kontakt aufnehmen' }}
                </Link>
            </div>
        </div>
    </section>
</template>
