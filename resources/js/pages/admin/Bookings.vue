<script setup lang="ts">
import AdminTable from '@/components/dashboard/AdminTable.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    bookings: { data: Array<Record<string, unknown>>; links: Array<{ url: string | null; label: string; active: boolean }> };
}>();

const columns = [
    { key: 'number', label: 'Auftrag' },
    { key: 'customer', label: 'Kunde' },
    { key: 'inspector', label: 'Gutachter' },
    { key: 'total', label: 'Gesamt', align: 'right' as const },
    { key: 'commission', label: 'Provision', align: 'right' as const },
    { key: 'inspectorShare', label: 'Gutachter-Anteil', align: 'right' as const },
    { key: 'status', label: 'Status' },
];
</script>

<template>
    <Head><title>Aufträge</title></Head>

    <PageCard title="Alle Aufträge">
        <AdminTable :columns="columns" :rows="bookings.data" row-key="id">
            <template #number="{ row }">
                <Link :href="`/admin/auftraege/${row.id}`" class="font-semibold text-green-600 hover:underline">{{ row.number }}</Link>
            </template>
            <template #total="{ value }">{{ formatEuro(value as number) }}</template>
            <template #commission="{ value }">{{ formatEuro(value as number) }}</template>
            <template #inspectorShare="{ value }">{{ formatEuro(value as number) }}</template>
            <template #status="{ value }"><StatusBadge :status="value as string" /></template>
        </AdminTable>
        <Pagination :links="bookings.links" />
    </PageCard>
</template>
