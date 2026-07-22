<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import Pagination from '@/components/dashboard/Pagination.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    logs: {
        data: Array<{ id: number; action: string; actor: string; subject: string | null; ip: string | null; date: string }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();
</script>

<template>
    <Head><title>Protokolle</title></Head>

    <PageCard title="Aktivitätsprotokoll">
        <div class="divide-y divide-ink-100">
            <div v-for="log in logs.data" :key="log.id" class="flex items-center justify-between gap-4 px-5 py-3 sm:px-6">
                <div class="min-w-0">
                    <p class="truncate font-mono text-sm font-semibold text-navy-700">{{ log.action }}</p>
                    <p class="text-xs text-ink-500">{{ log.actor }}<span v-if="log.subject"> → {{ log.subject }}</span></p>
                </div>
                <div class="shrink-0 text-right text-xs text-ink-500">
                    <p>{{ log.date }}</p>
                    <p v-if="log.ip">{{ log.ip }}</p>
                </div>
            </div>
        </div>
        <Pagination :links="logs.links" />
    </PageCard>
</template>
