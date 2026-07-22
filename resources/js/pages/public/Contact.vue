<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Mail, MapPin, Phone } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const form = useForm({ name: '', email: '', subject: '', message: '' });

function submit() {
    form.post('/kontakt', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toast.success('Nachricht gesendet. Wir melden uns bei Ihnen.');
        },
    });
}
</script>

<template>
    <Head>
        <title>Kontakt</title>
        <meta name="description" content="Kontaktieren Sie das Team von AngebotJetzt – wir helfen Ihnen gern weiter." />
    </Head>

    <section class="bg-sand-100 px-4 py-14 sm:px-6 lg:px-8 lg:py-16">
        <div class="mx-auto max-w-7xl">
            <p class="text-eyebrow mb-4 text-green-600">Kontakt</p>
            <h1 class="text-hero max-w-2xl text-navy-700">Wir sind für Sie da</h1>
        </div>
    </section>

    <section class="bg-white py-16 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[1fr_1.2fr] lg:px-8">
            <div class="space-y-6">
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 items-center justify-center rounded-card bg-green-50 text-green-600">
                        <Mail :size="20" aria-hidden="true" />
                    </span>
                    <div>
                        <p class="font-display font-bold text-navy-700">E-Mail</p>
                        <p class="text-ink-500">kontakt@angebotjetzt.de</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 items-center justify-center rounded-card bg-green-50 text-green-600">
                        <Phone :size="20" aria-hidden="true" />
                    </span>
                    <div>
                        <p class="font-display font-bold text-navy-700">Telefon</p>
                        <p class="text-ink-500">+49 30 1234567</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 items-center justify-center rounded-card bg-green-50 text-green-600">
                        <MapPin :size="20" aria-hidden="true" />
                    </span>
                    <div>
                        <p class="font-display font-bold text-navy-700">Adresse</p>
                        <p class="text-ink-500">Musterstraße 1<br />10115 Berlin</p>
                    </div>
                </div>
            </div>

            <form class="rounded-panel border border-ink-100 bg-white p-7 shadow-card" @submit.prevent="submit">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="c-name" class="mb-1.5 block text-sm font-semibold text-navy-700">Name</label>
                        <input id="c-name" v-model="form.name" type="text" required class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500" />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label for="c-email" class="mb-1.5 block text-sm font-semibold text-navy-700">E-Mail</label>
                        <input id="c-email" v-model="form.email" type="email" required class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500" />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>
                </div>
                <div class="mt-5">
                    <label for="c-subject" class="mb-1.5 block text-sm font-semibold text-navy-700">Betreff</label>
                    <input id="c-subject" v-model="form.subject" type="text" required class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500" />
                    <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600">{{ form.errors.subject }}</p>
                </div>
                <div class="mt-5">
                    <label for="c-message" class="mb-1.5 block text-sm font-semibold text-navy-700">Nachricht</label>
                    <textarea id="c-message" v-model="form.message" rows="5" required class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500" />
                    <p v-if="form.errors.message" class="mt-1 text-sm text-red-600">{{ form.errors.message }}</p>
                </div>
                <button type="submit" :disabled="form.processing" class="mt-6 w-full rounded-pill bg-green-500 py-3.5 font-bold text-white transition hover:bg-green-600 disabled:opacity-60">
                    Nachricht senden
                </button>
            </form>
        </div>
    </section>
</template>
