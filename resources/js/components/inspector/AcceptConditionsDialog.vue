<script setup lang="ts">
import { AlertTriangle, Scale, ShieldCheck } from 'lucide-vue-next';

/**
 * The binding-acceptance conditions for direct-accept services. Shared by the
 * registered-provider request page and the invited-guest page so both show
 * exactly the same three conditions; they must not be able to drift apart.
 */
defineProps<{ commissionPercent: number; processing?: boolean }>();
defineEmits<{ cancel: []; confirm: [] }>();
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-navy-900/60 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="accept-dialog-title"
        @click.self="$emit('cancel')"
    >
        <div class="w-full max-w-lg rounded-panel bg-white p-6 shadow-lift sm:p-8">
            <h2 id="accept-dialog-title" class="font-display text-xl font-bold text-navy-700">
                {{ 'Anfrage verbindlich annehmen' }}
            </h2>
            <p class="mt-2 text-sm text-ink-500">{{ 'Bitte bestätigen Sie die folgenden Bedingungen:' }}</p>

            <ul class="mt-5 space-y-4">
                <li class="flex items-start gap-3">
                    <ShieldCheck :size="18" class="mt-0.5 shrink-0 text-green-600" aria-hidden="true" />
                    <p class="text-sm text-ink-700">
                        {{ 'Sie nehmen diese Anfrage verbindlich an. Der Auftrag wird Ihnen sofort fest zugewiesen.' }}
                    </p>
                </li>
                <li class="flex items-start gap-3">
                    <AlertTriangle :size="18" class="mt-0.5 shrink-0 text-green-600" aria-hidden="true" />
                    <p class="text-sm text-ink-700">
                        {{ 'Das endgültige Sachverständigenhonorar richtet sich nach der tatsächlich festgestellten Schadenhöhe und steht zum Zeitpunkt der Annahme noch nicht fest.' }}
                    </p>
                </li>
                <li class="flex items-start gap-3">
                    <Scale :size="18" class="mt-0.5 shrink-0 text-green-600" aria-hidden="true" />
                    <p class="text-sm text-ink-700">
                        {{ `AngebotJetzt berechnet eine Provision von ${commissionPercent} % auf den Betrag, den Sie für diesen Auftrag tatsächlich vereinnahmen.` }}
                    </p>
                </li>
            </ul>

            <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    class="rounded-pill border border-ink-300 px-6 py-3 text-sm font-bold text-ink-700 transition hover:bg-sand-50"
                    @click="$emit('cancel')"
                >
                    {{ 'Abbrechen' }}
                </button>
                <button
                    type="button"
                    :disabled="processing"
                    class="rounded-pill bg-green-500 px-6 py-3 text-sm font-bold text-white transition hover:bg-green-600 disabled:opacity-60"
                    @click="$emit('confirm')"
                >
                    {{ processing ? 'Wird angenommen…' : 'Bedingungen akzeptieren und annehmen' }}
                </button>
            </div>
        </div>
    </div>
</template>
