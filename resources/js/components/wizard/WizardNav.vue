<script setup lang="ts">
import { ArrowLeft, ArrowRight } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        showBack?: boolean;
        nextLabel?: string;
        processing?: boolean;
    }>(),
    { showBack: true, nextLabel: 'Weiter', processing: false },
);
defineEmits<{ back: []; next: [] }>();
</script>

<template>
    <div class="mt-8 flex items-center justify-between gap-3">
        <button
            v-if="showBack"
            type="button"
            class="inline-flex items-center gap-2 rounded-pill px-5 py-3 text-sm font-bold text-ink-700 transition hover:bg-sand-50"
            @click="$emit('back')"
        >
            <ArrowLeft :size="18" aria-hidden="true" /> Zurück
        </button>
        <span v-else />
        <button
            type="button"
            :disabled="processing"
            class="inline-flex items-center gap-2 rounded-pill bg-green-500 px-7 py-3 text-sm font-bold text-white transition hover:bg-green-600 disabled:opacity-60"
            @click="$emit('next')"
        >
            {{ nextLabel }}
            <ArrowRight :size="18" aria-hidden="true" />
        </button>
    </div>
</template>
