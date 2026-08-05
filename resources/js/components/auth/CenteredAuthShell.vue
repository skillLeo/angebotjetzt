<script setup lang="ts">
import BrandLogo from '@/components/marketing/BrandLogo.vue';
import { Toaster } from '@/components/ui/sonner';
import { Link } from '@inertiajs/vue3';
import { ShieldCheck, type LucideIcon } from 'lucide-vue-next';
import { Motion } from 'motion-v';

withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        icon?: LucideIcon;
        iconClass?: string;
    }>(),
    { icon: () => ShieldCheck, iconClass: 'bg-green-50 text-green-600' },
);
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center bg-sand-50 px-6 py-10">
        <Link href="/" class="mb-8 inline-flex">
            <BrandLogo />
        </Link>

        <Motion
            class="w-full max-w-md"
            :initial="{ opacity: 0, y: 12 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }"
        >
            <div class="flex flex-col gap-6">
                <div v-if="title" class="text-center">
                    <div class="flex items-center justify-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-sm" :class="iconClass">
                            <component :is="icon" :size="18" aria-hidden="true" />
                        </span>
                        <h1 class="font-display text-2xl font-bold text-navy-700">{{ title }}</h1>
                    </div>
                    <p v-if="description" class="mt-2 text-[15px] text-ink-500">{{ description }}</p>
                </div>
                <div class="rounded-panel border border-ink-100 bg-white p-7 shadow-lift">
                    <slot />
                </div>
            </div>
        </Motion>

        <p class="mt-8 text-xs text-ink-500">© {{ new Date().getFullYear() }} AngebotJetzt · Alle Rechte vorbehalten</p>
    </div>
    <Toaster position="top-center" rich-colors />
</template>
