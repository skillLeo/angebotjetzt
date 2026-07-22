<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links: Array<{ url: string | null; label: string; active: boolean }>;
}>();
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap items-center justify-center gap-1.5 px-5 py-4" aria-label="Seitennavigation">
        <template v-for="(link, i) in links" :key="i">
            <Link
                v-if="link.url"
                :href="link.url"
                class="flex h-9 min-w-9 items-center justify-center rounded-card px-3 text-sm font-medium transition"
                :class="link.active ? 'bg-green-500 text-white' : 'text-ink-700 hover:bg-sand-50'"
                v-html="link.label"
            />
            <span
                v-else
                class="flex h-9 min-w-9 items-center justify-center px-3 text-sm text-ink-300"
                v-html="link.label"
            />
        </template>
    </nav>
</template>
