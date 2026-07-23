<script setup lang="ts">
import AdminTable from '@/components/dashboard/AdminTable.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    requests: { data: Array<Record<string, unknown>>; links: Array<{ url: string | null; label: string; active: boolean }> };
    filters: { status?: string; suche?: string };
}>();

const search = ref(props.filters.suche ?? '');
const status = ref(props.filters.status ?? '');

function apply() {
    router.get('/admin/requests', { suche: search.value || undefined, status: status.value || undefined }, { preserveState: true });
}

const columns = [
    { key: 'number', label: 'Nummer' },
    { key: 'customer', label: 'Kunde' },
    { key: 'service', label: 'Leistung' },
    { key: 'ort', label: 'Ort' },
    { key: 'offers', label: 'Angebote', align: 'center' as const },
    { key: 'status', label: 'Status' },
    { key: 'date', label: 'Datum' },
];
</script>

<template>
    <Head><title>Anfragen</title></Head>

    <PageCard title="Alle Anfragen">
        <template #actions>
            <div class="flex items-center gap-2">
                <select v-model="status" class="rounded-pill border border-ink-300 px-3 py-2 text-sm" @change="apply">
                    <option value="">Alle Status</option>
                    <option value="open">Offen</option>
                    <option value="offers_received">Angebote erhalten</option>
                    <option value="accepted">Angenommen</option>
                    <option value="completed">Abgeschlossen</option>
                    <option value="unmatched">Kein Gutachter</option>
                </select>
                <div class="relative">
                    <Search :size="16" class="absolute top-1/2 left-3 -translate-y-1/2 text-ink-300" aria-hidden="true" />
                    <input v-model="search" type="search" placeholder="Suche…" class="rounded-pill border border-ink-300 py-2 pr-3 pl-9 text-sm" @keyup.enter="apply" />
                </div>
            </div>
        </template>

        <AdminTable :columns="columns" :rows="requests.data" row-key="id">
            <template #number="{ row }">
                <Link :href="`/admin/requests/${row.id}`" class="font-semibold text-green-600 hover:underline">{{ row.number }}</Link>
            </template>
            <template #status="{ value }"><StatusBadge :status="value as string" /></template>
        </AdminTable>
        <Pagination :links="requests.links" />
    </PageCard>
</template>
