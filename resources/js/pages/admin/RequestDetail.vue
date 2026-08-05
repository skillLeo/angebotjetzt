<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Send } from 'lucide-vue-next';

const props = defineProps<{
    request: {
        id: number; number: string; status: string; service: string; date: string;
        customer: { name: string; email: string; phone: string | null };
        vehicle: { make: string; model: string; firstRegistration: string | null; mileage: number | null; vin: string | null; fuel: string | null; transmission: string | null };
        location: { plz: string; ort: string; strasse: string | null };
        notes: string | null;
        matches: Array<{ inspector: string; company: string | null; city: string | null; notified: string | null; viewed: string | null }>;
        offers: Array<{ id: number; inspector: string; price: number; status: string; date: string }>;
        invitedProviders: Array<{ email: string | null; date: string }>;
    };
}>();

const inviteForm = useForm({ email: '' });

function invite() {
    inviteForm.post(`/admin/requests/${props.request.id}/invite-provider`, {
        preserveScroll: true,
        onSuccess: () => inviteForm.reset('email'),
    });
}
</script>

<template>
    <Head><title>Anfrage {{ request.number }}</title></Head>

    <Link href="/admin/requests" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-ink-500 hover:text-navy-700">
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

            <PageCard title="Nicht registrierten Anbieter einladen" subtitle="Sendet einen Link zu dieser Anfrage per E-Mail" class="mt-6">
                <form class="flex flex-col gap-3 p-5 sm:p-6" @submit.prevent="invite">
                    <input
                        v-model="inviteForm.email"
                        type="email"
                        required
                        placeholder="anbieter@beispiel.de"
                        class="w-full rounded-card border border-ink-300 px-4 py-2.5 text-sm focus:border-green-500 focus:outline-none"
                    />
                    <p v-if="inviteForm.errors.email" class="text-sm text-red-600">{{ inviteForm.errors.email }}</p>
                    <button
                        type="submit"
                        :disabled="inviteForm.processing"
                        class="inline-flex items-center justify-center gap-2 rounded-pill bg-navy-700 py-2.5 text-sm font-bold text-white transition hover:bg-navy-800 disabled:opacity-60"
                    >
                        <Send :size="15" aria-hidden="true" /> Einladen
                    </button>
                </form>
                <div v-if="request.invitedProviders.length" class="border-t border-ink-100 px-5 py-4 sm:px-6">
                    <p class="mb-2 text-xs font-semibold text-ink-500">Bisher eingeladen</p>
                    <div class="space-y-1.5">
                        <div v-for="(inv, i) in request.invitedProviders" :key="i" class="flex items-center justify-between text-sm">
                            <span class="text-navy-700">{{ inv.email }}</span>
                            <span class="text-xs text-ink-500">{{ inv.date }}</span>
                        </div>
                    </div>
                </div>
            </PageCard>
        </div>
    </div>
</template>
