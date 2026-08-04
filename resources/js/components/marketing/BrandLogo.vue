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
    <span class="inline-flex flex-col items-start leading-none select-none">
        <span
            class="notranslate inline-flex items-center rounded-sm"
            :class="inverted ? 'bg-white px-3 py-1.5 shadow-sm' : ''"
            translate="no"
        >
            <img src="/images/logo-wordmark.png" alt="AngebotJetzt" class="h-7 w-auto md:h-8" />
        </span>
        <span
            v-if="tagline"
            class="mt-1.5 text-[10px] font-bold tracking-wide"
            :class="inverted ? 'text-navy-100' : 'text-ink-500'"
        >
            Anfragen. <span class="text-green-500">Vergleichen.</span> Beauftragen.
        </span>
    </span>
</template>
