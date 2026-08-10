<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        inverted?: boolean;
        tagline?: boolean;
        /**
         * Overrides the resolved src entirely — used only by the admin logo
         * upload preview to show a locally-selected file through this same
         * real component before anything is saved. Leave unset everywhere
         * else so the normal admin-uploaded/default logic below applies.
         */
        previewSrc?: string | null;
    }>(),
    { inverted: false, tagline: false, previewSrc: undefined },
);

const page = usePage();

// A custom logo uploaded via admin settings replaces both variants — one
// upload is meant to apply everywhere, not a separate file per background.
// Falls back to the original built-in files when nothing's been uploaded.
const customLogoUrl = computed(() => (page.props.branding as { logoUrl?: string | null } | undefined)?.logoUrl ?? null);

const src = computed(() => {
    if (props.previewSrc !== undefined) return props.previewSrc;
    if (customLogoUrl.value) return customLogoUrl.value;

    return props.inverted ? '/images/logo_two_transparentt.png' : '/images/logo-wordmark.png';
});
</script>

<template>
    <span class="inline-flex flex-col items-start leading-none select-none">
        <span class="notranslate inline-flex items-center" translate="no">
            <img :src="src" alt="AngebotJetzt" class="block h-8 w-auto md:h-9" />
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
