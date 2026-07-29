<script setup lang="ts">
import { useId } from 'vue';

withDefaults(
    defineProps<{
        label: string;
        modelValue: string | number | null;
        type?: string;
        error?: string;
        required?: boolean;
        placeholder?: string;
        inputmode?: string;
        maxlength?: number;
        hint?: string;
        min?: string;
        max?: string;
    }>(),
    { type: 'text', required: false },
);
defineEmits<{ 'update:modelValue': [value: string]; blur: [event: FocusEvent] }>();
const id = useId();
</script>

<template>
    <div>
        <label :for="id" class="mb-1.5 block text-sm font-semibold text-navy-700">
            {{ label }} <span v-if="required" class="text-green-600">*</span>
        </label>
        <input
            :id="id"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :inputmode="inputmode as any"
            :maxlength="maxlength"
            :min="min"
            :max="max"
            :aria-invalid="!!error"
            class="w-full rounded-card border px-4 py-3 text-[15px] transition focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
            :class="error ? 'border-red-400' : 'border-ink-300 focus:border-green-500'"
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
            @blur="$emit('blur', $event)"
        />
        <p v-if="hint && !error" class="mt-1 text-xs text-ink-500">{{ hint }}</p>
        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    </div>
</template>
