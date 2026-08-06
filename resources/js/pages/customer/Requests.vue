<script setup lang="ts">
import EmptyState from '@/components/dashboard/EmptyState.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';


defineProps<{
    requests: {
        data: Array<{ id: number; number: string; service: string; vehicle: string; ort: string; status: string; offers: number; date: string }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();
</script>

<template>
    <Head><title>{{ 'Meine Anfragen' }}</title></Head>

    <PageCard :title="'Meine Anfragen'">
        <template #actions>
            <Link href="/request/start" class="rounded-pill bg-green-500 px-4 py-2 text-sm font-bold text-white transition hover:bg-green-600">
                {{ 'Neue Anfrage' }}
            </Link>
        </template>

        <div v-if="requests.data.length" class="divide-y divide-ink-100">
            <Link
                v-for="r in requests.data"
                :key="r.id"
                :href="`/account/requests/${r.id}`"
                class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-sand-50 sm:px-6"
            >
                <div class="min-w-0">
                    <p class="truncate font-semibold text-navy-700">{{ r.vehicle }} · {{ r.service }}</p>
                    <p class="text-sm text-ink-500">{{ r.number }} · {{ r.ort }} · {{ r.date }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-3">
                    <span v-if="r.offers > 0" class="hidden rounded-pill bg-green-50 px-3 py-1 text-sm font-bold text-green-700 sm:inline">
                        {{ r.offers }} {{ 'Angebote' }}
                    </span>
                    <StatusBadge :status="r.status" />
                    <ChevronRight :size="18" class="text-ink-300" aria-hidden="true" />
                </div>
            </Link>
        </div>
        <EmptyState v-else :title="'Noch keine Anfragen'" :description="'Ihre Anfragen erscheinen hier.'">
            <Link href="/request/start" class="rounded-pill bg-green-500 px-6 py-2.5 text-sm font-bold text-white">{{ 'Anfrage stellen' }}</Link>
        </EmptyState>

        <Pagination :links="requests.links" />
    </PageCard>
</template>
