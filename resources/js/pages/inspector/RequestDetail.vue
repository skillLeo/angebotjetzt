<script setup lang="ts">
import LocationMap from '@/components/dashboard/LocationMap.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertTriangle, ArrowLeft, Scale, ShieldCheck } from 'lucide-vue-next';
import { ref } from 'vue';


const props = defineProps<{
    request: {
        id: number; number: string; service: string; ort: string; plz: string; status: string;
        vehicle: { make: string; model: string; firstRegistration: string | null; mileage: number | null; vin: string | null; fuel: string | null; transmission: string | null };
        preferredDate: string | null; alternativeDate: string | null; notes: string | null; photos: string[];
        directAccept: boolean; accidentRole: string | null; hasLawyer: string | null;
    };
    ownOffer: { price: number | null; message: string | null; estimatedDate: string | null; status: string; editedAt: string | null } | null;
    competingOffers: number[];
    commissionPercent: number;
}>();

const confirming = ref(false);
const accepting = ref(false);

function acceptRequest() {
    accepting.value = true;
    router.post(`/inspector/requests/${props.request.id}/accept`, {}, {
        onFinish: () => {
            accepting.value = false;
            confirming.value = false;
        },
    });
}
</script>

<template>
    <Head><title>{{ 'Anfrage' }} {{ request.number }}</title></Head>

    <Link href="/inspector/requests" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-ink-500 hover:text-navy-700">
        <ArrowLeft :size="16" aria-hidden="true" /> {{ 'Zurück zu Anfragen' }}
    </Link>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <PageCard :title="`${request.vehicle.make} ${request.vehicle.model}`" :subtitle="`${request.service} · ${request.number}`">
                <template #actions><StatusBadge :status="request.status" /></template>
                <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                    <div><p class="text-sm text-ink-500">{{ 'Erstzulassung' }}</p><p class="font-semibold text-navy-700">{{ request.vehicle.firstRegistration ?? '–' }}</p></div>
                    <div><p class="text-sm text-ink-500">{{ 'Kilometerstand' }}</p><p class="font-semibold text-navy-700">{{ request.vehicle.mileage ? request.vehicle.mileage.toLocaleString('de-DE') + ' km' : '–' }}</p></div>
                    <div><p class="text-sm text-ink-500">{{ 'FIN' }}</p><p class="font-semibold text-navy-700">{{ request.vehicle.vin ?? '–' }}</p></div>
                    <div><p class="text-sm text-ink-500">{{ 'Kraftstoff / Getriebe' }}</p><p class="font-semibold text-navy-700">{{ request.vehicle.fuel ?? '–' }} / {{ request.vehicle.transmission ?? '–' }}</p></div>
                    <div><p class="text-sm text-ink-500">{{ 'Wunschtermin' }}</p><p class="font-semibold text-navy-700">{{ request.preferredDate ?? '–' }}</p></div>
                    <div><p class="text-sm text-ink-500">{{ 'Alternativtermin' }}</p><p class="font-semibold text-navy-700">{{ request.alternativeDate ?? '–' }}</p></div>
                    <div v-if="request.notes" class="sm:col-span-2"><p class="text-sm text-ink-500">{{ 'Anmerkungen des Kunden' }}</p><p class="text-navy-700">{{ request.notes }}</p></div>
                </div>
                <!-- Answers to the two questions asked up front for this service. -->
                <div v-if="request.directAccept" class="mx-5 mb-6 rounded-card border border-navy-100 bg-navy-50 p-5 sm:mx-6">
                    <p class="font-display font-bold text-navy-700">{{ 'Angaben zum Unfall' }}</p>
                    <div class="mt-3 grid gap-4 sm:grid-cols-2">
                        <div class="flex items-start gap-2.5">
                            <AlertTriangle :size="17" class="mt-0.5 shrink-0 text-navy-600" aria-hidden="true" />
                            <div>
                                <p class="text-sm text-ink-500">{{ 'Rolle beim Unfall' }}</p>
                                <p class="font-semibold text-navy-700">{{ request.accidentRole ?? 'Keine Angabe' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <Scale :size="17" class="mt-0.5 shrink-0 text-navy-600" aria-hidden="true" />
                            <div>
                                <p class="text-sm text-ink-500">{{ 'Anwalt beauftragt' }}</p>
                                <p class="font-semibold text-navy-700">{{ request.hasLawyer ?? 'Keine Angabe' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="request.photos.length" class="grid grid-cols-3 gap-3 px-5 pb-6 sm:grid-cols-4 sm:px-6">
                    <img v-for="(p, i) in request.photos" :key="i" :src="p" :alt="'Fahrzeugfoto'" class="aspect-square w-full rounded-card object-cover" />
                </div>
            </PageCard>

            <PageCard :title="'Standort'">
                <div class="p-6 sm:p-8">
                    <p class="mb-3 text-sm text-ink-500">{{ `Region: ${request.plz} ${request.ort}` }}</p>
                    <LocationMap :plz="request.plz" :ort="request.ort" />
                </div>
            </PageCard>
        </div>

        <div class="space-y-6">
            <PageCard v-if="competingOffers.length && !request.directAccept" :title="'Andere Angebote für diese Anfrage'">
                <div class="space-y-2 p-5 text-sm sm:p-6">
                    <p class="text-ink-500">{{ `${competingOffers.length} weitere${competingOffers.length === 1 ? 's' : ''} Angebot${competingOffers.length === 1 ? '' : 'e'} bereits eingegangen:` }}</p>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="(price, i) in competingOffers" :key="i" class="rounded-pill bg-sand-100 px-3 py-1 font-semibold text-navy-700">
                            {{ formatEuro(price) }}
                        </span>
                    </div>
                </div>
            </PageCard>

            <!-- Direct-accept services carry no price: accepting IS the assignment. -->
            <PageCard v-if="request.directAccept" :title="'Anfrage annehmen'">
                <div class="p-6 sm:p-8">
                    <div v-if="request.status === 'accepted'" class="text-center">
                        <ShieldCheck :size="30" class="mx-auto text-green-600" aria-hidden="true" />
                        <p class="mt-2 font-display font-bold text-navy-700">{{ 'Diese Anfrage wurde bereits vergeben.' }}</p>
                    </div>
                    <div v-else>
                        <p class="text-sm text-ink-700">
                            {{ 'Für diese Leistung wird kein Festpreis angeboten. Ihr Honorar richtet sich nach der tatsächlich festgestellten Schadenhöhe.' }}
                        </p>
                        <p class="mt-3 text-sm text-ink-500">
                            {{ 'Wenn Sie die Anfrage annehmen, wird sie Ihnen sofort verbindlich zugewiesen und Sie erhalten die Kontaktdaten des Kunden.' }}
                        </p>
                        <button
                            type="button"
                            class="mt-5 w-full rounded-pill bg-green-500 py-3 text-sm font-bold text-white transition hover:bg-green-600"
                            @click="confirming = true"
                        >
                            {{ 'Anfrage verbindlich annehmen' }}
                        </button>
                    </div>
                </div>
            </PageCard>

            <PageCard v-else :title="'Ihr Angebot'">
                <div class="p-6 sm:p-8">
                    <div v-if="ownOffer" class="text-center">
                        <p class="text-sm text-ink-500">{{ 'Sie haben angeboten' }}</p>
                        <p class="mt-1 font-display text-3xl font-extrabold text-navy-700">{{ formatEuro(ownOffer.price) }}</p>
                        <div class="mt-3 flex justify-center"><StatusBadge :status="ownOffer.status" /></div>
                        <p v-if="ownOffer.estimatedDate" class="mt-3 text-sm text-ink-500">{{ 'Voraussichtlich fertig bis' }} {{ ownOffer.estimatedDate }}</p>
                        <p v-if="ownOffer.message" class="mt-2 text-sm text-ink-700">{{ ownOffer.message }}</p>
                        <p v-if="ownOffer.editedAt" class="mt-3 text-xs text-ink-500">{{ 'Zuletzt bearbeitet am' }} {{ ownOffer.editedAt }}</p>
                        <Link
                            v-if="ownOffer.status === 'open'"
                            :href="`/inspector/requests/${request.id}/offer/edit`"
                            class="mt-4 block rounded-pill border border-ink-300 py-3 text-center text-sm font-bold text-navy-700 transition hover:border-navy-700"
                        >
                            {{ 'Angebot bearbeiten' }}
                        </Link>
                    </div>
                    <div v-else>
                        <p class="text-sm text-ink-500">{{ 'Sie haben noch kein Angebot abgegeben.' }}</p>
                        <Link :href="`/inspector/requests/${request.id}/offer`" class="mt-4 block rounded-pill bg-green-500 py-3 text-center text-sm font-bold text-white transition hover:bg-green-600">
                            {{ 'Angebot abgeben' }}
                        </Link>
                    </div>
                </div>
            </PageCard>
        </div>
    </div>

    <!-- Binding-acceptance confirmation. Cancelling leaves the request untouched. -->
    <div
        v-if="confirming"
        class="fixed inset-0 z-50 flex items-center justify-center bg-navy-900/60 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="accept-dialog-title"
        @click.self="confirming = false"
    >
        <div class="w-full max-w-lg rounded-panel bg-white p-6 shadow-lift sm:p-8">
            <h2 id="accept-dialog-title" class="font-display text-xl font-bold text-navy-700">
                {{ 'Anfrage verbindlich annehmen' }}
            </h2>
            <p class="mt-2 text-sm text-ink-500">
                {{ 'Bitte bestätigen Sie die folgenden Bedingungen:' }}
            </p>

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
                    @click="confirming = false"
                >
                    {{ 'Abbrechen' }}
                </button>
                <button
                    type="button"
                    :disabled="accepting"
                    class="rounded-pill bg-green-500 px-6 py-3 text-sm font-bold text-white transition hover:bg-green-600 disabled:opacity-60"
                    @click="acceptRequest"
                >
                    {{ accepting ? 'Wird angenommen…' : 'Bedingungen akzeptieren und annehmen' }}
                </button>
            </div>
        </div>
    </div>
</template>
