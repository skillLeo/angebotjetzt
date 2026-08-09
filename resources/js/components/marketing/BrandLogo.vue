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
        <span class="notranslate inline-flex items-center" translate="no">
            <img
                v-if="inverted"
                src="/images/logo_two_transparentt.png"
                alt="AngebotJetzt"
                class="block h-8 w-auto md:h-9"
            />
            <img v-else src="/images/logo-wordmark.png" alt="AngebotJetzt" class="block h-8 w-auto md:h-9" />
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
