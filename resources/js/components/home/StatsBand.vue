<script setup lang="ts">
import AnimatedCounter from '@/components/marketing/AnimatedCounter.vue';
import { useSiteContent } from '@/composables/useSiteContent';
import { computed } from 'vue';

const props = defineProps<{
    stats: {
        bookings: number;
        inspectors: number;
        avgOffers: number;
        avgResponseHours: number;
    };
}>();

const c = useSiteContent();

/** The admin-set figure, ignored if it is not a usable number. */
function figure(key: string, fallback: number): number {
    const parsed = Number(c(key, String(fallback)).replace(',', '.'));

    return Number.isFinite(parsed) ? parsed : fallback;
}

/**
 * The unit shown after a counter, spaced from the number here rather than in
 * the stored value: Laravel trims request input, so a leading space typed in
 * admin would never survive the save.
 */
function unit(key: string, fallback: string): string {
    const value = c(key, fallback).trim();

    return value === '' ? '' : ` ${value}`;
}

const items = computed(() => [
    { value: Math.max(props.stats.bookings, figure('home.stats.min1', 8000)), suffix: '+', label: c('home.stats.label1', 'Aufträge vermittelt'), decimals: 0 },
    { value: Math.max(props.stats.inspectors, figure('home.stats.min2', 25)), suffix: '', label: c('home.stats.label2', 'Geprüfte Anbieter'), decimals: 0 },
    { value: props.stats.avgOffers || figure('home.stats.min3', 3.2), suffix: '', label: c('home.stats.label3', 'Ø Angebote pro Anfrage'), decimals: 1 },
    { value: props.stats.avgResponseHours || figure('home.stats.min4', 3), suffix: unit('home.stats.suffix4', 'Std.'), label: c('home.stats.label4', 'Ø Antwortzeit'), decimals: 0 },
]);
</script>

<template>
    <section class="bg-navy-900 py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-y-12 lg:grid-cols-4">
                <div v-for="(item, i) in items" :key="i" class="text-center">
                    <div class="flex justify-center">
                        <AnimatedCounter
                            :value="item.value"
                            :suffix="item.suffix"
                            :decimals="item.decimals"
                            digits-in-circles
                        />
                    </div>
                    <p class="mt-4 text-sm font-medium text-navy-100">{{ item.label }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
