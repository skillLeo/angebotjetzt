<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

defineProps<{
    request: {
        id: number; number: string; service: string; vehicle: string; ort: string; plz: string;
        status: string; matched: number; notes: string | null;
        vehicle: { make: string; model: string; firstRegistration: string | null; mileage: number | null; fuel: string | null; transmission: string | null };
        photos: string[];
    };
    offers: Array<{ id: number; price: number; message: string | null; estimatedDate: string | null; status: string; inspector: { name: string; company: string | null; city: string | null; verified: boolean; experience: number | null } }>;
}>();
</script>

<template>
    <Head><title>Anfrage {{ request.number }}</title></Head>

    <Link href="/konto/anfragen" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-ink-500 hover:text-navy-700">
        <ArrowLeft :size="16" aria-hidden="true" /> Zurück zu Anfragen
    </Link>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <PageCard :title="`${request.vehicle.make} ${request.vehicle.model}`" :subtitle="`${request.service} · ${request.number}`">
                <template #actions><StatusBadge :status="request.status" /></template>
                <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                    <div><p class="text-sm text-ink-500">Erstzulassung</p><p class="font-semibold text-navy-700">{{ request.vehicle.firstRegistration ?? '–' }}</p></div>
                    <div><p class="text-sm text-ink-500">Kilometerstand</p><p class="font-semibold text-navy-700">{{ request.vehicle.mileage ? request.vehicle.mileage.toLocaleString('de-DE') + ' km' : '–' }}</p></div>
                    <div><p class="text-sm text-ink-500">Kraftstoff</p><p class="font-semibold text-navy-700">{{ request.vehicle.fuel ?? '–' }}</p></div>
                    <div><p class="text-sm text-ink-500">Getriebe</p><p class="font-semibold text-navy-700">{{ request.vehicle.transmission ?? '–' }}</p></div>
                    <div><p class="text-sm text-ink-500">Ort</p><p class="font-semibold text-navy-700">{{ request.plz }} {{ request.ort }}</p></div>
                    <div v-if="request.notes" class="sm:col-span-2"><p class="text-sm text-ink-500">Anmerkungen</p><p class="text-navy-700">{{ request.notes }}</p></div>
                </div>
                <div v-if="request.photos.length" class="grid grid-cols-3 gap-3 px-5 pb-6 sm:grid-cols-4 sm:px-6">
                    <img v-for="(p, i) in request.photos" :key="i" :src="p" alt="Fahrzeugfoto" class="aspect-square w-full rounded-card object-cover" />
                </div>
            </PageCard>
        </div>

        <div>
            <PageCard title="Angebote">
                <div v-if="offers.length" class="space-y-3 p-5">
                    <div v-for="o in offers" :key="o.id" class="rounded-card border border-ink-100 p-4">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-navy-700">{{ o.inspector.name }}</p>
                            <span class="font-display text-lg font-extrabold text-navy-700">{{ formatEuro(o.price) }}</span>
                        </div>
                        <p class="text-sm text-ink-500">{{ o.inspector.city }}</p>
                    </div>
                    <Link :href="`/konto/anfragen/${request.id}/angebote`" class="mt-2 block rounded-pill bg-green-500 py-3 text-center text-sm font-bold text-white transition hover:bg-green-600">
                        Angebote vergleichen
                    </Link>
                </div>
                <p v-else class="px-5 py-8 text-center text-sm text-ink-500">
                    Noch keine Angebote. Wir haben {{ request.matched }} Gutachter benachrichtigt.
                </p>
            </PageCard>
        </div>
    </div>
</template>
