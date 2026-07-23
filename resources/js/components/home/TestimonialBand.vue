<script setup lang="ts">
import SectionHeading from '@/components/marketing/SectionHeading.vue';
import { CheckCircle2, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref } from 'vue';


defineProps<{
    reviews: Array<{
        name: string;
        text: string | null;
        rating: number;
        service: string | null;
        city: string | null;
    }>;
    personImage: string;
}>();

const track = ref<HTMLElement | null>(null);

function scroll(dir: number) {
    track.value?.scrollBy({ left: dir * 360, behavior: 'smooth' });
}
</script>

<template>
    <section class="bg-green-500 py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading
                centered
                inverted
                :eyebrow="'Kundenstimmen'"
                :line1="'Tausende zufriedene Kunden'"
                :line2="'vertrauen AngebotJetzt'"
            />

            <div class="relative mt-10">
                <div
                    ref="track"
                    class="flex snap-x snap-mandatory items-stretch gap-5 overflow-x-auto pb-4 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                >
                    <template v-for="(rev, i) in reviews" :key="i">
                        <!-- Insert the person photograph in the middle of the row -->
                        <div
                            v-if="i === 3"
                            class="relative w-[280px] shrink-0 snap-start overflow-hidden rounded-panel bg-green-700"
                        >
                            <img
                                :src="personImage"
                                width="280"
                                height="360"
                                loading="lazy"
                                decoding="async"
                                :alt="'Zufriedene Kundin mit Smartphone'"
                                class="h-full w-full object-cover"
                            />
                            <span
                                class="absolute bottom-5 left-1/2 flex -translate-x-1/2 items-center gap-2 rounded-pill bg-white px-4 py-2.5 text-sm font-bold whitespace-nowrap text-green-700 shadow-lift"
                            >
                                <CheckCircle2 :size="16" aria-hidden="true" />
                                {{ 'Bewertung abgegeben' }}
                            </span>
                        </div>

                        <article
                            class="flex w-[320px] shrink-0 snap-start flex-col rounded-panel bg-white p-7 shadow-lift"
                        >
                            <span
                                class="w-fit rounded-pill bg-green-50 px-3 py-1 text-xs font-bold tracking-wide text-green-700 uppercase"
                            >
                                {{ 'Positiv' }}
                            </span>
                            <p class="mt-4 flex-1 text-[15px] leading-relaxed text-ink-700">
                                „{{ rev.text }}"
                            </p>
                            <div class="mt-6 flex items-center gap-3">
                                <span
                                    class="flex h-11 w-11 items-center justify-center rounded-pill bg-navy-700 font-display text-base font-bold text-white"
                                >
                                    {{ rev.name.charAt(0) }}
                                </span>
                                <div>
                                    <p class="font-display font-bold text-navy-700">{{ rev.name }}</p>
                                    <div class="flex gap-0.5" aria-hidden="true">
                                        <span v-for="s in rev.rating" :key="s" class="text-amber-400">★</span>
                                    </div>
                                </div>
                            </div>
                            <div v-if="rev.service" class="mt-4 rounded-card bg-sand-50 px-4 py-3 text-sm text-ink-500">
                                {{ rev.service }}<span v-if="rev.city"> · {{ rev.city }}</span>
                            </div>
                        </article>
                    </template>
                </div>

                <div class="mt-8 flex justify-center gap-3">
                    <button
                        type="button"
                        class="flex h-11 w-11 items-center justify-center rounded-pill bg-white/20 text-white transition hover:bg-white/30"
                        :aria-label="'Zurück'"
                        @click="scroll(-1)"
                    >
                        <ChevronLeft :size="20" aria-hidden="true" />
                    </button>
                    <button
                        type="button"
                        class="flex h-11 w-11 items-center justify-center rounded-pill bg-white/20 text-white transition hover:bg-white/30"
                        :aria-label="'Weiter'"
                        @click="scroll(1)"
                    >
                        <ChevronRight :size="20" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>
