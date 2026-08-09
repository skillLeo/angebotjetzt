<script setup lang="ts">
import ServiceGrid from '@/components/home/ServiceGrid.vue';
import FaqAccordion from '@/components/marketing/FaqAccordion.vue';
import Reveal from '@/components/marketing/Reveal.vue';
import SectionHeading from '@/components/marketing/SectionHeading.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';


defineProps<{
    serviceTypes: Array<{ id: number; name: string; slug: string; description: string; image: string }>;
    inspectorCount: number;
}>();

const cities = ['Berlin', 'Hamburg', 'München', 'Köln', 'Frankfurt am Main', 'Stuttgart', 'Düsseldorf', 'Dortmund', 'Essen', 'Leipzig', 'Bremen', 'Dresden', 'Hannover', 'Nürnberg', 'Duisburg', 'Bochum', 'Wuppertal', 'Bonn', 'Münster', 'Mannheim', 'Karlsruhe', 'Augsburg'];

const faq = computed(() => [
    { q: 'Sind die Dienstleister geprüft?', a: 'Ja. Alle Dienstleister auf AngebotJetzt sind geprüfte, oft öffentlich bestellte und vereidigte Kfz-Sachverständige. Viele stammen aus unserem etablierten Netzwerk und sind seit Jahren aktiv.' },
    { q: 'Für welche Fahrzeuge gibt es Gutachten?', a: 'Von PKW über Motorräder und Nutzfahrzeuge bis hin zu Oldtimern und Elektrofahrzeugen. Wählen Sie einfach die passende Gutachten-Art und beschreiben Sie Ihr Fahrzeug.' },
]);
</script>

<template>
    <Head>
        <title>{{ 'Kfz-Gutachten – alle Leistungen im Überblick' }}</title>
        <meta name="description" :content="'Von der Unfallschadenanalyse bis zum Gebrauchtwagencheck: Finden Sie den passenden Kfz-Sachverständigen und vergleichen Sie Angebote.'" />
    </Head>

    <section class="bg-sand-100 px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="mx-auto max-w-7xl">
            <p class="text-eyebrow mb-4 text-green-600">{{ 'Kfz-Dienstleistungen' }}</p>
            <h1 class="text-hero max-w-3xl text-navy-700">{{ 'Kfz-Gutachten von geprüften Sachverständigen' }}</h1>
            <p class="text-lead mt-6 max-w-2xl text-ink-700">
                {{ `${inspectorCount} geprüfte Dienstleister stehen bundesweit bereit, um Ihnen ein individuelles Angebot zu machen. Wählen Sie das benötigte Gutachten, beschreiben Sie Ihr Fahrzeug und Ihre Region.` }}
            </p>
            <p class="mt-3 text-sm text-ink-500">
                {{ 'Bereits über 8.000 Kfz-Gutachten in ganz Deutschland vermittelt.' }}
            </p>
            <Link
                href="/request"
                class="mt-8 inline-block rounded-pill bg-green-500 px-8 py-4 text-base font-bold text-white transition hover:bg-green-600"
            >
                {{ 'Jetzt kostenlos Angebote erhalten' }}
            </Link>
        </div>
    </section>

    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading :line1="'Wählen Sie Ihr'" :line2="'Kfz-Gutachten'" />
            <div class="mt-12">
                <ServiceGrid :service-types="serviceTypes" />
            </div>
        </div>
    </section>

    <section class="bg-sand-50 py-16 lg:py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <SectionHeading centered :eyebrow="'Häufige Fragen'" :line1="'Fragen zum'" :line2="'Kfz-Gutachten'" />
            <div class="mt-10">
                <FaqAccordion :items="faq" />
            </div>
        </div>
    </section>

    <section class="bg-white py-14 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading :line1="'Kfz-Dienstleister'" :line2="'in Ihrer Stadt finden'" />
            <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3">
                <Reveal v-for="(city, i) in cities" :key="city" :delay="(i % 6) * 0.03">
                    <Link
                        href="/vehicle-reports"
                        class="block rounded-pill border border-ink-100 bg-white px-5 py-3 text-center text-sm font-semibold text-navy-700 transition hover:border-green-500 hover:text-green-600"
                    >
                        {{ 'Dienstleister' }} {{ city }}
                    </Link>
                </Reveal>
            </div>
        </div>
    </section>
</template>
