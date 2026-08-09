<script setup lang="ts">
import FormField from '@/components/forms/FormField.vue';
import WizardNav from '@/components/wizard/WizardNav.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    form: {
        service_type_id: number | null;
        vehicle_make: string;
        vehicle_model: string;
        plz: string;
        ort: string;
        contact_name: string;
        contact_email: string;
        contact_phone: string;
        agb: boolean;
        privacy: boolean;
        processing?: boolean;
        errors: Record<string, string>;
    };
    serviceTypes: Array<{ id: number; name: string }>;
    isGuest: boolean;
}>();
const emit = defineEmits<{ back: []; submit: [] }>();

const serviceName = computed(() => props.serviceTypes.find((t) => t.id === props.form.service_type_id)?.name ?? '');
</script>

<template>
    <div>
        <h2 class="font-display text-2xl font-bold text-navy-700">Ihre Kontaktdaten</h2>
        <p class="mt-1 text-ink-500">Damit die Dienstleister Ihnen Angebote senden können.</p>

        <div class="mt-6 grid gap-5">
            <FormField v-model="form.contact_name" label="Name" required :error="form.errors.contact_name" />
            <div class="grid gap-5 sm:grid-cols-2">
                <FormField v-model="form.contact_email" label="E-Mail" type="email" required :error="form.errors.contact_email" />
                <FormField v-model="form.contact_phone" label="Telefon" type="tel" required :error="form.errors.contact_phone" placeholder="+49 …" />
            </div>

            <template v-if="isGuest">
                <div class="rounded-card bg-sand-50 p-4">
                    <p class="text-sm font-semibold text-navy-700">Kein Konto nötig</p>
                    <p class="mt-1 text-sm text-ink-500">
                        Sie können Ihre Anfrage ohne Konto und ohne Passwort absenden. Direkt danach können Sie optional ein Konto anlegen, um sie später wiederzufinden.
                    </p>
                </div>
            </template>
        </div>

        <!-- Summary -->
        <div class="mt-7 rounded-card bg-sand-50 p-5">
            <p class="text-eyebrow mb-3 text-ink-500">Zusammenfassung</p>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-ink-500">Leistung</dt>
                    <dd class="text-right font-semibold text-navy-700">{{ serviceName }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-ink-500">Fahrzeug</dt>
                    <dd class="text-right font-semibold text-navy-700">{{ form.vehicle_make }} {{ form.vehicle_model }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-ink-500">Ort</dt>
                    <dd class="text-right font-semibold text-navy-700">{{ form.plz }} {{ form.ort }}</dd>
                </div>
            </dl>
        </div>

        <div class="mt-6 space-y-3">
            <label class="flex items-start gap-3">
                <input v-model="form.agb" type="checkbox" class="mt-0.5 h-5 w-5 shrink-0 accent-green-500" />
                <span class="text-sm text-ink-700">
                    Ich akzeptiere die <Link href="/terms" class="font-semibold text-green-600 underline" target="_blank">AGB</Link>.
                </span>
            </label>
            <p v-if="form.errors.agb" class="text-sm text-red-600">{{ form.errors.agb }}</p>
            <label class="flex items-start gap-3">
                <input v-model="form.privacy" type="checkbox" class="mt-0.5 h-5 w-5 shrink-0 accent-green-500" />
                <span class="text-sm text-ink-700">
                    Ich habe die <Link href="/privacy" class="font-semibold text-green-600 underline" target="_blank">Datenschutzerklärung</Link> gelesen.
                </span>
            </label>
            <p v-if="form.errors.privacy" class="text-sm text-red-600">{{ form.errors.privacy }}</p>
        </div>

        <WizardNav next-label="Anfrage kostenlos absenden" :processing="form.processing" @back="$emit('back')" @next="$emit('submit')" />
    </div>
</template>
