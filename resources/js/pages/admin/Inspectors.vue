<script setup lang="ts">
import AdminTable from '@/components/dashboard/AdminTable.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Upload } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    inspectors: { data: Array<Record<string, unknown>>; links: Array<{ url: string | null; label: string; active: boolean }> };
    filters: { suche?: string };
}>();

const search = ref(props.filters.suche ?? '');
function apply() {
    router.get('/admin/gutachter', { suche: search.value || undefined }, { preserveState: true });
}

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'city', label: 'Stadt' },
    { key: 'jobs', label: 'Aufträge', align: 'center' as const },
    { key: 'offers', label: 'Angebote', align: 'center' as const },
    { key: 'balance', label: 'Guthaben', align: 'right' as const },
    { key: 'active', label: 'Status' },
];
</script>

<template>
    <Head><title>Gutachter</title></Head>

    <PageCard title="Gutachterverwaltung">
        <template #actions>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <Search :size="16" class="absolute top-1/2 left-3 -translate-y-1/2 text-ink-300" aria-hidden="true" />
                    <input v-model="search" type="search" placeholder="Suche…" class="rounded-pill border border-ink-300 py-2 pr-3 pl-9 text-sm" @keyup.enter="apply" />
                </div>
                <Link href="/admin/gutachter/import" class="inline-flex items-center gap-2 rounded-pill bg-green-500 px-4 py-2 text-sm font-bold text-white transition hover:bg-green-600">
                    <Upload :size="16" aria-hidden="true" /> CSV-Import
                </Link>
            </div>
        </template>

        <AdminTable :columns="columns" :rows="inspectors.data" row-key="id">
            <template #name="{ row }">
                <Link :href="`/admin/gutachter/${row.id}`" class="font-semibold text-green-600 hover:underline">{{ row.name }}</Link>
                <p class="text-xs text-ink-500">{{ row.company }}</p>
            </template>
            <template #balance="{ value }">{{ formatEuro(value as number) }}</template>
            <template #active="{ value }">
                <span class="inline-flex rounded-pill px-2.5 py-1 text-xs font-bold" :class="value ? 'bg-green-50 text-green-700' : 'bg-ink-100 text-ink-500'">
                    {{ value ? 'Aktiv' : 'Inaktiv' }}
                </span>
            </template>
        </AdminTable>
        <Pagination :links="inspectors.links" />
    </PageCard>
</template>
