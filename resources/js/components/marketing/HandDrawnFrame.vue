<script setup lang="ts">
// Hand-drawn green frame drawn around the subject of a photograph,
// with a floating name/rating chip attached to its corner.
defineProps<{
    label: string;
}>();
</script>

<template>
    <span class="pointer-events-none absolute inset-[9%]" aria-hidden="true">
        <svg class="h-full w-full overflow-visible" viewBox="0 0 100 100" fill="none" preserveAspectRatio="none">
            <path
                d="M8 4 C 40 2, 78 3, 95 5 C 97 6, 97 30, 96 55 C 96 75, 97 92, 94 95 C 70 98, 30 97, 6 95 C 4 94, 3 70, 4 45 C 4 25, 3 8, 8 4 Z M8 4 C 20 6, 30 5, 38 4"
                stroke="#3EAE2B"
                stroke-width="1.4"
                stroke-linecap="round"
                vector-effect="non-scaling-stroke"
                style="stroke-width: 3px"
            />
        </svg>
        <span
            class="absolute -top-4 left-0 inline-flex items-center gap-1 bg-green-500 px-2.5 py-1 text-sm font-bold text-white shadow-card"
        >
            {{ label }} <span aria-hidden="true">★</span>
        </span>
    </span>
</template>
