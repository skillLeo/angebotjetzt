<script setup lang="ts">
import EmptyState from '@/components/dashboard/EmptyState.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link } from '@inertiajs/vue3';
import { ChevronRight, Package } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    bookings: {
        data: Array<{ id: number; number: string; service: string; vehicle: string; inspector: string; city: string | null; price: number; status: string; date: string }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();
</script>

<template>
    <Head><title>{{ t('dashboard.customerPages.myBookings') }}</title></Head>

    <PageCard :title="t('dashboard.customerPages.myBookings')">
        <div v-if="bookings.data.length" class="divide-y divide-ink-100">
            <Link
                v-for="b in bookings.data"
                :key="b.id"
                :href="`/konto/auftraege/${b.id}`"
                class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-sand-50 sm:px-6"
            >
                <div class="min-w-0">
                    <p class="truncate font-semibold text-navy-700">{{ b.vehicle }} · {{ b.service }}</p>
                    <p class="text-sm text-ink-500">{{ b.number }} · {{ b.inspector }} · {{ b.date }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-3">
                    <span class="hidden font-display font-bold text-navy-700 sm:inline">{{ formatEuro(b.price) }}</span>
                    <StatusBadge :status="b.status" />
                    <ChevronRight :size="18" class="text-ink-300" aria-hidden="true" />
                </div>
            </Link>
        </div>
        <EmptyState v-else :icon="Package" :title="t('dashboard.customerPages.noBookingsYet')" :description="t('dashboard.customerPages.bookingsAppearHere')" />
        <Pagination :links="bookings.links" />
    </PageCard>
</template>
