<script setup lang="ts">
import BrandLogo from '@/components/marketing/BrandLogo.vue';
import { Toaster } from '@/components/ui/sonner';
import { Link } from '@inertiajs/vue3';
import { ShieldCheck } from 'lucide-vue-next';
import { Motion } from 'motion-v';

defineProps<{
    quote?: string;
    quoteAuthor?: string;
}>();
</script>

<template>
    <div class="grid min-h-screen lg:grid-cols-2">
        <!-- Brand panel side -->
        <div class="relative hidden overflow-hidden bg-navy-900 lg:flex lg:flex-col lg:justify-between lg:p-16">
            <div class="pointer-events-none absolute -top-24 -right-24 h-80 w-80 rounded-pill bg-navy-800/60" aria-hidden="true" />
            <div class="pointer-events-none absolute -bottom-32 -left-16 h-96 w-96 rounded-pill bg-green-600/10" aria-hidden="true" />

            <Motion :initial="{ opacity: 0, y: -8 }" :animate="{ opacity: 1, y: 0 }" :transition="{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }">
                <BrandLogo inverted tagline />
            </Motion>

            <Motion
                v-if="quote"
                class="relative"
                :initial="{ opacity: 0, y: 16 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.6, delay: 0.15, ease: [0.16, 1, 0.3, 1] }"
            >
                <div class="rounded-panel border border-white/10 bg-white/[0.04] p-7 shadow-lift backdrop-blur-sm">
                    <blockquote>
                        <p class="font-display text-2xl leading-snug font-bold text-white">„{{ quote }}"</p>
                        <footer v-if="quoteAuthor" class="mt-5 text-sm text-navy-100">— {{ quoteAuthor }}</footer>
                    </blockquote>
                </div>
            </Motion>

            <Motion
                class="relative flex items-center justify-between text-sm text-navy-100"
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :transition="{ duration: 0.5, delay: 0.4 }"
            >
                <span>Anfragen. Vergleichen. Beauftragen.</span>
                <span class="inline-flex items-center gap-1.5"><ShieldCheck :size="14" class="text-green-400" aria-hidden="true" /> Geprüft &amp; sicher</span>
            </Motion>
        </div>

        <!-- Form side -->
        <div class="flex flex-col justify-center bg-sand-50 px-6 py-12 sm:px-12 lg:bg-white lg:px-16">
            <Motion
                class="mx-auto w-full max-w-md"
                :initial="{ opacity: 0, y: 12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }"
            >
                <Link href="/" class="inline-block lg:hidden">
                    <BrandLogo />
                </Link>
                <div class="mt-10 lg:mt-0">
                    <slot />
                </div>
            </Motion>
        </div>
    </div>
    <Toaster position="top-center" rich-colors />
</template>
