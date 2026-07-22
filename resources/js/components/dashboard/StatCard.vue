<script setup lang="ts">
import type { Component } from 'vue';

withDefaults(
    defineProps<{
        label: string;
        value: string | number;
        icon?: Component;
        accent?: boolean;
        hint?: string;
    }>(),
    { accent: false },
);
</script>

<template>
    <div class="rounded-card border border-ink-100 bg-white p-5 shadow-card">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm font-medium text-ink-500">{{ label }}</p>
                <p class="mt-2 font-display text-2xl font-extrabold" :class="accent ? 'text-green-600' : 'text-navy-700'">
                    {{ value }}
                </p>
                <p v-if="hint" class="mt-1 text-xs text-ink-500">{{ hint }}</p>
            </div>
            <span
                v-if="icon"
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-card"
                :class="accent ? 'bg-green-50 text-green-600' : 'bg-navy-50 text-navy-700'"
            >
                <component :is="icon" :size="21" aria-hidden="true" />
            </span>
        </div>
    </div>
</template>
