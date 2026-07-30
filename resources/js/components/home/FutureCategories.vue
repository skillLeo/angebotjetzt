<script setup lang="ts">
import Reveal from '@/components/marketing/Reveal.vue';
import { Link } from '@inertiajs/vue3';
import {
    ClipboardCheck,
    Flower2,
    Hammer,
    Monitor,
    PaintbrushVertical,
    PartyPopper,
    Shield,
    Sparkles,
    Truck,
} from 'lucide-vue-next';
import type { Component } from 'vue';

defineProps<{
    categories: Array<{ id: number; name: string; slug: string; icon: string; is_active: boolean }>;
}>();


const icons: Record<string, Component> = {
    truck: Truck,
    hammer: Hammer,
    'flower-2': Flower2,
    sparkles: Sparkles,
    paintbrush: PaintbrushVertical,
    'clipboard-check': ClipboardCheck,
    monitor: Monitor,
    'party-popper': PartyPopper,
    shield: Shield,
};
</script>

<template>
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-3">
        <template v-for="cat in categories" :key="cat.id">
            <Reveal>
                <Link
                    v-if="cat.is_active"
                    href="/vehicle-reports"
                    class="group flex items-center gap-4 rounded-card border border-ink-100 bg-white p-5 shadow-card transition hover:-translate-y-0.5 hover:shadow-lift"
                >
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-card bg-green-50 text-green-600 transition group-hover:scale-105"
                    >
                        <component :is="icons[cat.icon] ?? Sparkles" :size="22" aria-hidden="true" />
                    </span>
                    <div class="min-w-0">
                        <p class="truncate font-display font-bold text-navy-700">{{ cat.name }}</p>
                        <span class="mt-1 inline-block text-sm font-semibold text-green-600">{{ 'Angebote erhalten' }}</span>
                    </div>
                </Link>
                <Link
                    v-else
                    :href="`/coming-soon/${cat.slug}`"
                    class="group flex items-center gap-4 rounded-card border border-dashed border-ink-300 bg-sand-50 p-5 transition hover:border-green-500 hover:bg-green-50"
                >
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-card bg-white text-ink-500 shadow-card transition group-hover:text-green-600"
                    >
                        <component :is="icons[cat.icon] ?? Sparkles" :size="22" aria-hidden="true" />
                    </span>
                    <div class="min-w-0">
                        <p class="truncate font-display font-bold text-navy-700">{{ cat.name }}</p>
                        <span
                            class="mt-1 inline-block rounded-pill bg-navy-50 px-2.5 py-0.5 text-xs font-bold text-navy-500"
                        >
                            {{ 'Demnächst' }}
                        </span>
                    </div>
                </Link>
            </Reveal>
        </template>
    </div>
</template>
