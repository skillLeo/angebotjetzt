<script setup lang="ts">
import CenteredAuthShell from '@/components/auth/CenteredAuthShell.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { MailCheck } from 'lucide-vue-next';

defineProps<{ email: string }>();

const form = useForm({});

function resend() {
    form.post('/inspector/verify-email/resend');
}

function logout() {
    router.post('/inspector/logout');
}
</script>

<template>
    <Head><title>{{ 'E-Mail bestätigen' }}</title></Head>

    <CenteredAuthShell
        :title="'Bitte bestätigen Sie Ihre E-Mail-Adresse'"
        :description="`Wir haben einen Bestätigungslink an ${email} gesendet. Bitte klicken Sie auf den Link, um fortzufahren.`"
        :icon="MailCheck"
    >
        <div class="space-y-4 text-center">
            <p class="text-sm text-ink-500">{{ 'Keine E-Mail erhalten? Prüfen Sie auch Ihren Spam-Ordner.' }}</p>
            <button
                type="button"
                :disabled="form.processing"
                class="w-full rounded-pill bg-green-500 py-3.5 font-bold text-white transition hover:bg-green-600 disabled:opacity-60"
                @click="resend"
            >
                {{ 'E-Mail erneut senden' }}
            </button>
            <button type="button" class="w-full text-sm font-semibold text-ink-500 hover:text-navy-700" @click="logout">
                {{ 'Abmelden' }}
            </button>
        </div>
    </CenteredAuthShell>
</template>
