<script setup lang="ts">
import CookieBanner from '@/components/marketing/CookieBanner.vue';
import PublicFooter from '@/components/marketing/PublicFooter.vue';
import PublicHeader from '@/components/marketing/PublicHeader.vue';
import { Toaster } from '@/components/ui/sonner';
</script>

<template>
    <div class="flex min-h-screen flex-col bg-white">
        <a
            href="#main"
            class="sr-only focus:not-sr-only focus:absolute focus:z-[70] focus:rounded-pill focus:bg-navy-700 focus:px-5 focus:py-2 focus:text-white"
        >
            Zum Inhalt springen
        </a>
        <PublicHeader />
        <main id="main" class="flex-1 [overflow-x:clip]">
            <slot />
        </main>
        <PublicFooter />
        <CookieBanner />
        <Toaster position="top-center" rich-colors />
    </div>
</template>
