<script setup lang="ts">
import BrandLogo from '@/components/marketing/BrandLogo.vue';
import { Toaster } from '@/components/ui/sonner';
import { Link } from '@inertiajs/vue3';
import { CheckCircle2, MapPin, ShieldCheck, type LucideIcon } from 'lucide-vue-next';
import { Motion } from 'motion-v';

withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        icon?: LucideIcon;
    }>(),
    { icon: () => ShieldCheck },
);

const offers = [
    { name: 'Thomas Bergmann', price: '249,00 €', best: true },
    { name: 'Sabine Krüger', price: '289,00 €', best: false },
];
</script>

<template>
    <div class="flex min-h-svh bg-white">
        <!-- Left: brand panel, hidden on small screens -->
        <div class="relative hidden w-full max-w-md flex-col justify-between overflow-hidden bg-navy-900 p-10 lg:flex xl:max-w-lg">
            <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-green-500/20 blur-3xl" aria-hidden="true" />
            <div class="pointer-events-none absolute -bottom-32 -left-16 h-72 w-72 rounded-full bg-navy-500/30 blur-3xl" aria-hidden="true" />

            <Motion :initial="{ opacity: 0, y: -8 }" :animate="{ opacity: 1, y: 0 }" :transition="{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }">
                <Link href="/" class="relative inline-flex w-fit">
                    <BrandLogo inverted />
                </Link>
            </Motion>

            <Motion
                class="relative"
                :initial="{ opacity: 0, y: 16 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.6, delay: 0.1, ease: [0.16, 1, 0.3, 1] }"
            >
                <p class="text-eyebrow mb-3 text-green-400">Der Marktplatz für Kfz-Gutachten</p>
                <h2 class="font-display text-[28px] leading-tight font-extrabold text-white">
                    Von der Anfrage<br />
                    <span class="text-green-400">zum passenden Gutachter.</span>
                </h2>

                <!-- Product mockup: mirrors the homepage's offer-comparison visual -->
                <div class="relative mt-9 mb-2">
                    <div class="overflow-hidden rounded-panel border border-white/10 bg-white/[0.04] shadow-lift backdrop-blur-sm">
                        <div class="border-b border-white/10 px-5 py-4">
                            <p class="text-xs text-navy-100">Anfrage AJ-2026-000842</p>
                            <p class="mt-1 font-display text-base font-bold text-white">3 Angebote erhalten</p>
                        </div>
                        <div class="space-y-2.5 p-4">
                            <div
                                v-for="(offer, idx) in offers"
                                :key="idx"
                                class="rounded-card border bg-white p-3"
                                :class="offer.best ? 'border-green-500 ring-1 ring-green-500' : 'border-ink-100'"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-navy-700">{{ offer.name }}</span>
                                    <CheckCircle2 v-if="offer.best" :size="15" class="text-green-500" aria-hidden="true" />
                                </div>
                                <div class="mt-1 flex items-center gap-1 text-xs text-ink-500">
                                    <MapPin :size="11" aria-hidden="true" /> Köln
                                </div>
                                <p class="mt-1.5 font-display text-base font-extrabold text-navy-700">{{ offer.price }}</p>
                            </div>
                        </div>
                    </div>

                    <Motion
                        class="absolute -top-4 -right-4 flex items-center gap-1.5 rounded-pill bg-white px-3 py-2 text-xs font-bold text-navy-700 shadow-lift animate-float"
                        :initial="{ opacity: 0, scale: 0.9 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ duration: 0.5, delay: 0.6, ease: [0.16, 1, 0.3, 1] }"
                    >
                        <span class="text-green-600">8.000+</span> vermittelt
                    </Motion>
                </div>
            </Motion>

            <Motion
                class="relative flex items-center justify-between text-sm text-navy-100"
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :transition="{ duration: 0.5, delay: 0.4 }"
            >
                <span>© {{ new Date().getFullYear() }} AngebotJetzt</span>
                <span class="inline-flex items-center gap-1.5"><ShieldCheck :size="14" class="text-green-400" aria-hidden="true" /> Geprüfte Gutachter</span>
            </Motion>
        </div>

        <!-- Right: form panel -->
        <div class="flex flex-1 flex-col items-center justify-center gap-6 bg-sand-50 p-6 md:p-10">
            <Motion
                class="w-full max-w-sm"
                :initial="{ opacity: 0, y: 12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }"
            >
                <div class="flex flex-col gap-7">
                    <div class="flex flex-col items-center gap-5 lg:items-start">
                        <Link href="/" class="inline-flex flex-col items-center gap-2 lg:hidden">
                            <BrandLogo />
                        </Link>
                        <div class="flex flex-col items-center gap-4 text-center lg:items-start lg:text-left">
                            <span class="flex h-11 w-11 items-center justify-center rounded-card bg-green-50 text-green-600">
                                <component :is="icon" :size="21" aria-hidden="true" />
                            </span>
                            <div class="space-y-1.5">
                                <h1 class="font-display text-2xl font-bold text-navy-700">{{ title }}</h1>
                                <p class="text-[15px] text-ink-500">{{ description }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-panel border border-ink-100 bg-white p-7 shadow-lift">
                        <slot />
                    </div>
                </div>
            </Motion>
        </div>
    </div>
    <Toaster position="top-center" rich-colors />
</template>
