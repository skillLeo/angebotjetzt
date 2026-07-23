<script setup lang="ts">
import { formatEuroShort } from '@/lib/format';
import { ChevronLeft, ChevronRight, MapPin } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    requests: Array<{
        title: string;
        service: string;
        ort: string;
        plz: string;
        price: number | null;
        photo: string;
    }>;
}>();

const track = ref<HTMLElement | null>(null);

function scroll(dir: number) {
    track.value?.scrollBy({ left: dir * 340, behavior: 'smooth' });
}
</script>

<template>
    <div>
        <div class="mb-6 flex items-center justify-end gap-3">
            <button
                type="button"
                class="flex h-11 w-11 items-center justify-center rounded-pill border border-ink-300 text-navy-700 transition hover:border-navy-700 hover:bg-navy-50"
                :aria-label="t('home.liveRequests.back')"
                @click="scroll(-1)"
            >
                <ChevronLeft :size="20" aria-hidden="true" />
            </button>
            <button
                type="button"
                class="flex h-11 w-11 items-center justify-center rounded-pill border border-ink-300 text-navy-700 transition hover:border-navy-700 hover:bg-navy-50"
                :aria-label="t('home.liveRequests.forward')"
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
                v-for="(req, i) in requests"
                :key="i"
                class="w-[300px] shrink-0 snap-start overflow-hidden rounded-card border border-ink-100 bg-white shadow-card"
            >
                <div class="aspect-[16/10] overflow-hidden">
                    <img
                        :src="req.photo"
                        width="300"
                        height="188"
                        loading="lazy"
                        :alt="`${req.title} – ${req.service}`"
                        class="h-full w-full object-cover"
                    />
                </div>
                <div class="p-5">
                    <span class="text-eyebrow text-green-600">{{ req.service }}</span>
                    <h3 class="mt-2 font-display text-lg font-bold text-navy-700">{{ req.title }}</h3>
                    <p class="mt-3 flex items-center gap-1.5 text-sm text-ink-500">
                        <MapPin :size="15" class="text-ink-300" aria-hidden="true" />
                        {{ req.plz }} {{ req.ort }}
                    </p>
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-sm text-ink-500">{{ t('home.liveRequests.offersFrom') }}</span>
                        <span
                            class="rounded-pill bg-green-50 px-3.5 py-1.5 font-display text-base font-extrabold text-green-700"
                        >
                            {{ req.price ? formatEuroShort(req.price) : t('home.liveRequests.open') }}
                        </span>
                    </div>
                </div>
            </article>
        </div>
    </div>
</template>
