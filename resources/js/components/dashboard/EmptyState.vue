<script setup lang="ts">
import type { Component } from 'vue';
import { Inbox } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        title: string;
        description?: string;
        icon?: Component;
    }>(),
    { icon: () => Inbox },
);
</script>

<template>
    <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
        <span class="flex h-14 w-14 items-center justify-center rounded-pill bg-navy-50 text-navy-500">
            <component :is="icon" :size="26" aria-hidden="true" />
        </span>
        <p class="mt-4 font-display text-lg font-bold text-navy-700">{{ title }}</p>
        <p v-if="description" class="mt-1 max-w-sm text-sm text-ink-500">{{ description }}</p>
        <div class="mt-5">
            <slot />
        </div>
    </div>
</template>
