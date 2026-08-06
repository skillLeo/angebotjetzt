<script setup lang="ts">
import CenteredAuthShell from '@/components/auth/CenteredAuthShell.vue';
import FormField from '@/components/forms/FormField.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ClipboardCheck } from 'lucide-vue-next';

const props = defineProps<{
    profile: {
        birthday: string | null; tax_id: string | null; street: string | null;
        plz: string | null; city: string | null; phone: string | null;
    };
}>();

function isoDateYearsAgo(years: number): string {
    const d = new Date();
    d.setFullYear(d.getFullYear() - years);
    return d.toISOString().slice(0, 10);
}

// Matches the server-side rule exactly (must be 18+) so the picker can't
// even offer a date that would fail validation on submit.
const maxBirthday = isoDateYearsAgo(18);
const minBirthday = isoDateYearsAgo(100);

const form = useForm({
    birthday: props.profile.birthday ?? '',
    tax_id: props.profile.tax_id ?? '',
    street: props.profile.street ?? '',
    plz: props.profile.plz ?? '',
    city: props.profile.city ?? '',
    phone: props.profile.phone ?? '',
});

function submit() {
    form.post('/inspector/complete-profile');
}
</script>

<template>
    <Head><title>{{ 'Konto vervollständigen' }}</title></Head>

    <CenteredAuthShell
        :title="'Vervollständigen Sie Ihr Konto'"
        :description="'Diese Angaben benötigen wir, bevor unser Team Ihr Konto freischalten kann.'"
        :icon="ClipboardCheck"
    >
        <form class="space-y-4" @submit.prevent="submit">
            <div class="grid gap-4 sm:grid-cols-2">
                <FormField v-model="form.birthday" :label="'Geburtsdatum'" type="date" required :min="minBirthday" :max="maxBirthday" :error="form.errors.birthday" />
                <FormField v-model="form.phone" :label="'Telefon'" required :error="form.errors.phone" />
            </div>
            <FormField v-model="form.tax_id" :label="'Steuernummer / USt-IdNr.'" required :error="form.errors.tax_id" />
            <FormField v-model="form.street" :label="'Straße & Hausnummer (Firmensitz)'" required :error="form.errors.street" />
            <div class="grid gap-4 sm:grid-cols-2">
                <FormField v-model="form.plz" :label="'PLZ'" inputmode="numeric" :maxlength="5" required :error="form.errors.plz" />
                <FormField v-model="form.city" :label="'Stadt (Rechnungsadresse)'" required :error="form.errors.city" />
            </div>
            <p class="text-sm text-ink-500">
                {{ 'Ihr Servicegebiet legen Sie fest, sobald Ihr Konto freigeschaltet ist.' }}
            </p>
            <button type="submit" :disabled="form.processing" class="w-full rounded-pill bg-green-500 py-3.5 font-bold text-white transition hover:bg-green-600 disabled:opacity-60">
                {{ 'Konto vervollständigen' }}
            </button>
        </form>
    </CenteredAuthShell>
</template>
