<script setup lang="ts">
import EmptyState from '@/components/dashboard/EmptyState.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ChevronRight, Clock, Inbox, MapPin, Package, Star, Tag } from 'lucide-vue-next';


defineProps<{
    pendingApproval?: boolean;
    needsServiceArea?: boolean;
    stats: {
        openRequests: number; offers: number; wonJobs: number; responseRate: number | null;
        rating: number | null; reviewsCount: number;
    } | null;
    newRequests: Array<{ id: number; number: string; service: string; vehicle: string; ort: string; date: string }>;
}>();
</script>

<template>
    <Head><title>{{ 'Dashboard' }}</title></Head>

    <PageCard v-if="pendingApproval">
        <div class="flex flex-col items-center gap-4 px-6 py-16 text-center sm:px-8">
            <span class="flex h-14 w-14 items-center justify-center rounded-pill bg-amber-100 text-amber-700">
                <Clock :size="26" aria-hidden="true" />
            </span>
            <div>
                <h2 class="font-display text-xl font-bold text-navy-700">{{ 'Ihr Konto wird geprüft' }}</h2>
                <p class="mt-2 max-w-md text-ink-500">
                    {{ 'Vielen Dank für Ihre Registrierung. Unser Team prüft Ihr Gutachter-Konto derzeit. Sobald es freigeschaltet ist, erhalten Sie passende Anfragen aus Ihrem Servicegebiet und können Angebote abgeben.' }}
                </p>
            </div>
        </div>
    </PageCard>

    <div
        v-if="!pendingApproval && needsServiceArea"
        class="mb-6 flex flex-col items-start gap-4 rounded-card border border-amber-200 bg-amber-50 p-5 sm:flex-row sm:items-center sm:justify-between"
    >
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-pill bg-amber-100 text-amber-700">
                <MapPin :size="18" aria-hidden="true" />
            </span>
            <div>
                <p class="font-display font-bold text-navy-700">{{ 'Servicegebiet fehlt noch' }}</p>
                <p class="text-sm text-ink-500">{{ 'Ohne Servicegebiet erhalten Sie keine passenden Anfragen.' }}</p>
            </div>
        </div>
        <Link href="/inspector/service-areas" class="shrink-0 rounded-pill bg-green-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-green-600">
            {{ 'Jetzt festlegen' }}
        </Link>
    </div>

    <div v-if="!pendingApproval" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard :label="'Offene Anfragen'" :value="stats.openRequests" :icon="Inbox" accent />
        <StatCard :label="'Abgegebene Angebote'" :value="stats.offers" :icon="Tag" />
        <StatCard :label="'Gewonnene Aufträge'" :value="stats.wonJobs" :icon="Package" />
        <StatCard
            :label="'Bewertung'"
            :value="stats.rating ? stats.rating.toFixed(1).replace('.', ',') : '–'"
            :icon="Star"
            :hint="stats.reviewsCount ? `${stats.reviewsCount} ${'Bewertungen'}` : 'Noch keine Bewertungen'"
        />
    </div>

    <div v-if="!pendingApproval" class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <PageCard :title="'Neue passende Anfragen'">
                <template #actions>
                    <Link href="/inspector/requests" class="text-sm font-bold text-green-600 hover:text-green-700">{{ 'Alle ansehen' }}</Link>
                </template>
                <div v-if="newRequests.length" class="divide-y divide-ink-100">
                    <Link v-for="r in newRequests" :key="r.id" :href="`/inspector/requests/${r.id}`" class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-sand-50 sm:px-6">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-navy-700">{{ r.vehicle }} · {{ r.service }}</p>
                            <p class="text-sm text-ink-500">{{ r.ort }} · {{ r.number }} · {{ r.date }}</p>
                        </div>
                        <ChevronRight :size="18" class="shrink-0 text-ink-300" aria-hidden="true" />
                    </Link>
                </div>
                <EmptyState v-else :title="'Keine neuen Anfragen'" :description="'Sobald eine Anfrage in Ihrem Servicegebiet eingeht, erscheint sie hier.'" />
            </PageCard>
        </div>

        <div>
            <PageCard :title="'Antwortrate'">
                <div class="p-6 text-center">
                    <p class="font-display text-5xl font-extrabold text-navy-700">{{ stats.responseRate ?? '–' }}<span v-if="stats.responseRate !== null" class="text-2xl">%</span></p>
                    <p class="mt-2 text-sm text-ink-500">{{ 'Ihrer passenden Anfragen haben Sie ein Angebot abgegeben.' }}</p>
                </div>
            </PageCard>
        </div>
    </div>
</template>
