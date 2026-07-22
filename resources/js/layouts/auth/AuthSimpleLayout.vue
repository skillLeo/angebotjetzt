<script setup lang="ts">
import BrandLogo from '@/components/marketing/BrandLogo.vue';
import { Toaster } from '@/components/ui/sonner';
import { Link } from '@inertiajs/vue3';
import { BadgeCheck, CheckCircle2, ShieldCheck } from 'lucide-vue-next';

defineProps<{
    title?: string;
    description?: string;
}>();

const highlights = [
    { icon: CheckCircle2, text: 'Kostenlos Angebote von geprüften Kfz-Gutachtern erhalten' },
    { icon: ShieldCheck, text: 'Sicher bezahlen, erst wenn Sie zufrieden sind' },
    { icon: BadgeCheck, text: 'Über 8.000 vermittelte Gutachten in ganz Deutschland' },
];
</script>

<template>
    <div class="flex min-h-svh bg-white">
        <!-- Left: brand panel, hidden on small screens -->
        <div class="relative hidden w-full max-w-md flex-col justify-between overflow-hidden bg-navy-900 p-10 lg:flex xl:max-w-lg">
            <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-green-500/20 blur-3xl" aria-hidden="true" />
            <div class="pointer-events-none absolute -bottom-32 -left-16 h-72 w-72 rounded-full bg-navy-500/30 blur-3xl" aria-hidden="true" />

            <Link href="/" class="relative inline-flex w-fit">
                <BrandLogo inverted />
            </Link>

            <div class="relative">
                <h2 class="font-display text-3xl leading-tight font-extrabold text-white">
                    Kfz-Gutachten<br />
                    <span class="text-green-400">einfach vergleichen.</span>
                </h2>
                <ul class="mt-8 space-y-4">
                    <li v-for="(item, i) in highlights" :key="i" class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-pill bg-white/10 text-green-400">
                            <component :is="item.icon" :size="17" aria-hidden="true" />
                        </span>
                        <span class="pt-1 text-[15px] text-navy-100">{{ item.text }}</span>
                    </li>
                </ul>
            </div>

            <p class="relative text-sm text-navy-100">© {{ new Date().getFullYear() }} AngebotJetzt</p>
        </div>

        <!-- Right: form panel -->
        <div class="flex flex-1 flex-col items-center justify-center gap-6 bg-sand-50 p-6 md:p-10">
            <div class="w-full max-w-sm">
                <div class="flex flex-col gap-8">
                    <div class="flex flex-col items-center gap-5 lg:items-start">
                        <Link href="/" class="inline-flex flex-col items-center gap-2 lg:hidden">
                            <BrandLogo />
                        </Link>
                        <div class="space-y-2 text-center lg:text-left">
                            <h1 class="font-display text-xl font-bold text-navy-700">{{ title }}</h1>
                            <p class="text-center text-sm text-ink-500 lg:text-left">{{ description }}</p>
                        </div>
                    </div>
                    <div class="rounded-panel border border-ink-100 bg-white p-7 shadow-card">
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <Toaster position="top-center" rich-colors />
</template>
