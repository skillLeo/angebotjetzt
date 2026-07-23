<script setup lang="ts">
import EmptyState from '@/components/dashboard/EmptyState.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head } from '@inertiajs/vue3';
import { CreditCard } from 'lucide-vue-next';


defineProps<{
    payments: {
        data: Array<{ id: number; booking: string; service: string; total: number; status: string; date: string | null }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();
</script>

<template>
    <Head><title>{{ 'Zahlungen' }}</title></Head>

    <PageCard :title="'Meine Zahlungen'">
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
        <EmptyState v-else :icon="CreditCard" :title="'Noch keine Zahlungen'" :description="'Ihre Zahlungen erscheinen hier, sobald Sie einen Auftrag beauftragt haben.'" />
        <Pagination :links="payments.links" />
    </PageCard>
</template>
