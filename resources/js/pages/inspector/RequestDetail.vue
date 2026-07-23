<script setup lang="ts">
import LocationMap from '@/components/dashboard/LocationMap.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';


defineProps<{
    request: {
        id: number; number: string; service: string; ort: string; plz: string; status: string;
        vehicle: { make: string; model: string; firstRegistration: string | null; mileage: number | null; vin: string | null; fuel: string | null; transmission: string | null };
        preferredDate: string | null; alternativeDate: string | null; notes: string | null; photos: string[];
    };
    ownOffer: { price: number; status: string } | null;
}>();
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

        <div>
            <PageCard :title="'Ihr Angebot'">
                <div class="p-6 sm:p-8">
                    <div v-if="ownOffer" class="text-center">
                        <p class="text-sm text-ink-500">{{ 'Sie haben angeboten' }}</p>
                        <p class="mt-1 font-display text-3xl font-extrabold text-navy-700">{{ formatEuro(ownOffer.price) }}</p>
                        <div class="mt-3 flex justify-center"><StatusBadge :status="ownOffer.status" /></div>
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
</template>
