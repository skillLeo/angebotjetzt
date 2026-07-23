<script setup lang="ts">
import SectionHeading from '@/components/marketing/SectionHeading.vue';
import { Head } from '@inertiajs/vue3';
import { Handshake, ShieldCheck, Zap } from 'lucide-vue-next';
import { computed } from 'vue';


const values = computed(() => [
    { icon: ShieldCheck, title: 'Vertrauen', text: 'Nur geprüfte, unabhängige Sachverständige. Transparenz bei Preisen und Bewertungen.' },
    { icon: Zap, title: 'Einfachheit', text: 'Eine Anfrage genügt. Wir übernehmen die Suche, den Vergleich und die sichere Abwicklung.' },
    { icon: Handshake, title: 'Fairness', text: 'Faire Konditionen für beide Seiten – für Kunden kostenlos, für Gutachter ohne Grundgebühr.' },
]);
</script>

<template>
    <Head>
        <title>{{ 'Über uns' }}</title>
        <meta name="description" :content="'AngebotJetzt verbindet Kunden mit geprüften Kfz-Sachverständigen in ganz Deutschland – einfach, transparent und fair.'" />
    </Head>

    <section class="bg-sand-100 px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="mx-auto max-w-7xl">
            <p class="text-eyebrow mb-4 text-green-600">{{ 'Über AngebotJetzt' }}</p>
            <h1 class="text-hero max-w-3xl text-navy-700">{{ 'Der einfachste Weg zum richtigen Gutachten' }}</h1>
            <p class="text-lead mt-6 max-w-2xl text-ink-700">
                {{ 'AngebotJetzt ist der Marktplatz, der Autofahrer mit geprüften Kfz-Sachverständigen zusammenbringt. Aus einem etablierten Gutachternetzwerk entstanden, verbinden wir echte Fachleute mit Menschen, die schnell ein verlässliches Gutachten brauchen.' }}
            </p>
        </div>
    </section>

    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading :line1="'Wofür wir'" :line2="'stehen'" />
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

    <section class="bg-navy-900 py-16 lg:py-24">
        <div class="mx-auto grid max-w-7xl grid-cols-2 gap-y-10 px-4 sm:px-6 lg:grid-cols-4 lg:px-8">
            <div class="border-l-4 border-green-500 pl-5">
                <p class="font-display text-4xl font-extrabold text-white">8.000+</p>
                <p class="mt-1 text-sm text-navy-100">{{ 'Vermittelte Gutachten' }}</p>
            </div>
            <div class="border-l-4 border-green-500 pl-5">
                <p class="font-display text-4xl font-extrabold text-white">25+</p>
                <p class="mt-1 text-sm text-navy-100">{{ 'Geprüfte Gutachter' }}</p>
            </div>
            <div class="border-l-4 border-green-500 pl-5">
                <p class="font-display text-4xl font-extrabold text-white">4,9</p>
                <p class="mt-1 text-sm text-navy-100">{{ 'Ø Bewertung' }}</p>
            </div>
            <div class="border-l-4 border-green-500 pl-5">
                <p class="font-display text-4xl font-extrabold text-white">100 %</p>
                <p class="mt-1 text-sm text-navy-100">{{ 'Deutschlandweit' }}</p>
            </div>
        </div>
    </section>
</template>
