<script setup lang="ts">
import GermanyMap from '@/components/marketing/GermanyMap.vue';
import SectionHeading from '@/components/marketing/SectionHeading.vue';
import { MapPin } from 'lucide-vue-next';
import { computed } from 'vue';


defineProps<{
    cityCounts: Record<string, number>;
}>();

const activity = computed(() => [
    { city: 'Hamburg', service: 'Unfallschaden', time: `vor ${4} Minuten` },
    { city: 'München', service: 'Gebrauchtwagencheck', time: `vor ${11} Minuten` },
    { city: 'Köln', service: 'Fahrzeugbewertung', time: `vor ${18} Minuten` },
    { city: 'Berlin', service: 'Wertminderung', time: `vor ${26} Minuten` },
    { city: 'Frankfurt am Main', service: 'Spezialgutachten', time: `vor ${34} Minuten` },
    { city: 'Stuttgart', service: 'Unfallschaden', time: `vor ${41} Minuten` },
]);
</script>

<template>
    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading :eyebrow="'Bundesweit'" :line1="'Geprüfte Anbieter'" :line2="'in ganz Deutschland'" />

            <div class="mt-10 grid gap-8 lg:grid-cols-[1.6fr_1fr]">
                <GermanyMap :city-counts="cityCounts" />

                <div class="rounded-panel border border-ink-100 bg-white p-6 shadow-card">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2.5 w-2.5">
                            <span
                                class="absolute inline-flex h-full w-full animate-ping rounded-pill bg-green-400 opacity-75"
                            />
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-pill bg-green-500" />
                        </span>
                        <h3 class="font-display text-lg font-bold text-navy-700">{{ 'Live-Aktivität' }}</h3>
                    </div>
                    <ul class="mt-5 space-y-3">
                        <li
                            v-for="(item, i) in activity"
                            :key="i"
                            class="flex items-start gap-3 rounded-card bg-sand-50 p-4"
                        >
                            <span
                                class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-pill bg-green-50 text-green-600"
                            >
                                <MapPin :size="17" aria-hidden="true" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-navy-700">
                                    {{ 'Neue Anfrage in' }} {{ item.city }}
                                </p>
                                <p class="text-sm text-ink-500">{{ item.service }} · {{ item.time }}</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</template>
