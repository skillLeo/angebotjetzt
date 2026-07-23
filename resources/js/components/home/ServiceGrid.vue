<script setup lang="ts">
import Reveal from '@/components/marketing/Reveal.vue';
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

defineProps<{
    serviceTypes: Array<{ id: number; name: string; slug: string; description: string; image: string }>;
}>();

const { t } = useI18n();
</script>

<template>
    <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        <Reveal
            v-for="(type, i) in serviceTypes"
            :key="type.id"
            :delay="i * 0.07"
            :class="i === 0 ? 'md:col-span-2 lg:col-span-2' : ''"
        >
            <Link
                :href="`/vehicle-reports/${type.slug}`"
                class="group relative flex h-full flex-col overflow-hidden rounded-card border border-ink-100 bg-white transition-shadow hover:shadow-lift"
            >
                <div class="relative overflow-hidden" :class="i === 0 ? 'aspect-[16/8]' : 'aspect-[16/10]'">
                    <img
                        :src="type.image"
                        :width="i === 0 ? 800 : 440"
                        :height="i === 0 ? 400 : 275"
                        loading="lazy"
                        :alt="`${type.name} ${t('home.services.imageAltSuffix')}`"
                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    />
                    <span
                        class="absolute top-4 left-4 rounded-pill bg-white/95 px-3.5 py-1.5 text-sm font-bold text-navy-700 shadow-card backdrop-blur"
                    >
                        {{ type.name }}
                    </span>
                </div>
                <div class="flex flex-1 flex-col p-6">
                    <p class="flex-1 text-[15px] leading-relaxed text-ink-500">
                        {{ type.description }}
                    </p>
                    <span class="mt-5 inline-flex items-center gap-2 text-[15px] font-bold text-green-600">
                        {{ t('home.services.cta') }}
                        <ArrowRight
                            :size="18"
                            class="transition-transform duration-300 group-hover:translate-x-1.5"
                            aria-hidden="true"
                        />
                    </span>
                </div>
            </Link>
        </Reveal>
    </div>
</template>
