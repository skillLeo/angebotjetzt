<script setup lang="ts">
import FormField from '@/components/forms/FormField.vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import WizardNav from '@/components/wizard/WizardNav.vue';
import { UploadCloud } from 'lucide-vue-next';
import { ref } from 'vue';

export type WizardFieldDef = {
    key: string;
    label: string;
    type: 'text' | 'number' | 'date' | 'select' | 'textarea' | 'file';
    options: string[] | null;
    isRequired: boolean;
};

const props = defineProps<{
    fields: WizardFieldDef[];
    form: { answers: Record<string, string | number | File | null> };
}>();
const emit = defineEmits<{ next: []; back: [] }>();

const errors = ref<Record<string, string>>({});

function onFile(e: Event, key: string) {
    props.form.answers[key] = (e.target as HTMLInputElement).files?.[0] ?? null;
}

function proceed() {
    errors.value = {};
    for (const field of props.fields) {
        const value = props.form.answers[field.key];
        if (field.isRequired && (value === undefined || value === null || value === '')) {
            errors.value[field.key] = `Bitte geben Sie „${field.label}“ an.`;
        }
    }
    if (Object.keys(errors.value).length === 0) emit('next');
}
</script>

<template>
    <div>
        <h2 class="font-display text-2xl font-bold text-navy-700">Zusätzliche Angaben</h2>
        <p class="mt-1 text-ink-500">Bitte machen Sie folgende Angaben zu Ihrer Anfrage.</p>

        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <template v-for="field in fields" :key="field.key">
                <FormField
                    v-if="field.type === 'text' || field.type === 'number' || field.type === 'date'"
                    v-model="form.answers[field.key] as string"
                    :label="field.label"
                    :type="field.type"
                    :required="field.isRequired"
                    :error="errors[field.key]"
                />

                <div v-else-if="field.type === 'select'">
                    <label class="mb-1.5 block text-sm font-semibold text-navy-700">
                        {{ field.label }} <span v-if="field.isRequired" class="text-green-600">*</span>
                    </label>
                    <Select
                        :model-value="(form.answers[field.key] as string) ?? undefined"
                        @update:model-value="(v) => (form.answers[field.key] = v as string)"
                    >
                        <SelectTrigger class="h-12 w-full rounded-card border-ink-300 px-4 py-3 text-[15px] focus-visible:border-green-500">
                            <SelectValue :placeholder="'Bitte wählen'" />
                        </SelectTrigger>
                        <SelectContent class="max-w-[calc(100vw-2rem)]">
                            <SelectItem v-for="opt in field.options ?? []" :key="opt" :value="opt">{{ opt }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="errors[field.key]" class="mt-1 text-sm text-red-600">{{ errors[field.key] }}</p>
                </div>

                <div v-else-if="field.type === 'textarea'" class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-navy-700">
                        {{ field.label }} <span v-if="field.isRequired" class="text-green-600">*</span>
                    </label>
                    <textarea
                        v-model="form.answers[field.key] as string"
                        rows="4"
                        class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
                    />
                    <p v-if="errors[field.key]" class="mt-1 text-sm text-red-600">{{ errors[field.key] }}</p>
                </div>

                <div v-else-if="field.type === 'file'" class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-navy-700">
                        {{ field.label }} <span v-if="field.isRequired" class="text-green-600">*</span>
                    </label>
                    <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-card border-2 border-dashed border-ink-300 px-6 py-8 text-center transition hover:border-green-500 hover:bg-green-50">
                        <UploadCloud :size="28" class="text-ink-500" aria-hidden="true" />
                        <span class="text-sm font-medium text-ink-700">
                            {{ form.answers[field.key] instanceof File ? (form.answers[field.key] as File).name : 'Datei auswählen' }}
                        </span>
                        <input type="file" class="hidden" @change="onFile($event, field.key)" />
                    </label>
                    <p v-if="errors[field.key]" class="mt-1 text-sm text-red-600">{{ errors[field.key] }}</p>
                </div>
            </template>
        </div>

        <WizardNav @back="$emit('back')" @next="proceed" />
    </div>
</template>
