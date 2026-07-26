<script setup lang="ts">
import SplitAuthShell from '@/components/auth/SplitAuthShell.vue';
import FormField from '@/components/forms/FormField.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LayoutDashboard } from 'lucide-vue-next';

const form = useForm({ email: '', password: '', remember: false });

function submit() {
    form.post('/admin/login', { onFinish: () => form.reset('password') });
}
</script>

<template>
    <Head><title>{{ 'Admin-Login' }}</title></Head>

    <SplitAuthShell
        :quote="'Volle Kontrolle über jede Anfrage, jedes Angebot und jeden Euro – an einem Ort.'"
        :title="'Admin-Panel'"
        :description="'Melden Sie sich mit Ihren Administrator-Zugangsdaten an.'"
        :icon="LayoutDashboard"
        icon-class="bg-navy-50 text-navy-700"
    >
        <form class="space-y-5" @submit.prevent="submit">
            <FormField v-model="form.email" :label="'E-Mail'" type="email" required :error="form.errors.email" />
            <FormField v-model="form.password" :label="'Passwort'" type="password" required />
            <label class="flex items-center gap-2 text-sm text-ink-700">
                <input v-model="form.remember" type="checkbox" class="h-4 w-4 accent-green-500" /> {{ 'Angemeldet bleiben' }}
            </label>
            <button type="submit" :disabled="form.processing" class="w-full rounded-pill bg-navy-700 py-3.5 font-bold text-white transition hover:bg-navy-800 disabled:opacity-60">
                {{ 'Anmelden' }}
            </button>
        </form>
    </SplitAuthShell>
</template>
