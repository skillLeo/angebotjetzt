<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    payouts: {
        data: Array<{ id: number; inspector: string; inspectorId: number; company: string | null; amount: number; iban: string; bic: string | null; accountHolder: string; balance: number; status: string; requested: string; paid: string | null; paidBy: string | null }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();

const processing = ref<number | null>(null);

function markPaid(id: number) {
    processing.value = id;
    router.post(`/admin/payouts/${id}/paid`, {}, { preserveScroll: true, onFinish: () => (processing.value = null) });
}
</script>

<template>
    <Head><title>Auszahlungen</title></Head>

    <PageCard title="Auszahlungs-Warteschlange" subtitle="Überweisen Sie den Betrag manuell per Banküberweisung an die angegebene IBAN, dann bestätigen Sie es hier — 'Als ausgezahlt markieren' löst selbst keine Zahlung aus, sondern bucht nur das Guthaben aus.">
        <div v-if="payouts.data.length" class="divide-y divide-ink-100">
            <div v-for="p in payouts.data" :key="p.id" class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <Link :href="`/admin/inspectors/${p.inspectorId}`" class="font-semibold text-navy-700 hover:text-green-600">{{ p.inspector }}</Link>
                        <StatusBadge :status="p.status" />
                    </div>
                    <p class="mt-1 text-sm text-ink-500">
                        {{ p.accountHolder }} · {{ p.iban }}<span v-if="p.bic"> · {{ p.bic }}</span>
                    </p>
                    <p class="text-xs text-ink-500">Angefordert {{ p.requested }} · Verfügbares Guthaben {{ formatEuro(p.balance) }}<span v-if="p.paidBy"> · Bezahlt von {{ p.paidBy }}</span></p>
                </div>
                <div class="flex shrink-0 items-center gap-4">
                    <span class="font-display text-xl font-extrabold text-navy-700">{{ formatEuro(p.amount) }}</span>
                    <button
                        v-if="p.status === 'pending'"
                        type="button"
                        :disabled="processing === p.id"
                        class="inline-flex items-center gap-2 rounded-pill bg-green-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-green-600 disabled:opacity-60"
                        @click="markPaid(p.id)"
                    >
                        <Check :size="16" aria-hidden="true" /> Als ausgezahlt markieren
                    </button>
                </div>
            </div>
        </div>
        <p v-else class="px-5 py-10 text-center text-sm text-ink-500">Keine Auszahlungsanfragen.</p>
        <Pagination :links="payouts.links" />
    </PageCard>
</template>
