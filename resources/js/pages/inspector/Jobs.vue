<script setup lang="ts">
import EmptyState from '@/components/dashboard/EmptyState.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link } from '@inertiajs/vue3';
import { ChevronRight, Package } from 'lucide-vue-next';

defineProps<{
    jobs: {
        data: Array<{ id: number; number: string; service: string; vehicle: string; ort: string; price: number; net: number; status: string; date: string }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();
</script>

<template>
    <Head><title>Aufträge</title></Head>

    <PageCard title="Meine Aufträge">
        <div v-if="jobs.data.length" class="divide-y divide-ink-100">
            <Link v-for="j in jobs.data" :key="j.id" :href="`/gutachter/auftraege/${j.id}`" class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-sand-50 sm:px-6">
                <div class="min-w-0">
                    <p class="truncate font-semibold text-navy-700">{{ j.vehicle }} · {{ j.service }}</p>
                    <p class="text-sm text-ink-500">{{ j.ort }} · {{ j.number }} · {{ j.date }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-4">
                    <span class="hidden font-display font-bold text-green-600 sm:inline">{{ formatEuro(j.net) }}</span>
                    <StatusBadge :status="j.status" />
                    <ChevronRight :size="18" class="text-ink-300" aria-hidden="true" />
                </div>
            </Link>
        </div>
        <EmptyState v-else :icon="Package" title="Noch keine Aufträge" description="Gewonnene Aufträge erscheinen hier, sobald ein Kunde Ihr Angebot annimmt." />
        <Pagination :links="jobs.links" />
    </PageCard>
</template>
