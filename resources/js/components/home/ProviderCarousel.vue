<script setup lang="ts">
import StarRating from '@/components/marketing/StarRating.vue';
import { BadgeCheck, ChevronLeft, ChevronRight, Wrench } from 'lucide-vue-next';
import { ref } from 'vue';


defineProps<{
    providers: Array<{
        name: string;
        city: string | null;
        reviews: number;
        rating: number | null;
        jobs: number;
        since: string | null;
        photo: string | null;
    }>;
}>();

const track = ref<HTMLElement | null>(null);

function scroll(dir: number) {
    track.value?.scrollBy({ left: dir * 320, behavior: 'smooth' });
}
</script>

<template>
    <div>
        <div class="mb-6 flex items-center justify-end gap-3">
            <button
                type="button"
                class="flex h-11 w-11 items-center justify-center rounded-pill border border-ink-300 text-navy-700 transition hover:border-navy-700 hover:bg-navy-50"
                :aria-label="'Zurück'"
                @click="scroll(-1)"
            >
                <ChevronLeft :size="20" aria-hidden="true" />
            </button>
            <button
                type="button"
                class="flex h-11 w-11 items-center justify-center rounded-pill border border-ink-300 text-navy-700 transition hover:border-navy-700 hover:bg-navy-50"
                :aria-label="'Weiter'"
                @click="scroll(1)"
            >
                <ChevronRight :size="20" aria-hidden="true" />
            </button>
        </div>

        <div
            ref="track"
            class="flex snap-x snap-mandatory gap-5 overflow-x-auto pb-4 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
        >
            <article
                v-for="(p, i) in providers"
                :key="i"
                class="flex w-[280px] shrink-0 snap-start flex-col rounded-card border border-ink-100 bg-white p-6 shadow-card"
            >
                <div class="flex items-center gap-4">
                    <img
                        v-if="p.photo"
                        :src="p.photo"
                        width="64"
                        height="64"
                        loading="lazy"
                        :alt="`${'Anbieter'} ${p.name}`"
                        class="h-16 w-16 rounded-pill object-cover"
                    />
                    <span v-else class="flex h-16 w-16 shrink-0 items-center justify-center rounded-pill bg-sand-100 text-ink-400">
                        <Wrench :size="26" aria-hidden="true" />
                    </span>
                    <div>
                        <p class="text-sm text-ink-500">{{ 'Anbieter' }} {{ p.city }}</p>
                        <h3 class="font-display text-lg font-bold text-navy-700">{{ p.name }}</h3>
                    </div>
                </div>

                <span
                    class="mt-4 inline-flex w-fit items-center gap-1.5 rounded-pill bg-green-50 px-3 py-1 text-sm font-bold text-green-700"
                >
                    <BadgeCheck :size="15" aria-hidden="true" /> {{ 'Von AngebotJetzt geprüft' }}
                </span>

                <dl class="mt-5 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-ink-500">{{ 'Bewertungen' }}</dt>
                        <dd class="font-semibold text-ink-700">{{ p.reviews }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-500">{{ 'Dabei seit' }}</dt>
                        <dd class="font-semibold text-ink-700">{{ p.since }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-500">{{ 'Abgeschlossen' }}</dt>
                        <dd class="font-semibold text-ink-700">{{ p.jobs }} {{ 'Aufträge' }}</dd>
                    </div>
                </dl>

                <div class="mt-5 flex items-center gap-2 border-t border-ink-100 pt-4">
                    <StarRating :rating="p.rating ?? 5" :size="16" />
                    <span class="font-display text-base font-extrabold text-navy-700">
                        {{ (p.rating ?? 5).toFixed(1).replace('.', ',') }}
                    </span>
                </div>

                <button
                    type="button"
                    class="mt-5 rounded-pill border border-ink-300 py-2.5 text-sm font-bold text-navy-700 transition hover:border-navy-700 hover:bg-navy-50"
                >
                    {{ 'Profil ansehen' }}
                </button>
            </article>
        </div>
    </div>
</template>
