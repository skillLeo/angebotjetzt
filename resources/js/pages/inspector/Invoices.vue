<script setup lang="ts">
import EmptyState from '@/components/dashboard/EmptyState.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import { formatEuro } from '@/lib/format';
import { Head } from '@inertiajs/vue3';
import { Download, Receipt } from 'lucide-vue-next';


defineProps<{
    invoices: {
        data: Array<{
            id: number; number: string; booking: string; offerAmount: number;
            commissionPercent: number; commissionAmount: number; dueDate: string; date: string;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();
</script>

<template>
    <Head><title>{{ 'Rechnungen' }}</title></Head>

    <PageCard :title="'Provisionsrechnungen'" :subtitle="'Rechnungen über die Plattform-Provision (10%) für angenommene Aufträge'">
        <div v-if="invoices.data.length" class="divide-y divide-ink-100">
            <div v-for="i in invoices.data" :key="i.id" class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="min-w-0">
                    <p class="truncate font-semibold text-navy-700">{{ i.number }}</p>
                    <p class="text-sm text-ink-500">{{ 'Auftrag' }} {{ i.booking }} · {{ 'Fällig am' }} {{ i.dueDate }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-4">
                    <div class="text-right">
                        <p class="font-display font-bold text-navy-700">{{ formatEuro(i.commissionAmount) }}</p>
                        <p class="text-xs text-ink-500">{{ i.commissionPercent }}% {{ 'von' }} {{ formatEuro(i.offerAmount) }}</p>
                    </div>
                    <a
                        :href="`/inspector/invoices/${i.id}/download`"
                        class="inline-flex items-center gap-2 rounded-pill border border-ink-300 px-4 py-2 text-sm font-bold text-navy-700 transition hover:border-navy-700"
                    >
                        <Download :size="16" aria-hidden="true" /> {{ 'PDF' }}
                    </a>
                </div>
            </div>
        </div>
        <EmptyState v-else :icon="Receipt" :title="'Noch keine Rechnungen'" :description="'Sobald ein Kunde ein Angebot von Ihnen annimmt, erhalten Sie hier die Provisionsrechnung.'" />
        <Pagination :links="invoices.links" />
    </PageCard>
</template>
