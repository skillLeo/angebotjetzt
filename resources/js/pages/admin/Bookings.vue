<script setup lang="ts">
import AdminTable from '@/components/dashboard/AdminTable.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    bookings: { data: Array<Record<string, unknown>>; links: Array<{ url: string | null; label: string; active: boolean }> };
    filters: { status?: string };
}>();

const status = ref(props.filters.status ?? '');
function apply() {
    router.get('/admin/bookings', { status: status.value || undefined }, { preserveState: true });
}

const columns = [
    { key: 'number', label: 'Auftrag' },
    { key: 'customer', label: 'Kunde' },
    { key: 'inspector', label: 'Dienstleister' },
    { key: 'total', label: 'Gesamt', align: 'right' as const },
    { key: 'commission', label: 'Provision', align: 'right' as const },
    { key: 'inspectorShare', label: 'Dienstleister-Anteil', align: 'right' as const },
    { key: 'status', label: 'Status' },
    { key: 'date', label: 'Datum' },
];
</script>

<template>
    <Head><title>Aufträge</title></Head>

    <PageCard title="Alle Aufträge">
        <template #actions>
            <select v-model="status" class="rounded-pill border border-ink-300 px-3 py-2 text-sm" @change="apply">
                <option value="">Alle Status</option>
                <option value="accepted">Angenommen</option>
                <option value="in_progress">In Bearbeitung</option>
                <option value="completed_by_inspector">Abgeschlossen (Dienstleister)</option>
                <option value="confirmed">Bestätigt</option>
                <option value="cancelled">Storniert</option>
                <option value="refunded">Erstattet</option>
            </select>
        </template>
        <AdminTable :columns="columns" :rows="bookings.data" row-key="id">
            <template #number="{ row }">
                <Link :href="`/admin/bookings/${row.id}`" class="font-semibold text-green-600 hover:underline">{{ row.number }}</Link>
            </template>
            <template #total="{ value }">{{ formatEuro(value as number) }}</template>
            <template #commission="{ value }">{{ formatEuro(value as number) }}</template>
            <template #inspectorShare="{ value }">{{ formatEuro(value as number) }}</template>
            <template #status="{ value }"><StatusBadge :status="value as string" /></template>
        </AdminTable>
        <Pagination :links="bookings.links" />
    </PageCard>
</template>
