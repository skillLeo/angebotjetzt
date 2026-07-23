<script setup lang="ts">
import BrandLogo from '@/components/marketing/BrandLogo.vue';
import { Toaster } from '@/components/ui/sonner';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

defineProps<{
    quote?: string;
    quoteAuthor?: string;
}>();

const { t } = useI18n();
</script>

<template>
    <div class="grid min-h-screen lg:grid-cols-2">
        <!-- Form side -->
        <div class="flex flex-col justify-center px-6 py-12 sm:px-12 lg:px-16">
            <div class="mx-auto w-full max-w-md">
                <Link href="/" class="inline-block">
                    <BrandLogo />
                </Link>
                <div class="mt-10">
                    <slot />
                </div>
            </div>
        </div>

        <!-- Navy panel side -->
        <div class="relative hidden overflow-hidden bg-navy-900 lg:flex lg:flex-col lg:justify-between lg:p-16">
            <div class="absolute -top-24 -right-24 h-80 w-80 rounded-pill bg-navy-800/60" aria-hidden="true" />
            <div class="absolute -bottom-32 -left-16 h-96 w-96 rounded-pill bg-green-600/10" aria-hidden="true" />

            <BrandLogo inverted tagline />

            <blockquote v-if="quote" class="relative max-w-md">
                <p class="font-display text-2xl leading-snug font-bold text-white">„{{ quote }}"</p>
                <footer v-if="quoteAuthor" class="mt-5 text-navy-100">— {{ quoteAuthor }}</footer>
            </blockquote>

            <p class="relative text-sm text-navy-100">{{ t('home.hero.eyebrow') }}</p>
        </div>
    </div>
    <Toaster position="top-center" rich-colors />
</template>
