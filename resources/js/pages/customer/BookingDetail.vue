<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Mail, Phone, Star } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';


const props = defineProps<{
    booking: {
        id: number; number: string; service: string; vehicle: string; inspector: string; inspectorCompany: string | null;
        city: string | null; price: number; status: string; date: string;
        inspectorPhone: string | null; inspectorEmail: string | null; message: string | null; hasReview: boolean;
    };
}>();

const showReview = ref(false);
const rating = ref(5);
const reviewForm = useForm({ rating: 5, comment: '' });

function submitReview() {
    reviewForm.rating = rating.value;
    reviewForm.post(`/account/bookings/${props.booking.id}/review`, {
        preserveScroll: true,
        onSuccess: () => {
            showReview.value = false;
            toast.success('Vielen Dank für Ihre Bewertung!');
        },
    });
}
</script>

<template>
    <Head><title>{{ 'Auftrag' }} {{ booking.number }}</title></Head>

    <Link href="/account/bookings" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-ink-500 hover:text-navy-700">
        <ArrowLeft :size="16" aria-hidden="true" /> {{ 'Zurück zu Aufträgen' }}
    </Link>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <PageCard :title="`${booking.vehicle} · ${booking.service}`" :subtitle="booking.number">
                <template #actions><StatusBadge :status="booking.status" /></template>
                <div class="p-6 sm:p-8">
                    <p class="text-sm text-ink-500">{{ 'Ihr Gutachter' }}</p>
                    <p class="font-display text-lg font-bold text-navy-700">{{ booking.inspector }}</p>
                    <p v-if="booking.inspectorCompany" class="text-sm text-ink-500">{{ booking.inspectorCompany }}, {{ booking.city }}</p>

                    <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                        <a v-if="booking.inspectorPhone" :href="`tel:${booking.inspectorPhone}`" class="inline-flex items-center gap-2 rounded-pill border border-ink-300 px-5 py-2.5 text-sm font-bold text-navy-700 transition hover:border-navy-700">
                            <Phone :size="16" aria-hidden="true" /> {{ booking.inspectorPhone }}
                        </a>
                        <a v-if="booking.inspectorEmail" :href="`mailto:${booking.inspectorEmail}`" class="inline-flex items-center gap-2 rounded-pill border border-ink-300 px-5 py-2.5 text-sm font-bold text-navy-700 transition hover:border-navy-700">
                            <Mail :size="16" aria-hidden="true" /> {{ 'E-Mail' }}
                        </a>
                    </div>

                    <div v-if="booking.message" class="mt-5 rounded-card bg-sand-50 p-4 text-sm leading-relaxed text-ink-700">
                        „{{ booking.message }}"
                    </div>
                </div>
            </PageCard>

            <PageCard v-if="['completed_by_inspector', 'confirmed'].includes(booking.status) && !booking.hasReview" :title="'Bewerten Sie diesen Auftrag'">
                <div class="p-6 sm:p-8">
                    <button v-if="!showReview" type="button" class="rounded-pill bg-green-500 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-green-600" @click="showReview = true">
                        {{ 'Jetzt bewerten' }}
                    </button>
                    <div v-else>
                        <div class="flex items-center gap-2">
                            <button v-for="i in 5" :key="i" type="button" @click="rating = i" :aria-label="`${i} Sterne`">
                                <Star :size="26" class="pointer-events-none" :class="i <= rating ? 'fill-amber-400 text-amber-400' : 'fill-ink-100 text-ink-100'" />
                            </button>
                        </div>
                        <textarea v-model="reviewForm.comment" rows="4" :placeholder="'Wie war Ihre Erfahrung? (optional)'" class="mt-4 w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
                        <div class="mt-4 flex gap-3">
                            <button type="button" :disabled="reviewForm.processing" class="rounded-pill bg-green-500 px-6 py-2.5 text-sm font-bold text-white disabled:opacity-60" @click="submitReview">{{ 'Bewertung senden' }}</button>
                            <button type="button" class="rounded-pill px-6 py-2.5 text-sm font-bold text-ink-500" @click="showReview = false">{{ 'Abbrechen' }}</button>
                        </div>
                    </div>
                </div>
            </PageCard>
        </div>

        <div>
            <PageCard :title="'Auftragsübersicht'">
                <div class="space-y-3 p-5 text-sm sm:p-6">
                    <div class="flex justify-between"><span class="text-ink-500">{{ 'Auftragswert' }}</span><span class="font-bold text-navy-700">{{ formatEuro(booking.price) }}</span></div>
                    <p class="border-t border-ink-100 pt-3 text-xs leading-relaxed text-ink-500">
                        {{ 'Die Zahlung für diesen Auftrag erfolgt direkt zwischen Ihnen und dem Gutachter, außerhalb der Plattform.' }}
                    </p>
                </div>
            </PageCard>
        </div>
    </div>
</template>
