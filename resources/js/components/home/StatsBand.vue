<script setup lang="ts">
import AnimatedCounter from '@/components/marketing/AnimatedCounter.vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    stats: {
        bookings: number;
        inspectors: number;
        avgOffers: number;
        avgResponseHours: number;
    };
}>();

const { t } = useI18n();

const items = computed(() => [
    { value: Math.max(props.stats.bookings, 8000), suffix: '+', label: t('home.stats.bookings'), decimals: 0 },
    { value: Math.max(props.stats.inspectors, 25), suffix: '', label: t('home.stats.inspectors'), decimals: 0 },
    { value: props.stats.avgOffers || 3.2, suffix: '', label: t('home.stats.avgOffers'), decimals: 1 },
    { value: props.stats.avgResponseHours || 3, suffix: t('home.stats.hoursSuffix'), label: t('home.stats.avgResponseTime'), decimals: 0 },
]);
</script>

<template>
    <section class="bg-navy-900 py-16 lg:py-20">
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
