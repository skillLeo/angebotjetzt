<script setup lang="ts">
import CookieBanner from '@/components/marketing/CookieBanner.vue';
import PublicFooter from '@/components/marketing/PublicFooter.vue';
import PublicHeader from '@/components/marketing/PublicHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import { mobileNavOpen } from '@/composables/useMobileNav';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// Only the actual multi-step form (not the category/service picker before it)
// trims the header down to logo + trust message and hides the footer, so
// nothing distracts from completing the submission. The category/service
// picker keeps the normal site chrome. Confirmation is intentionally
// excluded from both — full chrome returns once submitted.
const REDUCED_HEADER_COMPONENTS = ['wizard/RequestWizard'];
const page = usePage();
const reducedHeader = computed(() => REDUCED_HEADER_COMPONENTS.includes(page.component));
</script>

<template>
    <div class="flex min-h-screen flex-col bg-white">
        <a
            href="#main"
            class="sr-only focus:not-sr-only focus:absolute focus:z-[70] focus:rounded-pill focus:bg-navy-700 focus:px-5 focus:py-2 focus:text-white"
        >
            {{ 'Zum Inhalt springen' }}
        </a>
        <PublicHeader :reduced="reducedHeader" />
        <main id="main" class="flex-1 [overflow-x:clip]">
            <slot />
        </main>
        <PublicFooter v-if="!reducedHeader" />
        <CookieBanner v-if="!mobileNavOpen" />
        <Toaster position="top-center" rich-colors />
    </div>
</template>
