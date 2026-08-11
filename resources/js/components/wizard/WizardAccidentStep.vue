<script setup lang="ts">
import WizardNav from '@/components/wizard/WizardNav.vue';
import { computed } from 'vue';

/**
 * Only shown for direct-accept services (Unfallschadengutachten). These two
 * answers decide how the claim is handled, so they're asked before any vehicle
 * detail and travel with the request to the provider.
 */
const props = defineProps<{ form: Record<string, unknown> }>();
defineEmits<{ next: []; back: [] }>();

const roles = [
    { value: 'geschaedigter', label: 'Geschädigter', hint: 'Der Unfall war unverschuldet.' },
    { value: 'verursacher', label: 'Unfallverursacher', hint: 'Ich habe den Unfall verursacht.' },
    { value: 'unklar', label: 'Unklar', hint: 'Die Schuldfrage ist noch offen.' },
];

const lawyerOptions = [
    { value: true, label: 'Ja' },
    { value: false, label: 'Nein' },
];

const complete = computed(
    () => !!props.form.accident_role && props.form.has_lawyer !== null && props.form.has_lawyer !== '',
);
</script>

<template>
    <div>
        <h2 class="font-display text-xl font-bold text-navy-700">Angaben zum Unfall</h2>
        <p class="mt-1 text-sm text-ink-500">
            Diese Angaben helfen dem Sachverständigen, Ihren Fall richtig einzuordnen.
        </p>

        <fieldset class="mt-7">
            <legend class="font-display font-bold text-navy-700">Welche Rolle haben Sie bei dem Unfall?</legend>
            <div class="mt-3 space-y-2">
                <label
                    v-for="role in roles"
                    :key="role.value"
                    class="flex cursor-pointer items-start gap-3 rounded-card border p-4 transition"
                    :class="
                        form.accident_role === role.value
                            ? 'border-green-500 bg-green-50'
                            : 'border-ink-300 bg-white hover:border-navy-500'
                    "
                >
                    <input
                        v-model="form.accident_role"
                        type="radio"
                        name="accident_role"
                        :value="role.value"
                        class="mt-1 accent-green-600"
                    />
                    <span>
                        <span class="block font-semibold text-navy-700">{{ role.label }}</span>
                        <span class="block text-sm text-ink-500">{{ role.hint }}</span>
                    </span>
                </label>
            </div>
        </fieldset>

        <fieldset class="mt-7">
            <legend class="font-display font-bold text-navy-700">
                Haben Sie wegen des Unfalls bereits einen Anwalt beauftragt?
            </legend>
            <div class="mt-3 flex gap-3">
                <label
                    v-for="opt in lawyerOptions"
                    :key="String(opt.value)"
                    class="flex flex-1 cursor-pointer items-center gap-3 rounded-card border p-4 transition"
                    :class="
                        form.has_lawyer === opt.value
                            ? 'border-green-500 bg-green-50'
                            : 'border-ink-300 bg-white hover:border-navy-500'
                    "
                >
                    <input
                        v-model="form.has_lawyer"
                        type="radio"
                        name="has_lawyer"
                        :value="opt.value"
                        class="accent-green-600"
                    />
                    <span class="font-semibold text-navy-700">{{ opt.label }}</span>
                </label>
            </div>
        </fieldset>

        <p v-if="!complete" class="mt-6 text-sm text-ink-500">
            Bitte beantworten Sie beide Fragen, um fortzufahren.
        </p>

        <WizardNav :processing="!complete" @back="$emit('back')" @next="complete && $emit('next')" />
    </div>
</template>
