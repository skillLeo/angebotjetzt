<script setup lang="ts">
import AdminTable from '@/components/dashboard/AdminTable.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/vue3';
import { Check, Search, Upload } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    inspectors: { data: Array<Record<string, unknown>>; links: Array<{ url: string | null; label: string; active: boolean }> };
    pendingCount: number;
    filters: { suche?: string; status?: string };
}>();

const search = ref(props.filters.suche ?? '');
const status = ref(props.filters.status ?? '');
function apply() {
    router.get('/admin/inspectors', { suche: search.value || undefined, status: status.value || undefined }, { preserveState: true });
}

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'city', label: 'Stadt' },
    { key: 'jobs', label: 'Aufträge', align: 'center' as const },
    { key: 'offers', label: 'Angebote', align: 'center' as const },
    { key: 'balance', label: 'Guthaben', align: 'right' as const },
    { key: 'active', label: 'Status' },
    { key: 'approveAction', label: '' },
];

function approve(id: number) {
    router.post(`/admin/inspectors/${id}/approve`, {}, { preserveScroll: true });
}
</script>

<template>
    <Head><title>Gutachter</title></Head>

    <PageCard title="Gutachterverwaltung" :subtitle="pendingCount > 0 ? `${pendingCount} Gutachter warten auf Freischaltung` : undefined">
        <template #actions>
            <div class="flex items-center gap-2">
                <select v-model="status" class="rounded-pill border border-ink-300 px-3 py-2 text-sm" @change="apply">
                    <option value="">Alle Status</option>
                    <option value="pending">Ausstehende Freischaltung</option>
                </select>
                <div class="relative">
                    <Search :size="16" class="absolute top-1/2 left-3 -translate-y-1/2 text-ink-300" aria-hidden="true" />
                    <input v-model="search" type="search" placeholder="Suche…" class="rounded-pill border border-ink-300 py-2 pr-3 pl-9 text-sm" @keyup.enter="apply" />
                </div>
                <Link href="/admin/inspectors/import" class="inline-flex items-center gap-2 rounded-pill bg-green-500 px-4 py-2 text-sm font-bold text-white transition hover:bg-green-600">
                    <Upload :size="16" aria-hidden="true" /> CSV-Import
                </Link>
            </div>
        </template>

        <AdminTable :columns="columns" :rows="inspectors.data" row-key="id">
            <template #name="{ row }">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-pill bg-green-50 font-display text-sm font-bold text-green-700">
                        {{ String(row.name).charAt(0) }}
                    </span>
                    <div class="min-w-0">
                        <Link :href="`/admin/inspectors/${row.id}`" class="font-semibold text-navy-700 hover:text-green-600 hover:underline">{{ row.name }}</Link>
                        <p class="truncate text-xs text-ink-500">{{ row.company }}</p>
                    </div>
                </div>
            </template>
            <template #balance="{ value }">{{ formatEuro(value as number) }}</template>
            <template #active="{ value, row }">
                <span v-if="!row.approved" class="inline-flex rounded-pill bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">
                    Ausstehend
                </span>
                <span v-else class="inline-flex rounded-pill px-2.5 py-1 text-xs font-bold" :class="value ? 'bg-green-50 text-green-700' : 'bg-ink-100 text-ink-500'">
                    {{ value ? 'Aktiv' : 'Inaktiv' }}
                </span>
            </template>
            <template #approveAction="{ row }">
                <button
                    v-if="!row.approved"
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-pill bg-green-500 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-green-600"
                    @click="approve(row.id as number)"
                >
                    <Check :size="14" aria-hidden="true" /> Freischalten
                </button>
            </template>
        </AdminTable>
        <Pagination :links="inspectors.links" />
    </PageCard>
</template>
