<script setup lang="ts">
import HandDrawnFrame from '@/components/marketing/HandDrawnFrame.vue';
import Reveal from '@/components/marketing/Reveal.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    image: string;
}>();
</script>

<template>
    <section class="bg-white px-4 py-8 sm:px-6 lg:py-14">
        <div class="mx-auto max-w-7xl overflow-hidden rounded-panel bg-navy-900">
            <div class="grid items-center gap-8 lg:grid-cols-2">
                <div class="p-8 sm:p-12 lg:p-16">
                    <Reveal>
                        <p class="text-eyebrow mb-5 text-green-400">Für Gutachter</p>
                        <h2 class="text-section text-white">
                            Mehr Aufträge.<br />
                            <span class="text-green-400">Kein Papierkram.</span>
                        </h2>
                        <p class="text-lead mt-6 max-w-lg text-navy-100">
                            Erhalten Sie passende Anfragen aus Ihrem Servicegebiet automatisch per
                            E-Mail. Sie bestimmen Ihre Preise selbst, verwalten Ihr Guthaben im
                            Wallet und lassen sich auszahlen, wann immer Sie möchten.
                        </p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <Link
                                href="/fuer-gutachter"
                                class="rounded-pill bg-green-500 px-7 py-3.5 text-center text-[15px] font-bold text-white transition hover:bg-green-600"
                            >
                                Als Gutachter registrieren
                            </Link>
                            <Link
                                href="/fuer-gutachter"
                                class="rounded-pill border border-navy-500 px-7 py-3.5 text-center text-[15px] font-bold text-white transition hover:bg-navy-800"
                            >
                                Mehr erfahren
                            </Link>
                        </div>
                    </Reveal>
                </div>

                <div class="relative h-full min-h-[320px] bg-navy-800">
                    <img
                        :src="image"
                        width="600"
                        height="500"
                        loading="eager"
                        decoding="async"
                        alt="Kfz-Gutachter bei der Fahrzeugbegutachtung"
                        class="h-full w-full object-cover lg:absolute lg:inset-0"
                    />
                    <HandDrawnFrame label="Thomas B. 5/5" />
                </div>
            </div>
        </div>
    </section>
</template>
