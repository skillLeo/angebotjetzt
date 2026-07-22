<script setup lang="ts">
import { Star } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        rating: number;
        size?: number;
    }>(),
    { size: 16 },
);
</script>

<template>
    <span class="inline-flex items-center gap-0.5" :aria-label="`Bewertung: ${rating} von 5 Sternen`">
        <Star
            v-for="i in 5"
            :key="i"
            :size="size"
            :class="i <= Math.round(rating) ? 'fill-amber-400 text-amber-400' : 'fill-ink-100 text-ink-100'"
            aria-hidden="true"
        />
    </span>
</template>
