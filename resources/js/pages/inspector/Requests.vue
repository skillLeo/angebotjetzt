<script setup lang="ts">
import EmptyState from '@/components/dashboard/EmptyState.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Check, ChevronRight } from 'lucide-vue-next';

const props = defineProps<{
    requests: {
        data: Array<{ id: number; number: string; service: string; vehicle: string; ort: string; date: string; totalOffers: number; hasOwnOffer: boolean }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    serviceTypes: Array<{ id: number; name: string; slug: string }>;
    filter: string | null;
}>();

function filterBy(slug: string | null) {
    router.get('/gutachter/anfragen', slug ? { service: slug } : {}, { preserveState: true });
}
</script>

<template>
    <Head><title>Anfragen</title></Head>

    <div class="mb-5 flex flex-wrap gap-2">
        <button
            type="button"
            class="rounded-pill px-4 py-2 text-sm font-bold transition"
            :class="!filter ? 'bg-navy-700 text-white' : 'bg-white text-ink-700 ring-1 ring-ink-100 hover:bg-sand-50'"
            @click="filterBy(null)"
        >
            Alle
        </button>
        <button
            v-for="t in serviceTypes"
            :key="t.id"
            type="button"
            class="rounded-pill px-4 py-2 text-sm font-bold transition"
            :class="filter === t.slug ? 'bg-navy-700 text-white' : 'bg-white text-ink-700 ring-1 ring-ink-100 hover:bg-sand-50'"
            @click="filterBy(t.slug)"
        >
            {{ t.name }}
        </button>
    </div>

    <PageCard title="Passende Anfragen">
        <div v-if="requests.data.length" class="divide-y divide-ink-100">
            <Link v-for="r in requests.data" :key="r.id" :href="`/gutachter/anfragen/${r.id}`" class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-sand-50 sm:px-6">
                <div class="min-w-0">
                    <p class="truncate font-semibold text-navy-700">{{ r.vehicle }} · {{ r.service }}</p>
                    <p class="text-sm text-ink-500">{{ r.ort }} · {{ r.number }} · {{ r.date }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-3">
                    <span v-if="r.hasOwnOffer" class="inline-flex items-center gap-1 rounded-pill bg-green-50 px-3 py-1 text-xs font-bold text-green-700">
                        <Check :size="13" aria-hidden="true" /> Angebot abgegeben
                    </span>
                    <span v-else class="hidden text-xs text-ink-500 sm:inline">{{ r.totalOffers }} Angebote gesamt</span>
                    <ChevronRight :size="18" class="text-ink-300" aria-hidden="true" />
                </div>
            </Link>
        </div>
        <EmptyState v-else title="Keine passenden Anfragen" description="Sobald eine Anfrage in Ihrem Servicegebiet eingeht, erscheint sie hier." />
        <Pagination :links="requests.links" />
    </PageCard>
</template>
