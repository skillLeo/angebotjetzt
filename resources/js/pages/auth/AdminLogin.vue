<script setup lang="ts">
import SplitAuthShell from '@/components/auth/SplitAuthShell.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ email: '', password: '', remember: false });

function submit() {
    form.post('/admin/login', { onFinish: () => form.reset('password') });
}
</script>

<template>
    <Head><title>Admin-Login</title></Head>

    <SplitAuthShell quote="Volle Kontrolle über jede Anfrage, jedes Angebot und jeden Euro – an einem Ort.">
        <h1 class="font-display text-3xl font-extrabold text-navy-700">Admin-Panel</h1>
        <p class="mt-2 text-ink-500">Melden Sie sich mit Ihren Administrator-Zugangsdaten an.</p>

        <form class="mt-8 space-y-5" @submit.prevent="submit">
            <div>
                <label for="email" class="mb-1.5 block text-sm font-semibold text-navy-700">E-Mail</label>
                <input id="email" v-model="form.email" type="email" required autocomplete="email" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500" />
                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
            </div>
            <div>
                <label for="password" class="mb-1.5 block text-sm font-semibold text-navy-700">Passwort</label>
                <input id="password" v-model="form.password" type="password" required autocomplete="current-password" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500" />
            </div>
            <label class="flex items-center gap-2 text-sm text-ink-700">
                <input v-model="form.remember" type="checkbox" class="h-4 w-4 accent-green-500" /> Angemeldet bleiben
            </label>
            <button type="submit" :disabled="form.processing" class="w-full rounded-pill bg-navy-700 py-3.5 font-bold text-white transition hover:bg-navy-800 disabled:opacity-60">
                Anmelden
            </button>
        </form>
    </SplitAuthShell>
</template>
