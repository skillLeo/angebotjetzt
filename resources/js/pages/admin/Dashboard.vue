<script setup lang="ts">
import BarChart from '@/components/dashboard/BarChart.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link } from '@inertiajs/vue3';
import { AlertTriangle, Banknote, FileText, Package, Percent, Tag } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    stats: { requests: number; requestsNew: number; offers: number; bookings: number; revenue: number; commission: number; pendingPayouts: number; pendingPayoutAmount: number; unmatchedRequests: number; inspectors: number };
    funnel: { requests: number; withOffers: number; booked: number; completed: number };
    revenueByWeek: Record<string, { total: number; commission: number }>;
    commissionPercent: number;
    topInspectors: Array<{ id: number; name: string; company: string | null; city: string | null; jobs: number }>;
}>();

const chartData = computed(() =>
    Object.entries(props.revenueByWeek).map(([label, v]) => ({ label, value: v.commission / 100 })),
);
const funnelSteps = computed(() => [
    { label: 'Anfragen', value: props.funnel.requests },
    { label: 'Mit Angeboten', value: props.funnel.withOffers },
    { label: 'Beauftragt', value: props.funnel.booked },
    { label: 'Abgeschlossen', value: props.funnel.completed },
]);
const funnelMax = computed(() => Math.max(1, props.funnel.requests));
</script>

<template>
    <Head><title>Dashboard</title></Head>

    <div v-if="stats.pendingPayouts > 0 || stats.unmatchedRequests > 0" class="mb-6 grid gap-3 sm:grid-cols-2">
        <Link v-if="stats.pendingPayouts > 0" href="/admin/payouts" class="flex items-center gap-3 rounded-card border border-amber-200 bg-amber-50 px-5 py-4">
            <AlertTriangle :size="22" class="text-amber-600" aria-hidden="true" />
            <p class="text-sm font-semibold text-amber-800">{{ stats.pendingPayouts }} offene Auszahlungen ({{ formatEuro(stats.pendingPayoutAmount) }})</p>
        </Link>
        <Link v-if="stats.unmatchedRequests > 0" href="/admin/requests?status=unmatched" class="flex items-center gap-3 rounded-card border border-amber-200 bg-amber-50 px-5 py-4">
            <AlertTriangle :size="22" class="text-amber-600" aria-hidden="true" />
            <p class="text-sm font-semibold text-amber-800">{{ stats.unmatchedRequests }} Anfragen ohne passenden Gutachter</p>
        </Link>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard label="Umsatz gesamt" :value="formatEuro(stats.revenue)" :icon="Banknote" accent hint="Bruttosumme aller bezahlten Aufträge" />
        <StatCard label="Provision gesamt" :value="formatEuro(stats.commission)" :icon="Percent" accent :hint="`Plattform-Anteil (${commissionPercent}%) vom Umsatz`" />
        <StatCard label="Anfragen" :value="stats.requests" :icon="FileText" :hint="`${stats.requestsNew} in 30 Tagen`" />
        <StatCard label="Aufträge" :value="stats.bookings" :icon="Package" hint="Bezahlte, bestätigte Buchungen" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <PageCard title="Provision je Woche (letzte 12 Wochen)" subtitle="Ihre Einnahmen aus der Vermittlungsprovision, gruppiert nach Kalenderwoche">
                <div class="p-6 sm:p-8">
                    <BarChart :data="chartData" />
                </div>
            </PageCard>
        </div>
        <div>
            <PageCard title="Conversion-Funnel" subtitle="Wie viele Anfragen am Ende zu einem bezahlten Auftrag führen">
                <div class="space-y-4 p-6 sm:p-8">
                    <div v-for="step in funnelSteps" :key="step.label">
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="text-ink-500">{{ step.label }}</span>
                            <span class="font-bold text-navy-700">{{ step.value }}</span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-pill bg-ink-100">
                            <div class="h-full rounded-pill bg-green-500" :style="{ width: `${(step.value / funnelMax) * 100}%` }" />
                        </div>
                    </div>
                </div>
            </PageCard>
        </div>
    </div>

    <div class="mt-6">
        <PageCard title="Top-Gutachter" subtitle="Die 5 Gutachter mit den meisten abgeschlossenen Aufträgen">
            <div class="divide-y divide-ink-100">
                <Link v-for="i in topInspectors" :key="i.id" :href="`/admin/inspectors/${i.id}`" class="flex items-center justify-between px-5 py-3.5 transition hover:bg-sand-50 sm:px-6">
                    <div>
                        <p class="font-semibold text-navy-700">{{ i.name }}</p>
                        <p class="text-sm text-ink-500">{{ i.company }} · {{ i.city }}</p>
                    </div>
                    <span class="rounded-pill bg-navy-50 px-3 py-1 text-sm font-bold text-navy-700">{{ i.jobs }} Aufträge</span>
                </Link>
            </div>
        </PageCard>
    </div>
</template>
