<script setup lang="ts">
import AdminTable from '@/components/dashboard/AdminTable.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import { Head, router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    customers: { data: Array<Record<string, unknown>>; links: Array<{ url: string | null; label: string; active: boolean }> };
    filters: { suche?: string };
}>();

const search = ref(props.filters.suche ?? '');
function apply() {
    router.get('/admin/customers', { suche: search.value || undefined }, { preserveState: true });
}

const columns = [
    { key: 'name', label: 'Kunde' },
    { key: 'phone', label: 'Telefon' },
    { key: 'requests', label: 'Anfragen', align: 'center' as const },
    { key: 'bookings', label: 'Aufträge', align: 'center' as const },
    { key: 'since', label: 'Seit' },
];
</script>

<template>
    <Head><title>Kunden</title></Head>

    <PageCard title="Kundenverwaltung">
        <template #actions>
            <div class="relative">
                <Search :size="16" class="absolute top-1/2 left-3 -translate-y-1/2 text-ink-300" aria-hidden="true" />
                <input v-model="search" type="search" placeholder="Suche…" class="rounded-pill border border-ink-300 py-2 pr-3 pl-9 text-sm" @keyup.enter="apply" />
            </div>
        </template>
        <AdminTable :columns="columns" :rows="customers.data" row-key="id">
            <template #name="{ row }">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-pill bg-navy-50 font-display text-sm font-bold text-navy-700">
                        {{ String(row.name).charAt(0) }}
                    </span>
                    <div class="min-w-0">
                        <p class="font-semibold text-navy-700">{{ row.name }}</p>
                        <p class="truncate text-xs text-ink-500">{{ row.email }}</p>
                    </div>
                </div>
            </template>
        </AdminTable>
        <Pagination :links="customers.links" />
    </PageCard>
</template>
