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
        <span
            class="font-display flex items-baseline gap-1.5 text-2xl font-extrabold tracking-tight md:text-[28px]"
            aria-label="AngebotJetzt"
        >
            <span :class="inverted ? 'text-white' : 'text-navy-700'">Angebot</span>
            <span class="text-green-500">Jetzt</span>
        </span>
        <span
            v-if="tagline"
            class="mt-1 text-[10px] font-bold tracking-wide"
            :class="inverted ? 'text-navy-100' : 'text-ink-500'"
        >
            Anfragen. <span class="text-green-500">Vergleichen.</span> Beauftragen.
        </span>
    </span>
</template>
