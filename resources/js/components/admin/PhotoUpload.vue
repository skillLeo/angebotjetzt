<script setup lang="ts">
import { ImagePlus, Trash2, Upload } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: File | null;
        /** Already-saved image, shown until a new file is chosen. */
        current?: string | null;
        label?: string;
        hint?: string;
        /** Round for people, square for company logos. */
        shape?: 'circle' | 'square';
        error?: string;
    }>(),
    {
        current: null,
        label: 'Foto',
        hint: 'PNG, JPG oder WebP · max. 2 MB',
        shape: 'circle',
        error: undefined,
    },
);

const emit = defineEmits<{ 'update:modelValue': [File | null] }>();

const input = ref<HTMLInputElement | null>(null);
const dragging = ref(false);
const objectUrl = ref<string | null>(null);
const localError = ref<string | null>(null);

const preview = computed(() => objectUrl.value ?? props.current);
const shapeClass = computed(() => (props.shape === 'circle' ? 'rounded-pill' : 'rounded-card'));

function releaseUrl() {
    if (objectUrl.value) {
        URL.revokeObjectURL(objectUrl.value);
        objectUrl.value = null;
    }
}

function accept(file: File | undefined | null) {
    localError.value = null;

    if (!file) return;

    if (!/^image\/(jpeg|png|webp)$/.test(file.type)) {
        localError.value = 'Bitte eine JPG-, PNG- oder WebP-Datei wählen.';
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        localError.value = 'Die Datei ist größer als 2 MB.';
        return;
    }

    releaseUrl();
    objectUrl.value = URL.createObjectURL(file);
    emit('update:modelValue', file);
}

function onDrop(event: DragEvent) {
    dragging.value = false;
    accept(event.dataTransfer?.files?.[0]);
}

function clear() {
    releaseUrl();
    localError.value = null;
    if (input.value) input.value.value = '';
    emit('update:modelValue', null);
}

// The parent resets the form after a successful save; drop the stale preview
// with it so the picker doesn't keep showing a file that's already gone.
watch(
    () => props.modelValue,
    (file) => {
        if (!file) {
            releaseUrl();
            if (input.value) input.value.value = '';
        }
    },
);

onBeforeUnmount(releaseUrl);

const sizeLabel = computed(() =>
    props.modelValue ? `${(props.modelValue.size / 1024).toFixed(0)} KB` : null,
);
</script>

<template>
    <div>
        <span class="mb-1.5 block text-xs font-bold text-ink-700">{{ label }}</span>

        <div
            class="group relative flex items-center gap-4 rounded-card border-2 border-dashed p-3 transition-colors"
            :class="[
                dragging
                    ? 'border-green-500 bg-green-50'
                    : (error || localError)
                      ? 'border-red-300 bg-red-50/40'
                      : 'border-ink-300 bg-sand-50/60 hover:border-navy-500 hover:bg-sand-50',
            ]"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="onDrop"
        >
            <!-- Thumbnail / empty slot -->
            <span
                class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden bg-white shadow-card ring-1 ring-ink-100"
                :class="shapeClass"
            >
                <img v-if="preview" :src="preview" alt="" class="h-full w-full object-cover" />
                <ImagePlus v-else :size="22" class="text-ink-300" aria-hidden="true" />
            </span>

            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-navy-700">
                    {{ modelValue ? modelValue.name : preview ? 'Aktuelles Foto' : 'Foto auswählen' }}
                </p>
                <p class="mt-0.5 truncate text-xs text-ink-500">
                    {{ sizeLabel ? `${sizeLabel} · bereit zum Speichern` : dragging ? 'Jetzt loslassen' : hint }}
                </p>

                <div class="mt-2 flex items-center gap-3">
                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-pill bg-navy-700 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-navy-800"
                        @click="input?.click()"
                    >
                        <Upload :size="13" aria-hidden="true" />
                        {{ preview ? 'Ersetzen' : 'Durchsuchen' }}
                    </button>
                    <button
                        v-if="modelValue"
                        type="button"
                        class="inline-flex items-center gap-1 text-xs font-bold text-ink-500 transition hover:text-red-600"
                        @click="clear"
                    >
                        <Trash2 :size="13" aria-hidden="true" />
                        Entfernen
                    </button>
                </div>
            </div>

            <input
                ref="input"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                class="sr-only"
                @change="accept(($event.target as HTMLInputElement).files?.[0])"
            />
        </div>

        <p v-if="localError || error" class="mt-1.5 text-xs font-semibold text-red-600">
            {{ localError || error }}
        </p>
    </div>
</template>
