<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowDownLeft, ArrowUpRight, TrendingUp, Wallet as WalletIcon } from 'lucide-vue-next';

defineProps<{
    wallet: { available: number; pending: number; lifetime: number };
    transactions: {
        data: Array<{ id: number; type: string; amount: number; balanceAfter: number; description: string; date: string }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();
</script>

<template>
    <Head><title>Wallet</title></Head>

    <div class="grid gap-4 sm:grid-cols-3">
        <StatCard label="Verfügbares Guthaben" :value="formatEuro(wallet.available)" :icon="WalletIcon" accent />
        <StatCard label="Ausstehend" :value="formatEuro(wallet.pending)" :icon="TrendingUp" hint="Wird nach Bestätigung freigegeben" />
        <StatCard label="Gesamtverdienst" :value="formatEuro(wallet.lifetime)" :icon="TrendingUp" />
    </div>

    <div class="mt-4 flex justify-end">
        <Link href="/gutachter/wallet/auszahlung" class="rounded-pill bg-green-500 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-green-600">
            Auszahlung anfordern
        </Link>
    </div>

    <div class="mt-6">
        <PageCard title="Transaktionsverlauf">
            <div v-if="transactions.data.length" class="divide-y divide-ink-100">
                <div v-for="t in transactions.data" :key="t.id" class="flex items-center justify-between gap-4 px-5 py-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-pill" :class="t.amount >= 0 ? 'bg-green-50 text-green-600' : 'bg-navy-50 text-navy-600'">
                            <ArrowDownLeft v-if="t.amount >= 0" :size="17" aria-hidden="true" />
                            <ArrowUpRight v-else :size="17" aria-hidden="true" />
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-navy-700">{{ t.description }}</p>
                            <p class="text-xs text-ink-500">{{ t.date }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-display font-bold" :class="t.amount >= 0 ? 'text-green-600' : 'text-navy-700'">
                            {{ t.amount >= 0 ? '+' : '' }}{{ formatEuro(t.amount) }}
                        </p>
                        <p class="text-xs text-ink-500">Saldo {{ formatEuro(t.balanceAfter) }}</p>
                    </div>
                </div>
            </div>
            <p v-else class="px-5 py-8 text-center text-sm text-ink-500">Noch keine Transaktionen.</p>
            <Pagination :links="transactions.links" />
        </PageCard>
    </div>
</template>
