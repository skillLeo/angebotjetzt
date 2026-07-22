<script setup lang="ts">
import Reveal from '@/components/marketing/Reveal.vue';
import SectionHeading from '@/components/marketing/SectionHeading.vue';
import { CheckCircle2, MapPin } from 'lucide-vue-next';

const steps = [
    {
        n: '01',
        title: 'Anfrage stellen',
        text: 'Beschreiben Sie Ihr Fahrzeug, das benötigte Gutachten und Ihre Region. In zwei Minuten erledigt – kostenlos und unverbindlich.',
    },
    {
        n: '02',
        title: 'Angebote vergleichen',
        text: 'Geprüfte Gutachter aus Ihrer Nähe senden Ihnen individuelle Preisangebote. Sie sehen alle Angebote übersichtlich nebeneinander.',
    },
    {
        n: '03',
        title: 'Gutachter beauftragen',
        text: 'Wählen Sie das beste Angebot, bezahlen Sie sicher online und beauftragen Sie Ihren Gutachter – alles in einem Schritt.',
    },
];
</script>

<template>
    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading eyebrow="In drei Schritten" line1="So funktioniert" line2="AngebotJetzt" />

            <div class="mt-10 grid gap-14 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                <ol class="relative">
                    <div
                        class="absolute top-4 bottom-4 left-[27px] w-0.5 bg-gradient-to-b from-green-500 via-green-400 to-transparent lg:left-[35px]"
                        aria-hidden="true"
                    />
                    <Reveal v-for="(step, i) in steps" :key="step.n" :delay="i * 0.1" as="li">
                        <div class="relative flex gap-6 pb-12 last:pb-0">
                            <span
                                class="relative z-10 flex h-14 w-14 shrink-0 items-center justify-center rounded-pill bg-green-50 font-display text-2xl font-extrabold text-green-600 lg:h-[72px] lg:w-[72px] lg:text-3xl"
                            >
                                {{ step.n }}
                            </span>
                            <div class="pt-1.5 lg:pt-3">
                                <h3 class="text-subsection text-navy-700">{{ step.title }}</h3>
                                <p class="mt-2 max-w-md text-[15px] leading-relaxed text-ink-500">
                                    {{ step.text }}
                                </p>
                            </div>
                        </div>
                    </Reveal>
                </ol>

                <!-- Phone mockup showing the offer comparison -->
                <Reveal :delay="0.2">
                    <div class="mx-auto w-full max-w-[300px]">
                        <div class="rounded-[40px] border-[10px] border-navy-900 bg-navy-900 shadow-lift">
                            <div class="overflow-hidden rounded-[30px] bg-sand-50">
                                <div class="bg-navy-700 px-5 pt-6 pb-5 text-white">
                                    <p class="text-xs text-navy-100">Anfrage AJ-2026-000842</p>
                                    <p class="mt-1 font-display text-lg font-bold">3 Angebote erhalten</p>
                                </div>
                                <div class="space-y-3 p-4">
                                    <div
                                        v-for="(offer, idx) in [
                                            { name: 'Thomas Bergmann', price: '249,00 €', best: true },
                                            { name: 'Sabine Krüger', price: '289,00 €', best: false },
                                            { name: 'Andreas Wolff', price: '310,00 €', best: false },
                                        ]"
                                        :key="idx"
                                        class="rounded-card border bg-white p-3.5"
                                        :class="offer.best ? 'border-green-500 ring-1 ring-green-500' : 'border-ink-100'"
                                    >
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-bold text-navy-700">{{ offer.name }}</span>
                                            <CheckCircle2 v-if="offer.best" :size="16" class="text-green-500" aria-hidden="true" />
                                        </div>
                                        <div class="mt-1.5 flex items-center gap-1 text-xs text-ink-500">
                                            <MapPin :size="12" aria-hidden="true" /> Köln
                                        </div>
                                        <p class="mt-2 font-display text-lg font-extrabold text-navy-700">{{ offer.price }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Reveal>
            </div>
        </div>
    </section>
</template>
