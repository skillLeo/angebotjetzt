<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import { computed } from 'vue';


const customerPoints = computed(() => [
    'Anfrage stellen – kostenlos',
    'Mehrere Angebote vergleichen',
    'Keine versteckten Gebühren',
    'Sichere Online-Zahlung',
    'Nur der Gutachterpreis fällt an',
]);
const inspectorPoints = computed(() => [
    'Passende Anfragen automatisch per E-Mail',
    'Eigene Preise pro Auftrag festlegen',
    'Faire, transparente Provision je Buchung',
    'Guthaben im Wallet, jederzeit auszahlbar',
    'Keine monatliche Grundgebühr',
]);
</script>

<template>
    <Head>
        <title>{{ 'Preise & Provision' }}</title>
        <meta name="description" :content="'Für Kunden kostenlos. Für Gutachter eine faire, transparente Provision je Buchung.'" />
    </Head>

    <section class="bg-sand-100 px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl text-center">
            <p class="text-eyebrow mb-4 text-green-600">{{ 'Transparent & fair' }}</p>
            <h1 class="text-hero mx-auto max-w-3xl text-navy-700">{{ 'Faire Preise, keine Überraschungen' }}</h1>
            <p class="text-lead mx-auto mt-6 max-w-2xl text-ink-700">
                {{ 'Für Kunden ist AngebotJetzt komplett kostenlos. Gutachter zahlen lediglich eine faire Provision je erfolgreich vermitteltem Auftrag.' }}
            </p>
        </div>
    </section>

    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto grid max-w-5xl gap-6 px-4 sm:px-6 md:grid-cols-2 lg:px-8">
            <div class="rounded-panel border border-ink-100 bg-white p-8 shadow-card">
                <p class="text-eyebrow text-green-600">{{ 'Für Kunden' }}</p>
                <p class="mt-4 font-display text-5xl font-extrabold text-navy-700">0 €</p>
                <p class="mt-1 text-ink-500">{{ 'für Anfrage & Vergleich' }}</p>
                <ul class="mt-7 space-y-3">
                    <li v-for="p in customerPoints" :key="p" class="flex items-start gap-3">
                        <Check :size="20" class="mt-0.5 shrink-0 text-green-500" aria-hidden="true" />
                        <span class="text-[15px] text-ink-700">{{ p }}</span>
                    </li>
                </ul>
                <Link href="/request" class="mt-8 block rounded-pill bg-green-500 py-3.5 text-center font-bold text-white transition hover:bg-green-600">
                    {{ 'Anfrage stellen' }}
                </Link>
            </div>

            <div class="rounded-panel border-2 border-navy-700 bg-navy-900 p-8 text-white shadow-lift">
                <p class="text-eyebrow text-green-400">{{ 'Für Gutachter' }}</p>
                <p class="mt-4 font-display text-5xl font-extrabold">{{ 'Fair' }}</p>
                <p class="mt-1 text-navy-100">{{ 'Provision je Buchung' }}</p>
                <ul class="mt-7 space-y-3">
                    <li v-for="p in inspectorPoints" :key="p" class="flex items-start gap-3">
                        <Check :size="20" class="mt-0.5 shrink-0 text-green-400" aria-hidden="true" />
                        <span class="text-[15px] text-navy-100">{{ p }}</span>
                    </li>
                </ul>
                <Link href="/for-inspectors" class="mt-8 block rounded-pill bg-green-500 py-3.5 text-center font-bold text-white transition hover:bg-green-600">
                    {{ 'Als Gutachter registrieren' }}
                </Link>
            </div>
        </div>
    </section>
</template>
