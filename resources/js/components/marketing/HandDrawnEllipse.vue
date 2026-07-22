<script setup lang="ts">
import { ref, onMounted } from 'vue';

// A clean, premium underline accent for an inline keyword: a soft gradient
// bar that grows in from the left beneath the text on scroll into view.
const visible = ref(false);
const el = ref<HTMLElement | null>(null);

onMounted(() => {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduced) {
        visible.value = true;
        return;
    }
    const observer = new IntersectionObserver(
        (entries) => {
            if (entries[0].isIntersecting) {
                visible.value = true;
                observer.disconnect();
            }
        },
        { threshold: 0.6 },
    );
    if (el.value) observer.observe(el.value);
});
</script>

<template>
    <span ref="el" class="relative inline-block whitespace-nowrap">
        <slot />
        <span
            class="pointer-events-none absolute inset-x-0 -bottom-1 h-[7px] origin-left rounded-full bg-gradient-to-r from-green-500 to-green-400 md:h-[9px]"
            aria-hidden="true"
            :style="{
                transform: `scaleX(${visible ? 1 : 0})`,
                transition: 'transform 0.7s cubic-bezier(0.16,1,0.3,1) 0.35s',
            }"
        />
    </span>
</template>
