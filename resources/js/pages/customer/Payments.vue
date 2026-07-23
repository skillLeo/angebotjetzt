<script setup lang="ts">
import EmptyState from '@/components/dashboard/EmptyState.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head } from '@inertiajs/vue3';
import { CreditCard } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    payments: {
        data: Array<{ id: number; booking: string; service: string; total: number; status: string; date: string | null }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();
</script>

<template>
    <Head><title>{{ t('dashboard.customerPages.paymentsTitle') }}</title></Head>

    <PageCard :title="t('dashboard.customerPages.myPayments')">
        <div v-if="payments.data.length" class="divide-y divide-ink-100">
            <div v-for="p in payments.data" :key="p.id" class="flex items-center justify-between gap-4 px-5 py-4 sm:px-6">
                <div class="min-w-0">
                    <p class="truncate font-semibold text-navy-700">{{ p.service }}</p>
                    <p class="text-sm text-ink-500">{{ p.booking }} · {{ p.date }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-3">
                    <span class="font-display font-bold text-navy-700">{{ formatEuro(p.total) }}</span>
                    <StatusBadge :status="p.status" />
                </div>
            </div>
        </div>
        <EmptyState v-else :icon="CreditCard" :title="t('dashboard.customerPages.noPaymentsYet')" :description="t('dashboard.customerPages.paymentsAppearHere')" />
        <Pagination :links="payments.links" />
    </PageCard>
</template>
