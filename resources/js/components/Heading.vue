<script setup lang="ts">
type Props = {
    title: string;
    description?: string;
    variant?: 'default' | 'small';
};

withDefaults(defineProps<Props>(), {
    variant: 'default',
});
</script>

<template>
    <header :class="variant === 'small' ? '' : 'mb-8 space-y-0.5'">
        <h2
            class="font-display font-bold text-navy-700"
            :class="variant === 'small' ? 'mb-0.5 text-base' : 'text-xl'"
        >
            {{ title }}
        </h2>
        <p v-if="description" class="text-sm text-ink-500">
            {{ description }}
        </p>
    </header>
</template>
