<script setup lang="ts">
import { ref, onMounted } from 'vue';

// A deliberately imperfect, hand-drawn ellipse around an inline keyword.
// The path wobbles and overshoots where it closes — never a perfect circle.
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
        <svg
            class="pointer-events-none absolute -inset-x-[8%] -inset-y-[18%] h-[136%] w-[116%] overflow-visible"
            viewBox="0 0 200 70"
            fill="none"
            aria-hidden="true"
            preserveAspectRatio="none"
        >
            <path
                d="M28 12 C 74 2, 168 4, 186 24 C 199 39, 168 62, 96 64 C 38 66, 6 54, 9 36 C 11 22, 40 12, 82 10 C 96 9, 110 9, 118 10"
                stroke="#3EAE2B"
                stroke-width="3"
                stroke-linecap="round"
                :style="{
                    strokeDasharray: 620,
                    strokeDashoffset: visible ? 0 : 620,
                    transition: 'stroke-dashoffset 1.1s cubic-bezier(0.16,1,0.3,1) 0.35s',
                }"
            />
        </svg>
    </span>
</template>
