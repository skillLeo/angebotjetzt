<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import { formatEuro } from '@/lib/format';
import { Head, router } from '@inertiajs/vue3';
import { Banknote, Hash, Percent } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    summary: { revenue: number; commission: number; count: number };
    byMonth: Record<string, { revenue: number; commission: number; count: number }>;
    filters: { von: string; bis: string };
    commissionPercent: number;
}>();

const von = ref(props.filters.von);
const bis = ref(props.filters.bis);

function apply() {
    router.get('/admin/commissions', { von: von.value, bis: bis.value }, { preserveState: true });
}
</script>

<template>
    <Head><title>Provisionen</title></Head>

    <PageCard title="Provisionsbericht" :subtitle="`Aktuelle Provision: ${commissionPercent} %`">
        <template #actions>
            <div class="flex items-center gap-2">
                <input v-model="von" type="date" class="rounded-pill border border-ink-300 px-3 py-1.5 text-sm" />
                <span class="text-ink-500">–</span>
                <input v-model="bis" type="date" class="rounded-pill border border-ink-300 px-3 py-1.5 text-sm" />
                <button type="button" class="rounded-pill bg-navy-700 px-4 py-1.5 text-sm font-bold text-white" @click="apply">Anwenden</button>
            </div>
        </template>

        <div class="grid gap-4 p-5 sm:grid-cols-3 sm:p-6">
            <StatCard label="Umsatz" :value="formatEuro(summary.revenue)" :icon="Banknote" />
            <StatCard label="Provision" :value="formatEuro(summary.commission)" :icon="Percent" accent />
            <StatCard label="Buchungen" :value="summary.count" :icon="Hash" />
        </div>

        <div class="border-t border-ink-100">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-ink-100 text-left">
                        <th class="px-6 py-3 text-xs font-bold tracking-wide text-ink-500 uppercase">Monat</th>
                        <th class="px-6 py-3 text-right text-xs font-bold tracking-wide text-ink-500 uppercase">Umsatz</th>
                        <th class="px-6 py-3 text-right text-xs font-bold tracking-wide text-ink-500 uppercase">Provision</th>
                        <th class="px-6 py-3 text-right text-xs font-bold tracking-wide text-ink-500 uppercase">Buchungen</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(v, month) in byMonth" :key="month" class="border-b border-ink-100">
                        <td class="px-6 py-3 text-sm font-semibold text-navy-700">{{ month }}</td>
                        <td class="px-6 py-3 text-right text-sm text-navy-700">{{ formatEuro(v.revenue) }}</td>
                        <td class="px-6 py-3 text-right text-sm font-bold text-green-600">{{ formatEuro(v.commission) }}</td>
                        <td class="px-6 py-3 text-right text-sm text-navy-700">{{ v.count }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </PageCard>
</template>
