<script setup lang="ts">
import ServiceGrid from '@/components/home/ServiceGrid.vue';
import SectionHeading from '@/components/marketing/SectionHeading.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    serviceTypes: Array<{ id: number; name: string; slug: string; description: string; image: string }>;
    inspectorCount: number;
}>();
</script>

<template>
    <Head>
        <title>Kfz-Gutachten – alle Leistungen im Überblick</title>
        <meta name="description" content="Von der Unfallschadenanalyse bis zum Gebrauchtwagencheck: Finden Sie den passenden Kfz-Sachverständigen und vergleichen Sie Angebote." />
    </Head>

    <section class="bg-sand-100 px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="mx-auto max-w-7xl">
            <p class="text-eyebrow mb-4 text-green-600">Automotive Services</p>
            <h1 class="text-hero max-w-3xl text-navy-700">Kfz-Gutachten von geprüften Sachverständigen</h1>
            <p class="text-lead mt-6 max-w-2xl text-ink-700">
                Wählen Sie das benötigte Gutachten, beschreiben Sie Ihr Fahrzeug und Ihre Region –
                {{ inspectorCount }} geprüfte Gutachter stehen bundesweit bereit, um Ihnen ein
                individuelles Angebot zu machen.
            </p>
            <Link
                href="/anfrage"
                class="mt-8 inline-block rounded-pill bg-green-500 px-8 py-4 text-base font-bold text-white transition hover:bg-green-600"
            >
                Jetzt kostenlos Angebote erhalten
            </Link>
        </div>
    </section>

    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading line1="Wählen Sie Ihr" line2="Kfz-Gutachten" />
            <div class="mt-12">
                <ServiceGrid :service-types="serviceTypes" />
            </div>
        </div>
    </section>
</template>
