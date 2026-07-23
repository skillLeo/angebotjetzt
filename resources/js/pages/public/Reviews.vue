<script setup lang="ts">
import Pagination from '@/components/dashboard/Pagination.vue';
import StarRating from '@/components/marketing/StarRating.vue';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    reviews: {
        data: Array<{ name: string; inspector: string | null; city: string | null; service: string | null; text: string | null; rating: number; date: string }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();
</script>

<template>
    <Head>
        <title>{{ t('public.reviews.metaTitle') }}</title>
        <meta name="description" :content="t('public.reviews.metaDescription')" />
    </Head>

    <section class="bg-sand-100 px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <p class="text-eyebrow mb-4 text-green-600">{{ t('public.reviews.eyebrow') }}</p>
            <h1 class="text-hero max-w-2xl text-navy-700">{{ t('public.reviews.title') }}</h1>
            <div class="mt-6 flex items-center gap-3">
                <span class="font-display text-4xl font-extrabold text-navy-700">4,9</span>
                <StarRating :rating="5" :size="20" />
            </div>
        </div>
    </section>

    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                <article v-for="(r, i) in reviews.data" :key="i" class="rounded-card border border-ink-100 bg-white p-6 shadow-card">
                    <div class="flex items-center justify-between">
                        <StarRating :rating="r.rating" :size="16" />
                        <span class="text-xs text-ink-300">{{ r.date }}</span>
                    </div>
                    <p class="mt-4 text-[15px] leading-relaxed text-ink-700">„{{ r.text }}"</p>
                    <div class="mt-5 border-t border-ink-100 pt-4">
                        <p class="font-display font-bold text-navy-700">{{ r.name }}</p>
                        <p class="text-sm text-ink-500">
                            {{ r.service }}<span v-if="r.city"> · {{ r.city }}</span>
                        </p>
                    </div>
                </article>
            </div>
            <Pagination :links="reviews.links" />
        </div>
    </section>
</template>
