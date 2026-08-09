<script setup lang="ts">
import AdminTable from '@/components/dashboard/AdminTable.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/vue3';
import { Banknote, Percent } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    payments: { data: Array<Record<string, unknown>>; links: Array<{ url: string | null; label: string; active: boolean }> };
    totals: { revenue: number; commission: number };
    filters: { status?: string };
}>();

const status = ref(props.filters.status ?? '');
function apply() {
    router.get('/admin/payments', { status: status.value || undefined }, { preserveState: true });
}

const columns = [
    { key: 'booking', label: 'Auftrag' },
    { key: 'customer', label: 'Kunde' },
    { key: 'inspector', label: 'Dienstleister' },
    { key: 'total', label: 'Gesamt', align: 'right' as const },
    { key: 'commission', label: 'Provision', align: 'right' as const },
    { key: 'status', label: 'Status' },
    { key: 'date', label: 'Datum' },
];
</script>

<template>
    <Head><title>Zahlungen</title></Head>

    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <StatCard label="Umsatz gesamt" :value="formatEuro(totals.revenue)" :icon="Banknote" accent hint="Summe aller erfolgreich bezahlten Zahlungen" />
        <StatCard label="Provision gesamt" :value="formatEuro(totals.commission)" :icon="Percent" accent hint="Ihr Anteil davon" />
    </div>

    <PageCard title="Alle Zahlungen" subtitle="Jede über Stripe abgewickelte Kundenzahlung für einen Auftrag">
        <template #actions>
            <select v-model="status" class="rounded-pill border border-ink-300 px-3 py-2 text-sm" @change="apply">
                <option value="">Alle Status</option>
                <option value="pending">Ausstehend</option>
                <option value="paid">Bezahlt</option>
                <option value="failed">Fehlgeschlagen</option>
                <option value="refunded">Erstattet</option>
            </select>
        </template>
        <AdminTable :columns="columns" :rows="payments.data" row-key="id">
            <template #booking="{ row }">
                <Link :href="`/admin/bookings/${row.bookingId}`" class="font-semibold text-green-600 hover:underline">{{ row.booking }}</Link>
            </template>
            <template #total="{ value }">{{ formatEuro(value as number) }}</template>
            <template #commission="{ value }">{{ formatEuro(value as number) }}</template>
            <template #status="{ value }"><StatusBadge :status="value as string" /></template>
        </AdminTable>
        <Pagination :links="payments.links" />
    </PageCard>
</template>
