<script setup lang="ts">
import CookieBanner from '@/components/marketing/CookieBanner.vue';
import PublicFooter from '@/components/marketing/PublicFooter.vue';
import PublicHeader from '@/components/marketing/PublicHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// The request/booking flow (category → service → multi-step form) hides the
// header and footer so nothing distracts from completing the submission.
// Confirmation is intentionally excluded — chrome returns once submitted.
const REQUEST_FLOW_COMPONENTS = ['public/RequestStart', 'wizard/RequestWizard'];
const page = usePage();
const hideChrome = computed(() => REQUEST_FLOW_COMPONENTS.includes(page.component));
</script>

<template>
    <div class="flex min-h-screen flex-col bg-white">
        <a
            href="#main"
            class="sr-only focus:not-sr-only focus:absolute focus:z-[70] focus:rounded-pill focus:bg-navy-700 focus:px-5 focus:py-2 focus:text-white"
        >
            {{ 'Zum Inhalt springen' }}
        </a>
        <PublicHeader v-if="!hideChrome" />
        <main id="main" class="flex-1 [overflow-x:clip]">
            <slot />
        </main>
        <PublicFooter v-if="!hideChrome" />
        <CookieBanner />
        <Toaster position="top-center" rich-colors />
    </div>
</template>
