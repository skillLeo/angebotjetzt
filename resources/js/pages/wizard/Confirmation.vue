<script setup lang="ts">
import FormField from '@/components/forms/FormField.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CheckCircle2, KeyRound, LogIn, Mail } from 'lucide-vue-next';
import { Motion } from 'motion-v';

const props = defineProps<{
    request: { number: string; matched: number; unmatched: boolean; service: string; ort: string };
    canClaim: boolean;
    canLogin: boolean;
    contactEmail: string | null;
    contactName: string | null;
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
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-card bg-green-50 text-green-600">
                        <Mail :size="20" aria-hidden="true" />
                    </span>
                    <div>
                        <p class="font-display font-bold text-navy-700">
                            {{ 'Dienstleister benachrichtigt' }}
                        </p>
                        <p class="mt-1 text-sm text-ink-500">
                            Wir haben passende Dienstleister in {{ request.ort }} über Ihre Anfrage ({{ request.service }})
                            informiert. Die ersten Angebote treffen meist innerhalb weniger Stunden ein.
                        </p>
                    </div>
                </div>
            </div>

            <div v-if="canLogin" class="mt-8 rounded-panel border border-ink-100 bg-white p-6 text-left shadow-card">
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-card bg-navy-50 text-navy-700">
                        <LogIn :size="20" aria-hidden="true" />
                    </span>
                    <div>
                        <p class="font-display font-bold text-navy-700">Sie haben bereits ein Konto</p>
                        <p class="mt-1 text-sm text-ink-500">
                            Für <span class="font-semibold text-navy-700">{{ contactEmail }}</span> besteht bereits ein Konto bei uns.
                            Melden Sie sich an, um diese Anfrage und alle eingehenden Angebote in Ihrem Konto zu verfolgen.
                        </p>
                        <Link
                            href="/login"
                            class="mt-4 inline-block rounded-pill bg-green-500 px-7 py-3 font-bold text-white transition hover:bg-green-600"
                        >
                            Jetzt anmelden
                        </Link>
                    </div>
                </div>
            </div>

            <div v-if="canClaim" class="mt-8 rounded-panel border border-ink-100 bg-white p-6 text-left shadow-card">
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-card bg-navy-50 text-navy-700">
                        <KeyRound :size="20" aria-hidden="true" />
                    </span>
                    <div>
                        <p class="font-display font-bold text-navy-700">Konto anlegen und Anfrage verfolgen</p>
                        <p class="mt-1 text-sm text-ink-500">
                            Vergeben Sie ein Passwort, um Ihre Anfrage und alle Angebote jederzeit einzusehen. Ganz ohne Konto
                            geht es aber genauso weiter – wir informieren Sie per E-Mail.
                        </p>
                    </div>
                </div>
                <form class="mt-5 grid gap-4" @submit.prevent="claim">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-navy-700">Name</label>
                            <p class="rounded-card border border-ink-100 bg-sand-50 px-4 py-3 text-[15px] text-ink-700">{{ contactName }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-navy-700">E-Mail</label>
                            <p class="rounded-card border border-ink-100 bg-sand-50 px-4 py-3 text-[15px] text-ink-700">{{ contactEmail }}</p>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <FormField
                            v-model="claimForm.password"
                            label="Passwort"
                            type="password"
                            :error="claimForm.errors.password"
                        />
                        <FormField v-model="claimForm.password_confirmation" label="Passwort bestätigen" type="password" />
                    </div>
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
                <Link v-if="!canClaim && !canLogin" href="/account/requests" class="rounded-pill bg-green-500 px-7 py-3.5 font-bold text-white transition hover:bg-green-600">
                    Meine Anfragen ansehen
                </Link>
                <Link href="/" class="rounded-pill border border-ink-300 px-7 py-3.5 font-bold text-navy-700 transition hover:border-navy-700">
                    Zur Startseite
                </Link>
            </div>
        </div>
    </section>
</template>
