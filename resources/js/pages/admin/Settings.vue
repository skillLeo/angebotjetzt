<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { CheckCircle2, XCircle } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const props = defineProps<{
    settings: { commission_percent: number | string; stripe_configured: boolean };
}>();

const form = useForm({ commission_percent: props.settings.commission_percent });

function submit() {
    form.post('/admin/settings', {
        preserveScroll: true,
        onSuccess: () => toast.success('Einstellungen gespeichert.'),
    });
}
</script>

<template>
    <Head><title>Einstellungen</title></Head>

    <div class="grid gap-6 lg:grid-cols-2">
        <PageCard title="Provision">
            <form class="space-y-5 p-5 sm:p-6" @submit.prevent="submit">
                <div>
                    <label for="comm" class="mb-1.5 block text-sm font-semibold text-navy-700">Plattform-Provision (%)</label>
                    <input id="comm" v-model="form.commission_percent" type="number" step="0.1" min="0" max="50" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
                    <p v-if="form.errors.commission_percent" class="mt-1 text-sm text-red-600">{{ form.errors.commission_percent }}</p>
                    <p class="mt-1 text-xs text-ink-500">Wird auf jede neue Buchung angewendet.</p>
                </div>
                <button type="submit" :disabled="form.processing" class="rounded-pill bg-green-500 px-7 py-3 text-sm font-bold text-white transition hover:bg-green-600 disabled:opacity-60">
                    Speichern
                </button>
            </form>
        </PageCard>

        <PageCard title="Zahlungsanbindung">
            <div class="p-5 sm:p-6">
                <div class="flex items-center gap-3 rounded-card px-4 py-3" :class="settings.stripe_configured ? 'bg-green-50' : 'bg-amber-50'">
                    <CheckCircle2 v-if="settings.stripe_configured" :size="20" class="text-green-600" aria-hidden="true" />
                    <XCircle v-else :size="20" class="text-amber-600" aria-hidden="true" />
                    <div>
                        <p class="text-sm font-semibold" :class="settings.stripe_configured ? 'text-green-800' : 'text-amber-800'">
                            Stripe {{ settings.stripe_configured ? 'konfiguriert' : 'nicht konfiguriert' }}
                        </p>
                        <p class="text-xs text-ink-500">STRIPE_SECRET und STRIPE_WEBHOOK_SECRET in der .env-Datei setzen.</p>
                    </div>
                </div>
            </div>
        </PageCard>
    </div>
</template>
