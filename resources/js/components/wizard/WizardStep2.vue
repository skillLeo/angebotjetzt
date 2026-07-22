<script setup lang="ts">
import FormField from '@/components/forms/FormField.vue';
import WizardNav from '@/components/wizard/WizardNav.vue';
import { UploadCloud, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    form: {
        vehicle_make: string;
        vehicle_model: string;
        first_registration: string;
        mileage: string | number;
        vin: string;
        fuel_type: string;
        transmission: string;
    };
}>();
const photos = defineModel<File[]>('photos', { required: true });
const emit = defineEmits<{ next: []; back: [] }>();

const errors = ref<Record<string, string>>({});
const previews = computed(() => photos.value.map((f) => ({ name: f.name, url: URL.createObjectURL(f) })));

function onFiles(e: Event) {
    const files = Array.from((e.target as HTMLInputElement).files ?? []);
    photos.value = [...photos.value, ...files].slice(0, 8);
}
function removePhoto(i: number) {
    photos.value = photos.value.filter((_, idx) => idx !== i);
}

function proceed() {
    errors.value = {};
    if (!props.form.vehicle_make) errors.value.vehicle_make = 'Bitte geben Sie die Marke an.';
    if (!props.form.vehicle_model) errors.value.vehicle_model = 'Bitte geben Sie das Modell an.';
    if (props.form.vin && !/^[A-HJ-NPR-Z0-9]{17}$/i.test(props.form.vin))
        errors.value.vin = 'Die FIN muss aus 17 gültigen Zeichen bestehen.';
    if (Object.keys(errors.value).length === 0) emit('next');
}
</script>

<template>
    <div>
        <h2 class="font-display text-2xl font-bold text-navy-700">Angaben zum Fahrzeug</h2>
        <p class="mt-1 text-ink-500">Je genauer, desto passgenauer die Angebote.</p>

        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <FormField v-model="form.vehicle_make" label="Marke" required :error="errors.vehicle_make" placeholder="z. B. Volkswagen" />
            <FormField v-model="form.vehicle_model" label="Modell" required :error="errors.vehicle_model" placeholder="z. B. Golf VII" />
            <FormField v-model="form.first_registration" label="Erstzulassung" placeholder="MM/JJJJ" />
            <FormField v-model="form.mileage" label="Kilometerstand" type="number" inputmode="numeric" placeholder="z. B. 85000" />
            <FormField v-model="form.vin" label="FIN / VIN" :error="errors.vin" maxlength="17" placeholder="17-stellig (optional)" hint="Optional – erhöht die Genauigkeit." />
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-navy-700">Kraftstoff</label>
                    <select v-model="form.fuel_type" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none">
                        <option value="">–</option>
                        <option>Benzin</option>
                        <option>Diesel</option>
                        <option>Elektro</option>
                        <option>Hybrid</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-navy-700">Getriebe</label>
                    <select v-model="form.transmission" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none">
                        <option value="">–</option>
                        <option>Schaltgetriebe</option>
                        <option>Automatik</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <label class="mb-1.5 block text-sm font-semibold text-navy-700">Fotos (optional, max. 8)</label>
            <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-card border-2 border-dashed border-ink-300 px-6 py-8 text-center transition hover:border-green-500 hover:bg-green-50">
                <UploadCloud :size="28" class="text-ink-500" aria-hidden="true" />
                <span class="text-sm font-medium text-ink-700">Fotos hierher ziehen oder auswählen</span>
                <input type="file" accept="image/*" multiple class="hidden" @change="onFiles" />
            </label>
            <div v-if="previews.length" class="mt-4 grid grid-cols-3 gap-3 sm:grid-cols-4">
                <div v-for="(p, i) in previews" :key="i" class="group relative aspect-square overflow-hidden rounded-card">
                    <img :src="p.url" :alt="p.name" class="h-full w-full object-cover" />
                    <button type="button" class="absolute top-1 right-1 flex h-6 w-6 items-center justify-center rounded-pill bg-navy-950/70 text-white" @click="removePhoto(i)" aria-label="Foto entfernen">
                        <X :size="14" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </div>

        <WizardNav @back="$emit('back')" @next="proceed" />
    </div>
</template>
