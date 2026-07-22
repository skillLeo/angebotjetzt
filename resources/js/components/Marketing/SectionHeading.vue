<script setup lang="ts">
withDefaults(
    defineProps<{
        line1: string;
        line2?: string;
        centered?: boolean;
        inverted?: boolean;
        eyebrow?: string;
    }>(),
    { centered: false, inverted: false },
);
</script>

<template>
    <div :class="centered ? 'text-center' : 'text-left'">
        <p
            v-if="eyebrow"
            class="text-eyebrow mb-4"
            :class="inverted ? 'text-green-100' : 'text-green-600'"
        >
            {{ eyebrow }}
        </p>
        <h2 class="text-section text-balance">
            <span :class="inverted ? 'text-white' : 'text-navy-700'">{{ line1 }}</span>
            <template v-if="line2">
                <br />
                <span :class="inverted ? 'text-green-400' : 'text-green-500'">{{ line2 }}</span>
            </template>
        </h2>
    </div>
</template>
