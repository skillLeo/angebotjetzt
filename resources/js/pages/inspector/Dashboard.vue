<script setup lang="ts">
import EmptyState from '@/components/dashboard/EmptyState.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link } from '@inertiajs/vue3';
import { ChevronRight, Inbox, Package, Tag, Wallet } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    stats: { openRequests: number; offers: number; wonJobs: number; walletAvailable: number; walletPending: number; responseRate: number | null };
    newRequests: Array<{ id: number; number: string; service: string; vehicle: string; ort: string; date: string }>;
}>();
</script>

<template>
    <Head><title>{{ t('dashboard.inspectorPages.dashboardTitle') }}</title></Head>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard :label="t('dashboard.inspectorPages.openRequests')" :value="stats.openRequests" :icon="Inbox" accent />
        <StatCard :label="t('dashboard.inspectorPages.submittedOffers')" :value="stats.offers" :icon="Tag" />
        <StatCard :label="t('dashboard.inspectorPages.wonJobs')" :value="stats.wonJobs" :icon="Package" />
        <StatCard :label="t('dashboard.inspectorPages.availableBalance')" :value="formatEuro(stats.walletAvailable)" :icon="Wallet" :hint="`${formatEuro(stats.walletPending)} ${t('dashboard.inspectorPages.pendingSuffix')}`" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <PageCard :title="t('dashboard.inspectorPages.newMatchingRequests')">
                <template #actions>
                    <Link href="/inspector/requests" class="text-sm font-bold text-green-600 hover:text-green-700">{{ t('dashboard.inspectorPages.viewAll') }}</Link>
                </template>
                <div v-if="newRequests.length" class="divide-y divide-ink-100">
                    <Link v-for="r in newRequests" :key="r.id" :href="`/inspector/requests/${r.id}`" class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-sand-50 sm:px-6">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-navy-700">{{ r.vehicle }} · {{ r.service }}</p>
                            <p class="text-sm text-ink-500">{{ r.ort }} · {{ r.number }} · {{ r.date }}</p>
                        </div>
                        <ChevronRight :size="18" class="shrink-0 text-ink-300" aria-hidden="true" />
                    </Link>
                </div>
                <EmptyState v-else :title="t('dashboard.inspectorPages.noNewRequests')" :description="t('dashboard.inspectorPages.noNewRequestsDesc')" />
            </PageCard>
        </div>

        <div>
            <PageCard :title="t('dashboard.inspectorPages.responseRate')">
                <div class="p-6 text-center">
                    <p class="font-display text-5xl font-extrabold text-navy-700">{{ stats.responseRate ?? '–' }}<span v-if="stats.responseRate !== null" class="text-2xl">%</span></p>
                    <p class="mt-2 text-sm text-ink-500">{{ t('dashboard.inspectorPages.responseRateDesc') }}</p>
                </div>
            </PageCard>
        </div>
    </div>
</template>
