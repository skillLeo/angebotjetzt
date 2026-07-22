<script setup lang="ts">
import FormField from '@/components/forms/FormField.vue';
import WizardNav from '@/components/wizard/WizardNav.vue';
import { ref } from 'vue';

const props = defineProps<{
    form: {
        plz: string;
        ort: string;
        strasse: string;
        preferred_date: string;
        alternative_date: string;
        notes: string;
    };
}>();
const emit = defineEmits<{ next: []; back: [] }>();

const errors = ref<Record<string, string>>({});

function proceed() {
    errors.value = {};
    if (!/^\d{5}$/.test(props.form.plz)) errors.value.plz = 'Bitte geben Sie eine gültige fünfstellige PLZ ein.';
    if (!props.form.ort) errors.value.ort = 'Bitte geben Sie Ihren Ort an.';
    if (Object.keys(errors.value).length === 0) emit('next');
}
</script>

<template>
    <div>
        <h2 class="font-display text-2xl font-bold text-navy-700">Ort und Wunschtermin</h2>
        <p class="mt-1 text-ink-500">Damit wir Gutachter in Ihrer Nähe finden.</p>

        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <FormField v-model="form.plz" label="Postleitzahl" required :error="errors.plz" inputmode="numeric" maxlength="5" placeholder="z. B. 50667" />
            <FormField v-model="form.ort" label="Ort" required :error="errors.ort" placeholder="z. B. Köln" />
            <div class="sm:col-span-2">
                <FormField v-model="form.strasse" label="Straße & Hausnummer" placeholder="optional" />
            </div>
            <FormField v-model="form.preferred_date" label="Wunschtermin" type="date" />
            <FormField v-model="form.alternative_date" label="Alternativtermin" type="date" />
        </div>

        <div class="mt-5">
            <label class="mb-1.5 block text-sm font-semibold text-navy-700">Anmerkungen</label>
            <textarea
                v-model="form.notes"
                rows="4"
                placeholder="Beschreiben Sie den Schaden oder besondere Wünsche (optional)."
                class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
            />
        </div>

        <WizardNav @back="$emit('back')" @next="proceed" />
    </div>
</template>
