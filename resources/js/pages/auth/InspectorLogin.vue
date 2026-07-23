<script setup lang="ts">
import SplitAuthShell from '@/components/auth/SplitAuthShell.vue';
import FormField from '@/components/forms/FormField.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Wrench } from 'lucide-vue-next';

const form = useForm({ email: '', password: '', remember: false });

function submit() {
    form.post('/inspector/login', { onFinish: () => form.reset('password') });
}
</script>

<template>
    <Head><title>{{ 'Gutachter-Login' }}</title></Head>

    <SplitAuthShell
        :quote="'Seit ich bei AngebotJetzt bin, kommen die passenden Aufträge automatisch – ganz ohne Akquise.'"
        quote-author="Thomas Bergmann, Kfz-Sachverständiger"
    >
        <span class="mb-5 flex h-11 w-11 items-center justify-center rounded-card bg-green-50 text-green-600">
            <Wrench :size="21" aria-hidden="true" />
        </span>
        <h1 class="font-display text-2xl font-bold text-navy-700">{{ 'Gutachter-Portal' }}</h1>
        <p class="mt-1.5 text-[15px] text-ink-500">{{ 'Melden Sie sich an, um Anfragen zu sehen und Angebote abzugeben.' }}</p>

        <form class="mt-8 space-y-5" @submit.prevent="submit">
            <FormField v-model="form.email" :label="'E-Mail'" type="email" required :error="form.errors.email" />
            <FormField v-model="form.password" :label="'Passwort'" type="password" required />
            <label class="flex items-center gap-2 text-sm text-ink-700">
                <input v-model="form.remember" type="checkbox" class="h-4 w-4 accent-green-500" /> {{ 'Angemeldet bleiben' }}
            </label>
            <button type="submit" :disabled="form.processing" class="w-full rounded-pill bg-green-500 py-3.5 font-bold text-white transition hover:bg-green-600 disabled:opacity-60">
                {{ 'Anmelden' }}
            </button>
        </form>
    </SplitAuthShell>
</template>
