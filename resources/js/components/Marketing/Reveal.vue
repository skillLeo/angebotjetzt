<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        delay?: number;
        as?: string;
    }>(),
    { delay: 0, as: 'div' },
);

// Content is visible by default. If JS + IntersectionObserver are available and
// the user allows motion, we start hidden and reveal on scroll into view.
// This guarantees content is NEVER stuck invisible if the observer never fires.
const el = ref<HTMLElement | null>(null);
const animate = ref(false);
const shown = ref(true);
let observer: IntersectionObserver | null = null;

onMounted(() => {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduced || typeof IntersectionObserver === 'undefined' || !el.value) {
        return;
    }

    animate.value = true;
    shown.value = false;

    observer = new IntersectionObserver(
        (entries) => {
            if (entries[0].isIntersecting) {
                shown.value = true;
                observer?.disconnect();
            }
        },
        { threshold: 0.1, rootMargin: '0px 0px -40px 0px' },
    );
    observer.observe(el.value);

    // Safety net: if the observer somehow never fires, reveal after 1.2s.
    window.setTimeout(() => {
        shown.value = true;
    }, 1200);
});

onBeforeUnmount(() => observer?.disconnect());
</script>

<template>
    <component
        :is="as"
        ref="el"
        :style="
            animate
                ? {
                      opacity: shown ? 1 : 0,
                      transform: shown ? 'none' : 'translateY(24px)',
                      transition: `opacity 0.6s cubic-bezier(0.16,1,0.3,1) ${delay}s, transform 0.6s cubic-bezier(0.16,1,0.3,1) ${delay}s`,
                  }
                : undefined
        "
    >
        <slot />
    </component>
</template>
