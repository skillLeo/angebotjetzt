<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

defineProps<{
    request: {
        id: number; number: string; status: string; service: string; date: string;
        customer: { name: string; email: string; phone: string | null };
        vehicle: { make: string; model: string; firstRegistration: string | null; mileage: number | null; vin: string | null; fuel: string | null; transmission: string | null };
        location: { plz: string; ort: string; strasse: string | null };
        notes: string | null;
        matches: Array<{ inspector: string; company: string | null; city: string | null; notified: string | null; viewed: string | null }>;
        offers: Array<{ id: number; inspector: string; price: number; status: string; date: string }>;
    };
}>();
</script>

<template>
    <Head><title>Anfrage {{ request.number }}</title></Head>

    <Link href="/admin/anfragen" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-ink-500 hover:text-navy-700">
        <ArrowLeft :size="16" aria-hidden="true" /> Zurück
    </Link>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <PageCard :title="`${request.vehicle.make} ${request.vehicle.model}`" :subtitle="`${request.service} · ${request.number}`">
                <template #actions><StatusBadge :status="request.status" /></template>
                <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                    <div><p class="text-sm text-ink-500">Kunde</p><p class="font-semibold text-navy-700">{{ request.customer.name }}</p><p class="text-sm text-ink-500">{{ request.customer.email }} · {{ request.customer.phone }}</p></div>
                    <div><p class="text-sm text-ink-500">Ort</p><p class="font-semibold text-navy-700">{{ request.location.plz }} {{ request.location.ort }}</p></div>
                    <div><p class="text-sm text-ink-500">Fahrzeug</p><p class="font-semibold text-navy-700">EZ {{ request.vehicle.firstRegistration ?? '–' }} · {{ request.vehicle.mileage ? request.vehicle.mileage.toLocaleString('de-DE') + ' km' : '–' }}</p></div>
                    <div><p class="text-sm text-ink-500">FIN</p><p class="font-semibold text-navy-700">{{ request.vehicle.vin ?? '–' }}</p></div>
                </div>
            </PageCard>

            <PageCard title="Eingegangene Angebote">
                <div v-if="request.offers.length" class="divide-y divide-ink-100">
                    <div v-for="o in request.offers" :key="o.id" class="flex items-center justify-between px-5 py-3.5 sm:px-6">
                        <div><p class="font-semibold text-navy-700">{{ o.inspector }}</p><p class="text-xs text-ink-500">{{ o.date }}</p></div>
                        <div class="flex items-center gap-3"><span class="font-display font-bold text-navy-700">{{ formatEuro(o.price) }}</span><StatusBadge :status="o.status" /></div>
                    </div>
                </div>
                <p v-else class="px-5 py-6 text-center text-sm text-ink-500">Noch keine Angebote.</p>
            </PageCard>
        </div>

        <div>
            <PageCard title="Benachrichtigte Gutachter">
                <div v-if="request.matches.length" class="divide-y divide-ink-100">
                    <div v-for="(m, i) in request.matches" :key="i" class="px-5 py-3 sm:px-6">
                        <p class="text-sm font-semibold text-navy-700">{{ m.inspector }}</p>
                        <p class="text-xs text-ink-500">{{ m.city }} · {{ m.viewed ? 'Gesehen' : 'Benachrichtigt' }}</p>
                    </div>
                </div>
                <p v-else class="px-5 py-6 text-center text-sm text-ink-500">Keine passenden Gutachter.</p>
            </PageCard>
        </div>
    </div>
</template>
