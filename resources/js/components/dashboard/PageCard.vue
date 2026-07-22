<script setup lang="ts">
defineProps<{ title?: string; subtitle?: string }>();
</script>

<template>
    <section class="rounded-card border border-ink-100 bg-white shadow-card">
        <div v-if="title || $slots.actions" class="flex flex-wrap items-center justify-between gap-3 border-b border-ink-100 px-5 py-4 sm:px-6">
            <div>
                <h2 v-if="title" class="font-display text-lg font-bold text-navy-700">{{ title }}</h2>
                <p v-if="subtitle" class="mt-0.5 text-sm text-ink-500">{{ subtitle }}</p>
            </div>
            <div v-if="$slots.actions" class="flex items-center gap-2">
                <slot name="actions" />
            </div>
        </div>
        <div :class="$slots.actions || title ? '' : ''">
            <slot />
        </div>
    </section>
</template>
