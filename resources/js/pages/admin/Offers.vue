<script setup lang="ts">
import AdminTable from '@/components/dashboard/AdminTable.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    offers: { data: Array<Record<string, unknown>>; links: Array<{ url: string | null; label: string; active: boolean }> };
    filters: { status?: string };
}>();

const status = ref(props.filters.status ?? '');
function apply() {
    router.get('/admin/angebote', { status: status.value || undefined }, { preserveState: true });
}

const columns = [
    { key: 'requestNumber', label: 'Anfrage' },
    { key: 'inspector', label: 'Gutachter' },
    { key: 'ort', label: 'Ort' },
    { key: 'price', label: 'Preis', align: 'right' as const },
    { key: 'commission', label: 'Provision', align: 'right' as const },
    { key: 'status', label: 'Status' },
    { key: 'date', label: 'Datum' },
];
</script>

<template>
    <Head><title>Angebote</title></Head>

    <PageCard title="Alle Angebote">
        <template #actions>
            <select v-model="status" class="rounded-pill border border-ink-300 px-3 py-2 text-sm" @change="apply">
                <option value="">Alle Status</option>
                <option value="open">Offen</option>
                <option value="accepted">Angenommen</option>
                <option value="rejected">Abgelehnt</option>
            </select>
        </template>
        <AdminTable :columns="columns" :rows="offers.data" row-key="id">
            <template #requestNumber="{ row }">
                <Link :href="`/admin/anfragen/${row.requestId}`" class="font-semibold text-green-600 hover:underline">{{ row.requestNumber }}</Link>
            </template>
            <template #price="{ value }">{{ formatEuro(value as number) }}</template>
            <template #commission="{ value }">{{ formatEuro(value as number) }}</template>
            <template #status="{ value }"><StatusBadge :status="value as string" /></template>
        </AdminTable>
        <Pagination :links="offers.links" />
    </PageCard>
</template>
