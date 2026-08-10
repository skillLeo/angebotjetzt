<script setup lang="ts">
import EmptyState from '@/components/dashboard/EmptyState.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import StarRating from '@/components/marketing/StarRating.vue';
import { Head } from '@inertiajs/vue3';
import { MessageSquare, Star } from 'lucide-vue-next';

defineProps<{
    averageRating: number | null;
    reviewsCount: number;
    reviews: {
        data: Array<{
            id: number; rating: number; rawRating: number | null; comment: string | null;
            service: string | null; requestNumber: string | null; date: string;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();
</script>

<template>
    <Head><title>{{ 'Bewertungen' }}</title></Head>

    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <StatCard :label="'Durchschnittliche Bewertung'" :value="averageRating ? `${averageRating} / 5` : '–'" :icon="Star" accent :hint="`${reviewsCount} Bewertung(en) insgesamt`" />
        <StatCard :label="'Anzahl Bewertungen'" :value="reviewsCount" :icon="MessageSquare" />
    </div>

    <PageCard :title="'Bewertungen'" :subtitle="'Kundenbewertungen zu Ihren abgeschlossenen Aufträgen'">
        <div v-if="reviews.data.length" class="divide-y divide-ink-100">
            <div v-for="r in reviews.data" :key="r.id" class="flex flex-col gap-2 px-5 py-4 sm:px-6">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <StarRating :rating="r.rating" :size="18" />
                        <span v-if="r.rawRating" class="text-xs font-semibold text-ink-500">{{ r.rawRating }}/10</span>
                    </div>
                    <span class="text-xs text-ink-500">{{ r.date }}</span>
                </div>
                <p v-if="r.comment" class="text-sm text-navy-700">{{ r.comment }}</p>
                <p v-else class="text-sm text-ink-500 italic">{{ 'Kein schriftlicher Kommentar.' }}</p>
                <p class="text-xs text-ink-500">{{ r.service }}<span v-if="r.requestNumber"> · {{ r.requestNumber }}</span></p>
            </div>
        </div>
        <EmptyState v-else :icon="Star" :title="'Noch keine Bewertungen'" :description="'Sobald Kunden ihre abgeschlossenen Aufträge bewerten, sehen Sie die Ergebnisse hier.'" />
        <Pagination :links="reviews.links" />
    </PageCard>
</template>
