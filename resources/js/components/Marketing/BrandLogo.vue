<script setup lang="ts">
withDefaults(
    defineProps<{
        inverted?: boolean;
        tagline?: boolean;
    }>(),
    { inverted: false, tagline: false },
);
</script>

<template>
    <span class="inline-flex flex-col leading-none select-none">
        <svg
            viewBox="0 0 232 34"
            class="h-7 w-auto md:h-8"
            role="img"
            aria-label="AngebotJetzt"
        >
            <text
                x="0"
                y="27"
                font-family="'Cabinet Grotesk', sans-serif"
                font-weight="800"
                font-size="30"
                letter-spacing="-0.8"
                :fill="inverted ? '#ffffff' : '#14375E'"
            >
                Angebot
            </text>
            <text
                x="123"
                y="27"
                font-family="'Cabinet Grotesk', sans-serif"
                font-weight="800"
                font-size="30"
                letter-spacing="-0.8"
                fill="#3EAE2B"
            >
                Jetzt
            </text>
        </svg>
        <span
            v-if="tagline"
            class="mt-1 text-[10px] font-bold tracking-wide"
            :class="inverted ? 'text-navy-100' : 'text-ink-500'"
        >
            Anfragen. <span class="text-green-500">Vergleichen.</span> Beauftragen.
        </span>
    </span>
</template>
