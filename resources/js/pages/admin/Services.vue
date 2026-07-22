<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    categories: Array<{ id: number; name: string; slug: string; active: boolean; types: Array<{ id: number; name: string; active: boolean }>; interest: number }>;
}>();

const processing = ref<number | null>(null);

function toggle(id: number) {
    processing.value = id;
    router.post(`/admin/kategorien/${id}/status`, {}, { preserveScroll: true, onFinish: () => (processing.value = null) });
}
</script>

<template>
    <Head><title>Dienstleistungen</title></Head>

    <PageCard title="Kategorien & Dienstleistungen" subtitle="Aktivieren Sie eine Kategorie, um sie auf der Startseite freizuschalten.">
        <div class="divide-y divide-ink-100">
            <div v-for="cat in categories" :key="cat.id" class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <div class="flex items-center gap-2">
                        <p class="font-display font-bold text-navy-700">{{ cat.name }}</p>
                        <span class="rounded-pill px-2.5 py-0.5 text-xs font-bold" :class="cat.active ? 'bg-green-50 text-green-700' : 'bg-ink-100 text-ink-500'">
                            {{ cat.active ? 'Aktiv' : 'Demnächst' }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-ink-500">
                        {{ cat.types.length }} Leistungen<span v-if="cat.interest > 0"> · {{ cat.interest }} Interessenten</span>
                    </p>
                </div>
                <button
                    type="button"
                    :disabled="processing === cat.id"
                    class="rounded-pill px-5 py-2 text-sm font-bold transition disabled:opacity-60"
                    :class="cat.active ? 'border border-ink-300 text-ink-700 hover:border-navy-700' : 'bg-green-500 text-white hover:bg-green-600'"
                    @click="toggle(cat.id)"
                >
                    {{ cat.active ? 'Deaktivieren' : 'Aktivieren' }}
                </button>
            </div>
        </div>
    </PageCard>
</template>
