<script setup lang="ts">
import SplitAuthShell from '@/components/auth/SplitAuthShell.vue';
import FormField from '@/components/forms/FormField.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { UserPlus } from 'lucide-vue-next';


const form = useForm({
    company_name: '', name: '', email: '', phone: '', city: '',
    password: '', password_confirmation: '', plz_from: '', plz_to: '', agb: false,
});

function submit() {
    form.post('/inspector/register', { onFinish: () => form.reset('password', 'password_confirmation') });
}
</script>

<template>
    <Head><title>{{ 'Als Gutachter registrieren' }}</title></Head>

    <SplitAuthShell
        :quote="'Endlich passende Aufträge ohne Kaltakquise – ich bestimme meine Preise selbst.'"
        quote-author="Sabine Krüger, Kfz-Sachverständige"
        :title="'Als Gutachter registrieren'"
        :description="'Erhalten Sie passende Anfragen aus Ihrer Region automatisch.'"
        :icon="UserPlus"
    >
        <form class="space-y-4" @submit.prevent="submit">
            <FormField v-model="form.company_name" :label="'Firma (optional)'" :error="form.errors.company_name" />
            <div class="grid gap-4 sm:grid-cols-2">
                <FormField v-model="form.name" :label="'Name'" required :error="form.errors.name" />
                <FormField v-model="form.phone" :label="'Telefon'" required :error="form.errors.phone" />
            </div>
            <FormField v-model="form.email" :label="'E-Mail'" type="email" required :error="form.errors.email" />
            <FormField v-model="form.city" :label="'Stadt (Ihr Servicegebiet)'" required :error="form.errors.city" />
            <div class="grid gap-4 sm:grid-cols-2">
                <FormField v-model="form.plz_from" :label="'PLZ von (optional)'" inputmode="numeric" :maxlength="5" :error="form.errors.plz_from" />
                <FormField v-model="form.plz_to" :label="'PLZ bis (optional)'" inputmode="numeric" :maxlength="5" :error="form.errors.plz_to" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <FormField v-model="form.password" :label="'Passwort'" type="password" required :error="form.errors.password" />
                <FormField v-model="form.password_confirmation" :label="'Passwort bestätigen'" type="password" required />
            </div>
            <label class="flex items-start gap-3 text-sm text-ink-700">
                <input v-model="form.agb" type="checkbox" class="mt-0.5 h-5 w-5 accent-green-500" />
                <span>{{ 'Ich akzeptiere die' }} <Link href="/terms" class="font-semibold text-green-600 underline" target="_blank">{{ 'AGB' }}</Link> {{ 'und' }} <Link href="/privacy" class="font-semibold text-green-600 underline" target="_blank">{{ 'Datenschutzerklärung' }}</Link>.</span>
            </label>
            <p v-if="form.errors.agb" class="text-sm text-red-600">{{ form.errors.agb }}</p>

            <button type="submit" :disabled="form.processing" class="w-full rounded-pill bg-green-500 py-3.5 font-bold text-white transition hover:bg-green-600 disabled:opacity-60">
                {{ 'Konto erstellen' }}
            </button>
            <p class="text-center text-sm text-ink-500">
                {{ 'Bereits registriert?' }} <Link href="/login" class="font-semibold text-green-600">{{ 'Zum Login' }}</Link>
            </p>
        </form>
    </SplitAuthShell>
</template>
