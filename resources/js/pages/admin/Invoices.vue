<script setup lang="ts">
import AdminTable from '@/components/dashboard/AdminTable.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/vue3';
import { Download, Search } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    invoices: { data: Array<Record<string, unknown>>; links: Array<{ url: string | null; label: string; active: boolean }> };
    filters: { suche?: string };
}>();

const search = ref(props.filters.suche ?? '');
function apply() {
    router.get('/admin/invoices', { suche: search.value || undefined }, { preserveState: true });
}

const columns = [
    { key: 'number', label: 'Rechnungsnummer' },
    { key: 'booking', label: 'Auftrag' },
    { key: 'inspector', label: 'Dienstleister' },
    { key: 'offerAmount', label: 'Auftragswert', align: 'right' as const },
    { key: 'commissionAmount', label: 'Provision', align: 'right' as const },
    { key: 'dueDate', label: 'Fällig am' },
    { key: 'date', label: 'Erstellt am' },
    { key: 'download', label: '' },
];
</script>

<template>
    <Head><title>Rechnungen</title></Head>

    <PageCard title="Provisionsrechnungen" subtitle="Alle an Dienstleister gestellten Provisionsrechnungen (10% je angenommenem Angebot)">
        <template #actions>
            <div class="relative">
                <Search :size="16" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-ink-300" aria-hidden="true" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Rechnung oder Dienstleister suchen…"
                    class="rounded-pill border border-ink-300 py-2 pl-9 pr-3 text-sm"
                    @keyup.enter="apply"
                />
            </div>
        </template>
        <AdminTable :columns="columns" :rows="invoices.data" row-key="id">
            <template #booking="{ row }">
                <Link :href="`/admin/bookings/${row.bookingId}`" class="font-semibold text-green-600 hover:underline">{{ row.booking }}</Link>
            </template>
            <template #offerAmount="{ value }">{{ formatEuro(value as number) }}</template>
            <template #commissionAmount="{ value }">{{ formatEuro(value as number) }}</template>
            <template #download="{ row }">
                <a
                    :href="`/admin/invoices/${row.id}/download`"
                    class="inline-flex items-center gap-1.5 rounded-pill border border-ink-300 px-3 py-1.5 text-xs font-bold text-navy-700 transition hover:border-navy-700"
                >
                    <Download :size="14" aria-hidden="true" /> PDF
                </a>
            </template>
        </AdminTable>
        <Pagination :links="invoices.links" />
    </PageCard>
</template>
