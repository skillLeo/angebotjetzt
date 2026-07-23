<script setup lang="ts">
import EmptyState from '@/components/dashboard/EmptyState.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';
import { FileText, Package, Tag } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    stats: { requests: number; openOffers: number; bookings: number };
    recentRequests: Array<{ id: number; number: string; service: string; vehicle: string; ort: string; status: string; offers: number; date: string }>;
}>();
</script>

<template>
    <Head><title>{{ t('dashboard.customerPages.dashboardTitle') }}</title></Head>

    <div class="grid gap-4 sm:grid-cols-3">
        <StatCard :label="t('dashboard.customerPages.myRequests')" :value="stats.requests" :icon="FileText" />
        <StatCard :label="t('dashboard.customerPages.openOffers')" :value="stats.openOffers" :icon="Tag" accent :hint="t('dashboard.customerPages.waitingDecision')" />
        <StatCard :label="t('dashboard.customerPages.myBookings')" :value="stats.bookings" :icon="Package" />
    </div>

    <div class="mt-6">
        <PageCard :title="t('dashboard.customerPages.latestRequests')">
            <template #actions>
                <Link href="/anfrage" class="rounded-pill bg-green-500 px-4 py-2 text-sm font-bold text-white transition hover:bg-green-600">
                    {{ t('dashboard.customerPages.newRequest') }}
                </Link>
            </template>
            <div v-if="recentRequests.length" class="divide-y divide-ink-100">
                <Link
                    v-for="r in recentRequests"
                    :key="r.id"
                    :href="`/konto/anfragen/${r.id}`"
                    class="flex flex-col gap-2 px-5 py-4 transition hover:bg-sand-50 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                >
                    <div>
                        <p class="font-semibold text-navy-700">{{ r.vehicle }} · {{ r.service }}</p>
                        <p class="text-sm text-ink-500">{{ r.number }} · {{ r.ort }} · {{ r.date }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span v-if="r.offers > 0" class="rounded-pill bg-green-50 px-3 py-1 text-sm font-bold text-green-700">
                            {{ r.offers }} {{ t('dashboard.customerPages.offersCount') }}
                        </span>
                        <StatusBadge :status="r.status" />
                    </div>
                </Link>
            </div>
            <EmptyState v-else :title="t('dashboard.customerPages.noRequestsYet')" :description="t('dashboard.customerPages.noRequestsDesc')">
                <Link href="/anfrage" class="rounded-pill bg-green-500 px-6 py-2.5 text-sm font-bold text-white">{{ t('dashboard.customerPages.submitRequest') }}</Link>
            </EmptyState>
        </PageCard>
    </div>
</template>
