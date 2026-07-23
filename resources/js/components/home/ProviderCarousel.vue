<script setup lang="ts">
import StarRating from '@/components/marketing/StarRating.vue';
import { BadgeCheck, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();

defineProps<{
    providers: Array<{
        name: string;
        city: string | null;
        reviews: number;
        rating: number | null;
        jobs: number;
        since: string | null;
        photo: string;
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
                :aria-label="t('home.providers.back')"
                @click="scroll(-1)"
            >
                <ChevronLeft :size="20" aria-hidden="true" />
            </button>
            <button
                type="button"
                class="flex h-11 w-11 items-center justify-center rounded-pill border border-ink-300 text-navy-700 transition hover:border-navy-700 hover:bg-navy-50"
                :aria-label="t('home.providers.forward')"
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
                        :src="p.photo"
                        width="64"
                        height="64"
                        loading="lazy"
                        :alt="`${t('home.providers.inCity')} ${p.name}`"
                        class="h-16 w-16 rounded-pill object-cover"
                    />
                    <div>
                        <p class="text-sm text-ink-500">{{ t('home.providers.inCity') }} {{ p.city }}</p>
                        <h3 class="font-display text-lg font-bold text-navy-700">{{ p.name }}</h3>
                    </div>
                </div>

                <span
                    class="mt-4 inline-flex w-fit items-center gap-1.5 rounded-pill bg-green-50 px-3 py-1 text-sm font-bold text-green-700"
                >
                    <BadgeCheck :size="15" aria-hidden="true" /> {{ t('home.providers.verified') }}
                </span>

                <dl class="mt-5 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-ink-500">{{ t('home.providers.reviews') }}</dt>
                        <dd class="font-semibold text-ink-700">{{ p.reviews }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-500">{{ t('home.providers.memberSince') }}</dt>
                        <dd class="font-semibold text-ink-700">{{ p.since }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-500">{{ t('home.providers.completed') }}</dt>
                        <dd class="font-semibold text-ink-700">{{ p.jobs }} {{ t('home.providers.jobsSuffix') }}</dd>
                    </div>
                </dl>

                <div class="mt-5 flex items-center gap-2 border-t border-ink-100 pt-4">
                    <StarRating :rating="p.rating ?? 5" :size="16" />
                    <span class="font-display text-base font-extrabold text-navy-700">
                        {{ (p.rating ?? 5).toFixed(1).replace('.', locale === 'de' ? ',' : '.') }}
                    </span>
                </div>

                <button
                    type="button"
                    class="mt-5 rounded-pill border border-ink-300 py-2.5 text-sm font-bold text-navy-700 transition hover:border-navy-700 hover:bg-navy-50"
                >
                    {{ t('home.providers.viewProfile') }}
                </button>
            </article>
        </div>
    </div>
</template>
