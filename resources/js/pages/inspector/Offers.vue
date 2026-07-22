<script setup lang="ts">
import EmptyState from '@/components/dashboard/EmptyState.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link } from '@inertiajs/vue3';
import { Tag } from 'lucide-vue-next';

defineProps<{
    offers: {
        data: Array<{ id: number; requestId: number; requestNumber: string; service: string; vehicle: string; ort: string; price: number; net: number; status: string; date: string }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();
</script>

<template>
    <Head><title>Meine Angebote</title></Head>

    <PageCard title="Meine Angebote">
        <div v-if="offers.data.length" class="divide-y divide-ink-100">
            <Link v-for="o in offers.data" :key="o.id" :href="`/gutachter/anfragen/${o.requestId}`" class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-sand-50 sm:px-6">
                <div class="min-w-0">
                    <p class="truncate font-semibold text-navy-700">{{ o.vehicle }} · {{ o.service }}</p>
                    <p class="text-sm text-ink-500">{{ o.ort }} · {{ o.requestNumber }} · {{ o.date }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-4">
                    <div class="text-right">
                        <p class="font-display font-bold text-navy-700">{{ formatEuro(o.price) }}</p>
                        <p class="text-xs text-ink-500">netto {{ formatEuro(o.net) }}</p>
                    </div>
                    <StatusBadge :status="o.status" />
                </div>
            </Link>
        </div>
        <EmptyState v-else :icon="Tag" title="Noch keine Angebote" description="Ihre abgegebenen Angebote erscheinen hier." />
        <Pagination :links="offers.links" />
    </PageCard>
</template>
