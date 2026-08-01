<script setup lang="ts">
import FormField from '@/components/forms/FormField.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CheckCircle2, Clock, KeyRound, Mail } from 'lucide-vue-next';
import { Motion } from 'motion-v';

const props = defineProps<{
    request: { number: string; matched: number; unmatched: boolean; service: string; ort: string };
    canClaim: boolean;
    contactEmail: string | null;
}>();

const claimForm = useForm({
    password: '',
    password_confirmation: '',
});

function claim() {
    claimForm.post(`/request/confirmation/${props.request.number}/claim`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head>
        <title>Anfrage gesendet</title>
    </Head>

    <section class="flex min-h-[70vh] items-center bg-sand-50 px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-xl text-center">
            <Motion :initial="{ scale: 0.6, opacity: 0 }" :animate="{ scale: 1, opacity: 1 }" :transition="{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }">
                <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-pill bg-green-500 text-white">
                    <CheckCircle2 :size="42" aria-hidden="true" />
                </span>
            </Motion>

            <h1 class="text-section mt-8 text-navy-700">Ihre Anfrage ist unterwegs!</h1>
            <p class="mt-3 text-ink-500">Anfrage-Nummer <span class="font-bold text-navy-700">{{ request.number }}</span></p>

            <div class="mt-8 rounded-panel border border-ink-100 bg-white p-6 text-left shadow-card">
                <template v-if="!unmatched">
                    <div class="flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-card bg-green-50 text-green-600">
                            <Mail :size="20" aria-hidden="true" />
                        </span>
                        <div>
                            <p class="font-display font-bold text-navy-700">{{ request.matched }} Gutachter benachrichtigt</p>
                            <p class="mt-1 text-sm text-ink-500">
                                Wir haben passende Gutachter in {{ request.ort }} über Ihre Anfrage ({{ request.service }})
                                informiert. Die ersten Angebote treffen meist innerhalb weniger Stunden ein.
                            </p>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div class="flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-card bg-amber-100 text-amber-700">
                            <Clock :size="20" aria-hidden="true" />
                        </span>
                        <div>
                            <p class="font-display font-bold text-navy-700">Wir melden uns persönlich</p>
                            <p class="mt-1 text-sm text-ink-500">
                                Für Ihre Region suchen wir gerade passende Gutachter. Unser Team meldet sich
                                in Kürze persönlich bei Ihnen.
                            </p>
                        </div>
                    </div>
                </template>
            </div>

            <div v-if="canClaim" class="mt-8 rounded-panel border border-ink-100 bg-white p-6 text-left shadow-card">
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-card bg-navy-50 text-navy-700">
                        <KeyRound :size="20" aria-hidden="true" />
                    </span>
                    <div>
                        <p class="font-display font-bold text-navy-700">Konto anlegen und Anfrage verfolgen</p>
                        <p class="mt-1 text-sm text-ink-500">
                            Optional: Vergeben Sie ein Passwort für <span class="font-semibold text-navy-700">{{ contactEmail }}</span>,
                            um Ihre Anfrage und alle Angebote jederzeit einzusehen. Ganz ohne Konto geht es aber genauso weiter –
                            wir informieren Sie per E-Mail.
                        </p>
                    </div>
                </div>
                <form class="mt-5 grid gap-4 sm:grid-cols-2" @submit.prevent="claim">
                    <FormField
                        v-model="claimForm.password"
                        label="Passwort"
                        type="password"
                        :error="claimForm.errors.password"
                    />
                    <FormField v-model="claimForm.password_confirmation" label="Passwort bestätigen" type="password" />
                    <div class="sm:col-span-2">
                        <button
                            type="submit"
                            :disabled="claimForm.processing || !claimForm.password"
                            class="rounded-pill bg-green-500 px-7 py-3 font-bold text-white transition hover:bg-green-600 disabled:cursor-not-allowed disabled:bg-ink-300"
                        >
                            Konto erstellen
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <Link v-if="!canClaim" href="/account/requests" class="rounded-pill bg-green-500 px-7 py-3.5 font-bold text-white transition hover:bg-green-600">
                    Meine Anfragen ansehen
                </Link>
                <Link href="/" class="rounded-pill border border-ink-300 px-7 py-3.5 font-bold text-navy-700 transition hover:border-navy-700">
                    Zur Startseite
                </Link>
            </div>
        </div>
    </section>
</template>
