<script setup lang="ts">
import AdminTable from '@/components/dashboard/AdminTable.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/vue3';
import { Wallet } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    wallets: { data: Array<Record<string, unknown>>; links: Array<{ url: string | null; label: string; active: boolean }> };
    totals: { available: number; pending: number };
    filters: { status?: string };
}>();

const status = ref(props.filters.status ?? '');
function apply() {
    router.get('/admin/wallets', { status: status.value || undefined }, { preserveState: true });
}

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
        <StatCard label="Verfügbares Guthaben (gesamt)" :value="formatEuro(totals.available)" :icon="Wallet" accent hint="Bereit zur Auszahlung an Gutachter" />
        <StatCard label="Ausstehendes Guthaben (gesamt)" :value="formatEuro(totals.pending)" :icon="Wallet" hint="Wird erst nach Auftragsbestätigung freigegeben" />
    </div>

    <PageCard title="Alle Wallets" subtitle="Guthabenstand jedes Gutachters: Verfügbar kann ausgezahlt werden, Ausstehend wartet noch auf Ihre Bestätigung des Auftrags">
        <template #actions>
            <select v-model="status" class="rounded-pill border border-ink-300 px-3 py-2 text-sm" @change="apply">
                <option value="">Alle</option>
                <option value="pending">Nur mit ausstehendem Guthaben</option>
                <option value="available">Nur mit verfügbarem Guthaben</option>
            </select>
        </template>
        <AdminTable :columns="columns" :rows="wallets.data" row-key="inspectorId">
            <template #name="{ row }">
                <Link :href="`/admin/inspectors/${row.inspectorId}`" class="font-semibold text-green-600 hover:underline">{{ row.name }}</Link>
            </template>
            <template #available="{ value }">{{ formatEuro(value as number) }}</template>
            <template #pending="{ value }">{{ formatEuro(value as number) }}</template>
            <template #lifetime="{ value }">{{ formatEuro(value as number) }}</template>
        </AdminTable>
        <Pagination :links="wallets.links" />
    </PageCard>
</template>
