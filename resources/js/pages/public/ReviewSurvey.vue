<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Star } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    booking: { id: number; number: string; service: string };
}>();

const form = useForm<{ rating: number | null; comment: string }>({ rating: null, comment: '' });

const showComment = computed(() => form.rating !== null && form.rating <= 7);

function submit() {
    form.post(`/reviews/${props.booking.id}/survey`);
}
</script>

<template>
    <Head><title>{{ 'Wie war Ihr Auftrag?' }}</title></Head>

    <section class="flex min-h-[70vh] items-center bg-sand-50 px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-xl rounded-panel border border-ink-100 bg-white p-7 shadow-card sm:p-10">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-pill bg-green-50 text-green-600">
                <Star :size="26" aria-hidden="true" />
            </span>
            <h1 class="text-section mt-5 text-center text-navy-700">{{ 'Wie war Ihr Auftrag?' }}</h1>
            <p class="mt-2 text-center text-ink-500">{{ `${booking.service} · ${booking.number}` }}</p>

            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <div>
                    <p class="mb-3 text-center text-sm font-semibold text-navy-700">
                        {{ 'Wie zufrieden waren Sie insgesamt? (1 = sehr unzufrieden, 10 = sehr zufrieden)' }}
                    </p>
                    <div class="grid grid-cols-5 gap-2 sm:grid-cols-10">
                        <button
                            v-for="n in 10"
                            :key="n"
                            type="button"
                            class="flex h-11 items-center justify-center rounded-card border text-sm font-bold transition"
                            :class="form.rating === n ? 'border-green-500 bg-green-500 text-white' : 'border-ink-300 text-navy-700 hover:border-green-500'"
                            @click="form.rating = n"
                        >
                            {{ n }}
                        </button>
                    </div>
                    <p v-if="form.errors.rating" class="mt-2 text-center text-sm text-red-600">{{ form.errors.rating }}</p>
                </div>

                <div v-if="showComment">
                    <label for="comment" class="mb-1.5 block text-sm font-semibold text-navy-700">
                        {{ 'Was können wir besser machen?' }}
                    </label>
                    <textarea
                        id="comment"
                        v-model="form.comment"
                        rows="4"
                        :placeholder="'Ihr Feedback hilft uns, besser zu werden.'"
                        class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="form.processing || form.rating === null"
                    class="w-full rounded-pill bg-green-500 py-3.5 font-bold text-white transition hover:bg-green-600 disabled:opacity-60"
                >
                    {{ 'Bewertung absenden' }}
                </button>
            </form>
        </div>
    </section>
</template>
