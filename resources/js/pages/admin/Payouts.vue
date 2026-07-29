<script setup lang="ts">
import AdminTable from '@/components/dashboard/AdminTable.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    payouts: {
        data: Array<{ id: number; inspector: string; inspectorId: number; company: string | null; amount: number; iban: string; bic: string | null; accountHolder: string; balance: number; status: string; requested: string; paid: string | null; paidBy: string | null }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: { status?: string };
}>();

const status = ref(props.filters.status ?? '');
function apply() {
    router.get('/admin/payouts', { status: status.value || undefined }, { preserveState: true });
}

const processing = ref<number | null>(null);
function markPaid(id: number) {
    processing.value = id;
    router.post(`/admin/payouts/${id}/paid`, {}, { preserveScroll: true, onFinish: () => (processing.value = null) });
}

const columns = [
    { key: 'inspector', label: 'Gutachter' },
    { key: 'amount', label: 'Betrag', align: 'right' as const },
    { key: 'iban', label: 'IBAN' },
    { key: 'balance', label: 'Guthaben', align: 'right' as const },
    { key: 'status', label: 'Status' },
    { key: 'requested', label: 'Angefordert' },
    { key: 'paid', label: 'Bezahlt' },
    { key: 'actions', label: '' },
];
</script>

<template>
    <Head><title>Auszahlungen</title></Head>

    <PageCard title="Auszahlungs-Warteschlange" subtitle="Überweisen Sie den Betrag manuell per Banküberweisung an die angegebene IBAN, dann bestätigen Sie es hier — 'Als ausgezahlt markieren' löst selbst keine Zahlung aus, sondern bucht nur das Guthaben aus.">
        <template #actions>
            <select v-model="status" class="rounded-pill border border-ink-300 px-3 py-2 text-sm" @change="apply">
                <option value="">Alle Status</option>
                <option value="pending">Ausstehend</option>
                <option value="paid">Bezahlt</option>
                <option value="rejected">Abgelehnt</option>
            </select>
        </template>
        <AdminTable :columns="columns" :rows="payouts.data" row-key="id">
            <template #inspector="{ row }">
                <Link :href="`/admin/inspectors/${row.inspectorId}`" class="font-semibold text-green-600 hover:underline">{{ row.inspector }}</Link>
                <p v-if="row.company" class="text-xs text-ink-500">{{ row.company }}</p>
            </template>
            <template #amount="{ value }">{{ formatEuro(value as number) }}</template>
            <template #iban="{ row }">
                <p>{{ row.iban }}</p>
                <p class="text-xs text-ink-500">{{ row.accountHolder }}</p>
            </template>
            <template #balance="{ value }">{{ formatEuro(value as number) }}</template>
            <template #status="{ value }"><StatusBadge :status="value as string" /></template>
            <template #paid="{ row }">
                <span>{{ row.paid ?? '–' }}</span>
                <p v-if="row.paidBy" class="text-xs text-ink-500">{{ 'von' }} {{ row.paidBy }}</p>
            </template>
            <template #actions="{ row }">
                <button
                    v-if="row.status === 'pending'"
                    type="button"
                    :disabled="processing === row.id"
                    class="inline-flex items-center gap-2 rounded-pill bg-green-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-green-600 disabled:opacity-60"
                    @click="markPaid(row.id as number)"
                >
                    <Check :size="14" aria-hidden="true" /> {{ 'Als ausgezahlt markieren' }}
                </button>
            </template>
        </AdminTable>
        <Pagination :links="payouts.links" />
    </PageCard>
</template>
