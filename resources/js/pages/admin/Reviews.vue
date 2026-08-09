<script setup lang="ts">
import AdminTable from '@/components/dashboard/AdminTable.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Star } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    reviews: { data: Array<Record<string, unknown>>; links: Array<{ url: string | null; label: string; active: boolean }> };
    unpublishedCount: number;
    filters: { status?: string };
}>();

const statusFilter = ref(props.filters.status ?? '');
function apply() {
    router.get('/admin/reviews', { status: statusFilter.value || undefined }, { preserveState: true });
}

function toggle(id: number) {
    router.post(`/admin/reviews/${id}/published`, {}, { preserveScroll: true });
}

const columns = [
    { key: 'rating', label: 'Bewertung' },
    { key: 'comment', label: 'Kommentar' },
    { key: 'inspector', label: 'Anbieter' },
    { key: 'customer', label: 'Kunde' },
    { key: 'published', label: 'Status', align: 'center' as const },
    { key: 'date', label: 'Datum' },
];
</script>

<template>
    <Head><title>Bewertungen</title></Head>

    <PageCard
        title="Bewertungen"
        :subtitle="`Öffentliche und interne Rückmeldungen von Kunden.${unpublishedCount > 0 ? ` ${unpublishedCount} unveröffentlicht.` : ''}`"
    >
        <template #actions>
            <select v-model="statusFilter" class="rounded-pill border border-ink-300 px-3 py-2 text-sm" @change="apply">
                <option value="">Alle</option>
                <option value="unpublished">Nur unveröffentlicht</option>
            </select>
        </template>

        <AdminTable :columns="columns" :rows="reviews.data" row-key="id">
            <template #rating="{ row }">
                <span class="inline-flex items-center gap-1 font-bold text-navy-700">
                    <Star :size="14" class="text-amber-400" aria-hidden="true" /> {{ row.rating }}/5
                </span>
                <span v-if="row.rawRating" class="block text-xs text-ink-500">{{ `${row.rawRating}/10 in der Umfrage` }}</span>
            </template>
            <template #comment="{ row }">
                <p class="max-w-xs text-ink-700">{{ row.comment ?? '—' }}</p>
            </template>
            <template #inspector="{ row }">
                <span>{{ row.inspector ?? '—' }}</span>
                <span v-if="row.inspectorCompany" class="block text-xs text-ink-500">{{ row.inspectorCompany }}</span>
            </template>
            <template #published="{ row }">
                <button
                    type="button"
                    class="rounded-pill px-3 py-1 text-xs font-bold transition"
                    :class="row.published ? 'bg-green-50 text-green-700 hover:bg-green-100' : 'bg-amber-100 text-amber-700 hover:bg-amber-200'"
                    @click="toggle(row.id as number)"
                >
                    {{ row.published ? 'Veröffentlicht' : 'Unveröffentlicht' }}
                </button>
            </template>
        </AdminTable>
        <Pagination :links="reviews.links" />
    </PageCard>
</template>
