<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    data: Array<{ label: string; value: number }>;
    color?: string;
}>();

const max = computed(() => Math.max(1, ...props.data.map((d) => d.value)));
</script>

<template>
    <div class="flex h-48 items-end gap-2">
        <div v-for="(d, i) in data" :key="i" class="flex flex-1 flex-col items-center gap-2">
            <div class="flex w-full flex-1 items-end">
                <div
                    class="w-full rounded-t-md transition-all"
                    :style="{ height: `${(d.value / max) * 100}%`, backgroundColor: color ?? '#3EAE2B', minHeight: d.value > 0 ? '4px' : '0' }"
                />
            </div>
            <span class="text-[10px] text-ink-500">{{ d.label }}</span>
        </div>
    </div>
</template>
