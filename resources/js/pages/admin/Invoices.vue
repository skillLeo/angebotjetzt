<script setup lang="ts">
import AdminTable from '@/components/dashboard/AdminTable.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/vue3';
import { Check, CircleDollarSign, Download, Search } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    invoices: { data: Array<Record<string, unknown>>; links: Array<{ url: string | null; label: string; active: boolean }> };
    totals: { outstanding: number; paid: number };
    filters: { suche?: string; status?: string };
}>();

const search = ref(props.filters.suche ?? '');
const status = ref(props.filters.status ?? '');
function apply() {
    router.get('/admin/invoices', { suche: search.value || undefined, status: status.value || undefined }, { preserveState: true });
}

const marking = ref<number | null>(null);
function markPaid(id: number) {
    marking.value = id;
    router.post(`/admin/invoices/${id}/paid`, {}, { preserveScroll: true, onFinish: () => (marking.value = null) });
}

const columns = [
    { key: 'request', label: 'Anfrage' },
    { key: 'inspector', label: 'Dienstleister' },
    { key: 'offerAmount', label: 'Auftragswert', align: 'right' as const },
    { key: 'commissionAmount', label: 'Provision', align: 'right' as const },
    { key: 'dueDate', label: 'Fällig am' },
    { key: 'status', label: 'Status', align: 'center' as const },
    { key: 'download', label: '' },
];
</script>

<template>
    <Head><title>Provisionen & Rechnungen</title></Head>

    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <StatCard label="Offene Provisionen (gesamt)" :value="formatEuro(totals.outstanding)" :icon="CircleDollarSign" accent hint="Noch nicht als bezahlt markiert" />
        <StatCard label="Bezahlte Provisionen (gesamt)" :value="formatEuro(totals.paid)" :icon="Check" hint="Bereits als bezahlt markiert" />
    </div>

    <PageCard title="Provisionen & Rechnungen" subtitle="Alle an Dienstleister gestellten Provisionsrechnungen (10% je angenommenem Angebot)">
        <template #actions>
            <div class="flex items-center gap-2">
                <select v-model="status" class="rounded-pill border border-ink-300 px-3 py-2 text-sm" @change="apply">
                    <option value="">Alle Status</option>
                    <option value="outstanding">Nur offene</option>
                    <option value="paid">Nur bezahlte</option>
                </select>
                <div class="relative">
                    <Search :size="16" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-ink-300" aria-hidden="true" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Anfrage-Nr. oder Dienstleister suchen…"
                        class="rounded-pill border border-ink-300 py-2 pl-9 pr-3 text-sm"
                        @keyup.enter="apply"
                    />
                </div>
            </div>
        </template>
        <AdminTable :columns="columns" :rows="invoices.data" row-key="id">
            <template #request="{ row }">
                <Link :href="`/admin/bookings/${row.bookingId}`" class="font-semibold text-green-600 hover:underline">{{ row.request }}</Link>
            </template>
            <template #offerAmount="{ value }">{{ formatEuro(value as number) }}</template>
            <template #commissionAmount="{ value }">{{ formatEuro(value as number) }}</template>
            <template #status="{ row }">
                <button
                    v-if="!row.paid"
                    type="button"
                    :disabled="marking === row.id"
                    class="inline-flex items-center gap-1.5 rounded-pill bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-700 transition hover:bg-amber-200 disabled:opacity-60"
                    @click="markPaid(row.id as number)"
                >
                    <Check :size="13" aria-hidden="true" /> Als bezahlt markieren
                </button>
                <span v-else class="inline-flex flex-col items-center gap-0.5 rounded-pill bg-green-50 px-3 py-1.5 text-xs font-bold text-green-700">
                    Bezahlt
                    <span class="text-[10px] font-semibold text-green-600">{{ row.paidAt }}</span>
                </span>
            </template>
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
