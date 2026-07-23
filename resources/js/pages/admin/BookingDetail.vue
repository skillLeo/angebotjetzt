<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{
    booking: {
        id: number; number: string; status: string; service: string; vehicle: string; requestId: number; requestNumber: string;
        customer: { name: string; email: string; phone: string | null };
        inspector: { id: number; name: string; company_name: string | null; city: string | null; email: string };
        moneyTrail: { total: number; commission: number; inspectorShare: number; stripeRef: string | null; paidAt: string | null } | null;
        completedAt: string | null; confirmedAt: string | null;
        review: { rating: number; comment: string | null } | null;
    };
}>();

function confirm() {
    router.post(`/admin/bookings/${props.booking.id}/confirm`, {}, { preserveScroll: true });
}
</script>

<template>
    <Head><title>Auftrag {{ booking.number }}</title></Head>

    <Link href="/admin/bookings" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-ink-500 hover:text-navy-700">
        <ArrowLeft :size="16" aria-hidden="true" /> Zurück
    </Link>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <PageCard :title="`${booking.vehicle} · ${booking.service}`" :subtitle="booking.number">
                <template #actions><StatusBadge :status="booking.status" /></template>
                <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                    <div><p class="text-sm text-ink-500">Kunde</p><p class="font-semibold text-navy-700">{{ booking.customer.name }}</p><p class="text-sm text-ink-500">{{ booking.customer.email }}</p></div>
                    <div>
                        <p class="text-sm text-ink-500">Gutachter</p>
                        <Link :href="`/admin/inspectors/${booking.inspector.id}`" class="font-semibold text-green-600 hover:underline">{{ booking.inspector.name }}</Link>
                        <p class="text-sm text-ink-500">{{ booking.inspector.city }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-ink-500">Anfrage</p>
                        <Link :href="`/admin/requests/${booking.requestId}`" class="font-semibold text-green-600 hover:underline">{{ booking.requestNumber }}</Link>
                    </div>
                    <div v-if="booking.review"><p class="text-sm text-ink-500">Bewertung</p><p class="font-semibold text-navy-700">{{ booking.review.rating }}/5</p></div>
                </div>
                <div v-if="booking.status === 'completed_by_inspector'" class="border-t border-ink-100 p-5 sm:p-6">
                    <button type="button" class="rounded-pill bg-green-500 px-6 py-3 text-sm font-bold text-white transition hover:bg-green-600" @click="confirm">
                        Abschluss bestätigen & Guthaben freigeben
                    </button>
                </div>
            </PageCard>
        </div>

        <div>
            <PageCard title="Geldfluss">
                <div v-if="booking.moneyTrail" class="space-y-3 p-5 text-sm sm:p-6">
                    <div class="flex justify-between"><span class="text-ink-500">Kunde bezahlt</span><span class="font-bold text-navy-700">{{ formatEuro(booking.moneyTrail.total) }}</span></div>
                    <div class="flex justify-between"><span class="text-ink-500">Plattform-Provision</span><span class="font-bold text-green-600">{{ formatEuro(booking.moneyTrail.commission) }}</span></div>
                    <div class="flex justify-between border-t border-ink-100 pt-3"><span class="text-ink-500">Gutachter-Anteil</span><span class="font-bold text-navy-700">{{ formatEuro(booking.moneyTrail.inspectorShare) }}</span></div>
                    <div class="pt-2 text-xs text-ink-500">Stripe: {{ booking.moneyTrail.stripeRef ?? '–' }}<br>Bezahlt: {{ booking.moneyTrail.paidAt ?? '–' }}</div>
                </div>
                <p v-else class="px-5 py-6 text-center text-sm text-ink-500">Noch keine Zahlung.</p>
            </PageCard>
        </div>
    </div>
</template>
