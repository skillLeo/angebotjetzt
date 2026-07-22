<script setup lang="ts">
import { ref, onMounted } from 'vue';

const props = withDefaults(
    defineProps<{
        value: number;
        suffix?: string;
        decimals?: number;
        digitsInCircles?: boolean;
    }>(),
    { suffix: '', decimals: 0, digitsInCircles: false },
);

const display = ref(0);
const el = ref<HTMLElement | null>(null);

function formatted(n: number): string {
    return new Intl.NumberFormat('de-DE', {
        minimumFractionDigits: props.decimals,
        maximumFractionDigits: props.decimals,
    }).format(n);
}

onMounted(() => {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduced || typeof IntersectionObserver === 'undefined') {
        display.value = props.value;
        return;
    }
    const observer = new IntersectionObserver(
        (entries) => {
            if (entries[0].isIntersecting) {
                animate();
                observer.disconnect();
            }
        },
        { threshold: 0.2, rootMargin: '0px 0px -40px 0px' },
    );
    if (el.value) observer.observe(el.value);

    // Safety net: never leave the counter stuck at 0 if the observer never fires.
    window.setTimeout(() => {
        if (display.value === 0 && props.value !== 0) {
            display.value = props.value;
        }
    }, 2000);
});

function animate() {
    const duration = 1400;
    const start = performance.now();
    function tick(now: number) {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        display.value = props.value * eased;
        if (progress < 1) requestAnimationFrame(tick);
        else display.value = props.value;
    }
    requestAnimationFrame(tick);
}
</script>

<template>
    <span ref="el" class="inline-flex items-center">
        <template v-if="digitsInCircles">
            <span
                v-for="(char, i) in formatted(display).split('')"
                :key="i"
                :class="
                    /[0-9]/.test(char)
                        ? 'mx-0.5 inline-flex h-12 w-10 items-center justify-center rounded-card bg-white/10 font-display text-3xl font-extrabold text-white md:h-16 md:w-14 md:text-5xl'
                        : 'font-display text-3xl font-extrabold text-white md:text-5xl'
                "
            >
                {{ char }}
            </span>
            <span v-if="suffix" class="ml-1.5 font-display text-2xl font-extrabold text-green-400 md:text-4xl">{{ suffix }}</span>
        </template>
        <template v-else>
            {{ formatted(display) }}{{ suffix }}
        </template>
    </span>
</template>
