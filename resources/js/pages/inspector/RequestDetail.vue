<script setup lang="ts">
import LocationMap from '@/components/dashboard/LocationMap.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();

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
    <Head><title>{{ t('dashboard.inspectorPages.requestTitle') }} {{ request.number }}</title></Head>

    <Link href="/gutachter/anfragen" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-ink-500 hover:text-navy-700">
        <ArrowLeft :size="16" aria-hidden="true" /> {{ t('dashboard.inspectorPages.backToRequests') }}
    </Link>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <PageCard :title="`${request.vehicle.make} ${request.vehicle.model}`" :subtitle="`${request.service} · ${request.number}`">
                <template #actions><StatusBadge :status="request.status" /></template>
                <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                    <div><p class="text-sm text-ink-500">{{ t('dashboard.inspectorPages.firstRegistration') }}</p><p class="font-semibold text-navy-700">{{ request.vehicle.firstRegistration ?? '–' }}</p></div>
                    <div><p class="text-sm text-ink-500">{{ t('dashboard.inspectorPages.mileage') }}</p><p class="font-semibold text-navy-700">{{ request.vehicle.mileage ? request.vehicle.mileage.toLocaleString(locale === 'de' ? 'de-DE' : 'en-US') + ' km' : '–' }}</p></div>
                    <div><p class="text-sm text-ink-500">{{ t('dashboard.inspectorPages.vin') }}</p><p class="font-semibold text-navy-700">{{ request.vehicle.vin ?? '–' }}</p></div>
                    <div><p class="text-sm text-ink-500">{{ t('dashboard.inspectorPages.fuelTransmission') }}</p><p class="font-semibold text-navy-700">{{ request.vehicle.fuel ?? '–' }} / {{ request.vehicle.transmission ?? '–' }}</p></div>
                    <div><p class="text-sm text-ink-500">{{ t('dashboard.inspectorPages.preferredDate') }}</p><p class="font-semibold text-navy-700">{{ request.preferredDate ?? '–' }}</p></div>
                    <div><p class="text-sm text-ink-500">{{ t('dashboard.inspectorPages.alternativeDate') }}</p><p class="font-semibold text-navy-700">{{ request.alternativeDate ?? '–' }}</p></div>
                    <div v-if="request.notes" class="sm:col-span-2"><p class="text-sm text-ink-500">{{ t('dashboard.inspectorPages.customerNotes') }}</p><p class="text-navy-700">{{ request.notes }}</p></div>
                </div>
                <div v-if="request.photos.length" class="grid grid-cols-3 gap-3 px-5 pb-6 sm:grid-cols-4 sm:px-6">
                    <img v-for="(p, i) in request.photos" :key="i" :src="p" :alt="t('dashboard.inspectorPages.vehiclePhotoAlt')" class="aspect-square w-full rounded-card object-cover" />
                </div>
            </PageCard>

            <PageCard :title="t('dashboard.inspectorPages.location')">
                <div class="p-5 sm:p-6">
                    <p class="mb-3 text-sm text-ink-500">{{ t('dashboard.inspectorPages.region', { plz: request.plz, ort: request.ort }) }}</p>
                    <LocationMap :plz="request.plz" :ort="request.ort" />
                </div>
            </PageCard>
        </div>

        <div>
            <PageCard :title="t('dashboard.inspectorPages.yourOffer')">
                <div class="p-5 sm:p-6">
                    <div v-if="ownOffer" class="text-center">
                        <p class="text-sm text-ink-500">{{ t('dashboard.inspectorPages.youOffered') }}</p>
                        <p class="mt-1 font-display text-3xl font-extrabold text-navy-700">{{ formatEuro(ownOffer.price) }}</p>
                        <div class="mt-3 flex justify-center"><StatusBadge :status="ownOffer.status" /></div>
                    </div>
                    <div v-else>
                        <p class="text-sm text-ink-500">{{ t('dashboard.inspectorPages.noOfferYet') }}</p>
                        <Link :href="`/gutachter/anfragen/${request.id}/angebot`" class="mt-4 block rounded-pill bg-green-500 py-3 text-center text-sm font-bold text-white transition hover:bg-green-600">
                            {{ t('dashboard.inspectorPages.submitOffer') }}
                        </Link>
                    </div>
                </div>
            </PageCard>
        </div>
    </div>
</template>
