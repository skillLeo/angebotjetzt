<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{ title: string; updated?: string }>();
</script>

<template>
    <Head>
        <title>{{ title }}</title>
    </Head>
    <section class="bg-white px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
        <div class="mx-auto max-w-3xl">
            <h1 class="text-section text-navy-700">{{ title }}</h1>
            <p v-if="updated" class="mt-3 text-sm text-ink-500">Stand: {{ updated }}</p>
            <div class="legal-prose mt-10 space-y-5 text-[15px] leading-relaxed text-ink-700">
                <slot />
            </div>
        </div>
    </section>
</template>

<style scoped>
.legal-prose :deep(h2) {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 700;
    color: var(--color-navy-700);
    margin-top: 2rem;
    margin-bottom: 0.25rem;
}
.legal-prose :deep(h3) {
    font-weight: 700;
    color: var(--color-navy-700);
    margin-top: 1.25rem;
}
.legal-prose :deep(a) {
    color: var(--color-green-600);
    text-decoration: underline;
}
.legal-prose :deep(ul) {
    list-style: disc;
    padding-left: 1.5rem;
}
</style>
