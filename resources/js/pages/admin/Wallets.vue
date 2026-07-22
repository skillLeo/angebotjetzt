<script setup lang="ts">
import AdminTable from '@/components/dashboard/AdminTable.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link } from '@inertiajs/vue3';
import { Wallet } from 'lucide-vue-next';

defineProps<{
    wallets: { data: Array<Record<string, unknown>>; links: Array<{ url: string | null; label: string; active: boolean }> };
    totals: { available: number; pending: number };
}>();

const columns = [
    { key: 'name', label: 'Gutachter' },
    { key: 'city', label: 'Stadt' },
    { key: 'available', label: 'Verfügbar', align: 'right' as const },
    { key: 'pending', label: 'Ausstehend', align: 'right' as const },
    { key: 'lifetime', label: 'Gesamt', align: 'right' as const },
];
</script>

<template>
    <Head><title>Wallets</title></Head>

    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <StatCard label="Verfügbares Guthaben (gesamt)" :value="formatEuro(totals.available)" :icon="Wallet" accent />
        <StatCard label="Ausstehendes Guthaben (gesamt)" :value="formatEuro(totals.pending)" :icon="Wallet" />
    </div>

    <PageCard title="Alle Wallets">
        <AdminTable :columns="columns" :rows="wallets.data" row-key="inspectorId">
            <template #name="{ row }">
                <Link :href="`/admin/gutachter/${row.inspectorId}`" class="font-semibold text-green-600 hover:underline">{{ row.name }}</Link>
            </template>
            <template #available="{ value }">{{ formatEuro(value as number) }}</template>
            <template #pending="{ value }">{{ formatEuro(value as number) }}</template>
            <template #lifetime="{ value }">{{ formatEuro(value as number) }}</template>
        </AdminTable>
        <Pagination :links="wallets.links" />
    </PageCard>
</template>
