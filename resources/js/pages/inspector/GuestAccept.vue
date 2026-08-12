<script setup lang="ts">
import CenteredAuthShell from '@/components/auth/CenteredAuthShell.vue';
import AcceptConditionsDialog from '@/components/inspector/AcceptConditionsDialog.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { AlertTriangle, ClipboardCheck, Scale } from 'lucide-vue-next';
import { ref } from 'vue';

/**
 * Invited guest for a direct-accept service. Deliberately carries no price
 * field: the fee follows from the damage established on inspection, so this
 * mirrors the registered provider's accept-outright screen.
 */
const props = defineProps<{
    email: string;
    commissionPercent: number;
    request: {
        id: number; number: string; service: string; vehicle: string; ort: string;
        accidentRole: string | null; hasLawyer: string | null;
    };
}>();

const form = useForm({ name: '', company_name: '', agb: false });
const confirming = ref(false);

function openDialog() {
    form.clearErrors();
    if (!form.name.trim()) {
        form.setError('name', 'Bitte geben Sie Ihren Namen an.');
        return;
    }
    if (!form.agb) {
        form.setError('agb', 'Bitte akzeptieren Sie die AGB.');
        return;
    }
    confirming.value = true;
}

function accept() {
    form.post(`/inspector/invite/${props.request.id}/accept`, {
        onFinish: () => (confirming.value = false),
    });
}
</script>

<template>
    <Head><title>{{ 'Anfrage annehmen' }}</title></Head>

    <CenteredAuthShell
        :title="'Anfrage annehmen'"
        :description="`${request.vehicle} · ${request.service} · ${request.ort} · ${request.number}`"
        :icon="ClipboardCheck"
    >
        <div class="space-y-5">
            <p class="rounded-card bg-sand-50 p-3 text-sm text-ink-500">
                {{ `Sie nehmen diese Anfrage als ${email} an. Ein Konto legen wir für Sie an, sobald Sie die Anfrage annehmen.` }}
            </p>

            <!-- The customer's answers, same as a registered provider sees. -->
            <div class="rounded-card border border-navy-100 bg-navy-50 p-4">
                <p class="font-display font-bold text-navy-700">{{ 'Angaben zum Unfall' }}</p>
                <div class="mt-3 space-y-2.5">
                    <div class="flex items-start gap-2.5">
                        <AlertTriangle :size="16" class="mt-0.5 shrink-0 text-navy-600" aria-hidden="true" />
                        <div>
                            <p class="text-sm text-ink-500">{{ 'Rolle beim Unfall' }}</p>
                            <p class="font-semibold text-navy-700">{{ request.accidentRole ?? 'Keine Angabe' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <Scale :size="16" class="mt-0.5 shrink-0 text-navy-600" aria-hidden="true" />
                        <div>
                            <p class="text-sm text-ink-500">{{ 'Anwalt beauftragt' }}</p>
                            <p class="font-semibold text-navy-700">{{ request.hasLawyer ?? 'Keine Angabe' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-sm leading-relaxed text-ink-700">
                {{ 'Für diese Leistung wird kein Festpreis angeboten. Ihr Honorar richtet sich nach der tatsächlich festgestellten Schadenhöhe. Wenn Sie die Anfrage annehmen, wird sie Ihnen sofort verbindlich zugewiesen und Sie erhalten die Kontaktdaten des Kunden.' }}
            </p>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ 'Ihr Name *' }}</label>
                    <input id="name" v-model="form.name" type="text" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label for="company" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ 'Firma (optional)' }}</label>
                    <input id="company" v-model="form.company_name" type="text" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
                </div>
            </div>

            <label class="flex items-start gap-3 text-sm text-ink-700">
                <input v-model="form.agb" type="checkbox" class="mt-0.5 h-5 w-5 accent-green-500" />
                <span>{{ 'Ich akzeptiere die' }} <a href="/terms" target="_blank" class="font-semibold text-green-600 underline">{{ 'AGB' }}</a> {{ 'für Dienstleister.' }}</span>
            </label>
            <p v-if="form.errors.agb" class="text-sm text-red-600">{{ form.errors.agb }}</p>

            <button
                type="button"
                :disabled="form.processing"
                class="w-full rounded-pill bg-green-500 py-3.5 font-bold text-white transition hover:bg-green-600 disabled:opacity-60"
                @click="openDialog"
            >
                {{ 'Anfrage verbindlich annehmen' }}
            </button>
        </div>
    </CenteredAuthShell>

    <AcceptConditionsDialog
        v-if="confirming"
        :commission-percent="commissionPercent"
        :processing="form.processing"
        @cancel="confirming = false"
        @confirm="accept"
    />
</template>
