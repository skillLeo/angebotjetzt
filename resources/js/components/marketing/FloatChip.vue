<script setup lang="ts">
import { Motion } from 'motion-v';

// Small white rounded chip floating over the hero photograph.
withDefaults(
    defineProps<{
        delay?: number;
        alt?: boolean;
    }>(),
    { delay: 0, alt: false },
);
</script>

<template>
    <Motion
        :initial="{ opacity: 0, y: 16, scale: 0.92 }"
        :animate="{ opacity: 1, y: 0, scale: 1 }"
        :transition="{ duration: 0.55, delay, ease: [0.16, 1, 0.3, 1] }"
        class="absolute"
    >
        <div
            class="flex items-center gap-2 rounded-card bg-white px-3.5 py-2.5 text-sm font-medium text-ink-700 shadow-lift"
            :class="alt ? 'animate-float-alt' : 'animate-float'"
        >
            <slot />
        </div>
    </Motion>
</template>
