<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowDownLeft, ArrowUpRight, TrendingUp, Wallet as WalletIcon } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    wallet: { available: number; pending: number; lifetime: number };
    transactions: {
        data: Array<{ id: number; type: string; amount: number; balanceAfter: number; description: string; date: string }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();
</script>

<template>
    <Head><title>{{ t('dashboard.inspectorPages.walletTitle') }}</title></Head>

    <div class="grid gap-4 sm:grid-cols-3">
        <StatCard :label="t('dashboard.inspectorPages.availableBalanceCard')" :value="formatEuro(wallet.available)" :icon="WalletIcon" accent />
        <StatCard :label="t('dashboard.inspectorPages.pending')" :value="formatEuro(wallet.pending)" :icon="TrendingUp" :hint="t('dashboard.inspectorPages.pendingHint')" />
        <StatCard :label="t('dashboard.inspectorPages.totalEarnings')" :value="formatEuro(wallet.lifetime)" :icon="TrendingUp" />
    </div>

    <div class="mt-4 flex justify-end">
        <Link href="/gutachter/wallet/auszahlung" class="rounded-pill bg-green-500 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-green-600">
            {{ t('dashboard.inspectorPages.requestPayout') }}
        </Link>
    </div>

    <div class="mt-6">
        <PageCard :title="t('dashboard.inspectorPages.transactionHistory')">
            <div v-if="transactions.data.length" class="divide-y divide-ink-100">
                <div v-for="tx in transactions.data" :key="tx.id" class="flex items-center justify-between gap-4 px-5 py-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-pill" :class="tx.amount >= 0 ? 'bg-green-50 text-green-600' : 'bg-navy-50 text-navy-600'">
                            <ArrowDownLeft v-if="tx.amount >= 0" :size="17" aria-hidden="true" />
                            <ArrowUpRight v-else :size="17" aria-hidden="true" />
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-navy-700">{{ tx.description }}</p>
                            <p class="text-xs text-ink-500">{{ tx.date }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-display font-bold" :class="tx.amount >= 0 ? 'text-green-600' : 'text-navy-700'">
                            {{ tx.amount >= 0 ? '+' : '' }}{{ formatEuro(tx.amount) }}
                        </p>
                        <p class="text-xs text-ink-500">{{ t('dashboard.inspectorPages.balance', { amount: formatEuro(tx.balanceAfter) }) }}</p>
                    </div>
                </div>
            </div>
            <p v-else class="px-5 py-8 text-center text-sm text-ink-500">{{ t('dashboard.inspectorPages.noTransactionsYet') }}</p>
            <Pagination :links="transactions.links" />
        </PageCard>
    </div>
</template>
