<script setup lang="ts">
import { Minus, Plus } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    items: Array<{ q: string; a: string }>;
}>();

const open = ref<number | null>(0);

function toggle(i: number) {
    open.value = open.value === i ? null : i;
}
</script>

<template>
    <div class="divide-y divide-ink-100 overflow-hidden rounded-panel border border-ink-100 bg-white">
        <div v-for="(item, i) in items" :key="i">
            <h3>
                <button
                    type="button"
                    class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left transition hover:bg-sand-50"
                    :aria-expanded="open === i"
                    @click="toggle(i)"
                >
                    <span class="font-display text-lg font-bold text-navy-700">{{ item.q }}</span>
                    <span
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-pill transition-colors"
                        :class="open === i ? 'bg-green-500 text-white' : 'bg-navy-50 text-navy-700'"
                    >
                        <Minus v-if="open === i" :size="18" aria-hidden="true" />
                        <Plus v-else :size="18" aria-hidden="true" />
                    </span>
                </button>
            </h3>
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="max-h-0 opacity-0"
                enter-to-class="max-h-96 opacity-100"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="max-h-96 opacity-100"
                leave-to-class="max-h-0 opacity-0"
            >
                <div v-if="open === i" class="overflow-hidden">
                    <p class="px-6 pb-6 text-[15px] leading-relaxed text-ink-500">{{ item.a }}</p>
                </div>
            </Transition>
        </div>
    </div>
</template>
