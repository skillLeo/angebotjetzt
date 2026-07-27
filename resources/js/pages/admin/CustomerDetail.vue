<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, FileText, Package } from 'lucide-vue-next';

defineProps<{
    customer: {
        id: number; name: string; email: string; phone: string | null; since: string;
        requestsCount: number; bookingsCount: number;
        requests: Array<{ id: number; number: string; service: string; vehicle: string; status: string; date: string }>;
        bookings: Array<{ id: number; number: string; inspector: string; status: string; date: string }>;
    };
}>();
</script>

<template>
    <Head><title>{{ customer.name }}</title></Head>

    <Link href="/admin/customers" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-ink-500 hover:text-navy-700">
        <ArrowLeft :size="16" aria-hidden="true" /> Zurück
    </Link>

    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-navy-700">{{ customer.name }}</h1>
        <p class="text-ink-500">{{ customer.email }}<span v-if="customer.phone"> · {{ customer.phone }}</span> · Kunde seit {{ customer.since }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <StatCard label="Anfragen" :value="customer.requestsCount" :icon="FileText" />
        <StatCard label="Aufträge" :value="customer.bookingsCount" :icon="Package" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <PageCard title="Anfragen" subtitle="Die letzten Kfz-Gutachten-Anfragen dieses Kunden">
            <div v-if="customer.requests.length" class="divide-y divide-ink-100">
                <Link
                    v-for="r in customer.requests"
                    :key="r.id"
                    :href="`/admin/requests/${r.id}`"
                    class="flex items-center justify-between gap-3 px-5 py-3.5 transition hover:bg-sand-50 sm:px-6"
                >
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-navy-700">{{ r.vehicle }} · {{ r.service }}</p>
                        <p class="text-xs text-ink-500">{{ r.number }} · {{ r.date }}</p>
                    </div>
                    <StatusBadge :status="r.status" />
                </Link>
            </div>
            <p v-else class="px-5 py-8 text-center text-sm text-ink-500">Noch keine Anfragen.</p>
        </PageCard>

        <PageCard title="Aufträge" subtitle="Bezahlte, beauftragte Gutachten dieses Kunden">
            <div v-if="customer.bookings.length" class="divide-y divide-ink-100">
                <Link
                    v-for="b in customer.bookings"
                    :key="b.id"
                    :href="`/admin/bookings/${b.id}`"
                    class="flex items-center justify-between gap-3 px-5 py-3.5 transition hover:bg-sand-50 sm:px-6"
                >
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-navy-700">{{ b.number }}</p>
                        <p class="text-xs text-ink-500">{{ b.inspector }} · {{ b.date }}</p>
                    </div>
                    <StatusBadge :status="b.status" />
                </Link>
            </div>
            <p v-else class="px-5 py-8 text-center text-sm text-ink-500">Noch keine Aufträge.</p>
        </PageCard>
    </div>
</template>
