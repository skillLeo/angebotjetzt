<script setup lang="ts">
import BrandLogo from '@/components/marketing/BrandLogo.vue';
import { Toaster } from '@/components/ui/sonner';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-sand-50 p-6 md:p-10">
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-5">
                    <Link href="/" class="inline-flex flex-col items-center gap-2">
                        <BrandLogo />
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="font-display text-xl font-bold text-navy-700">{{ title }}</h1>
                        <p class="text-center text-sm text-ink-500">{{ description }}</p>
                    </div>
                </div>
                <div class="rounded-panel border border-ink-100 bg-white p-7 shadow-card">
                    <slot />
                </div>
            </div>
        </div>
    </div>
    <Toaster position="top-center" rich-colors />
</template>
